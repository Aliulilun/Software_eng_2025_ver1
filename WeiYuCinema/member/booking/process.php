<?php
/**
 * 訂票處理頁面（付款處理）
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 檢查必要的POST資料
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['showingId']) || !isset($_POST['selectedSeats']) || 
    !isset($_POST['ticketCount']) || !isset($_POST['ticketTotalPrice']) ||
    !isset($_POST['selectedMeals']) || !isset($_POST['mealTotalPrice']) ||
    !isset($_POST['grandTotalPrice'])) {
    header("Location: booking.php?error=invalid_request");
    exit();
}

$showingId = $_POST['showingId'];
$selectedSeats = $_POST['selectedSeats'];
$ticketCount = (int)$_POST['ticketCount'];
$ticketTotalPrice = (int)$_POST['ticketTotalPrice'];
$selectedMealsJson = $_POST['selectedMeals'];
$mealTotalPrice = (int)$_POST['mealTotalPrice'];
$grandTotalPrice = (int)$_POST['grandTotalPrice'];

// 解析餐點資料
$selectedMeals = json_decode($selectedMealsJson, true);
if ($selectedMeals === null) {
    $selectedMeals = [];
}

// 驗證資料
if (empty($selectedSeats) || $ticketCount <= 0 || $grandTotalPrice <= 0) {
    header("Location: booking.php?error=invalid_data");
    exit();
}

// 開始資料庫交易
mysqli_autocommit($conn, false);

try {
    // 1. 再次檢查座位是否仍然可用
    $seatArray = explode(',', $selectedSeats);
    $seatCheckSql = "SELECT seatNumber, seatEmpty FROM seatCondition WHERE showingId = ? AND seatNumber IN (" . 
                    str_repeat('?,', count($seatArray) - 1) . "?) FOR UPDATE";
    $stmt = mysqli_prepare($conn, $seatCheckSql);
    $types = str_repeat('s', count($seatArray) + 1);
    mysqli_stmt_bind_param($stmt, $types, $showingId, ...$seatArray);
    mysqli_stmt_execute($stmt);
    $seatCheckResult = mysqli_stmt_get_result($stmt);
    
    $unavailableSeats = [];
    while ($seatCheck = mysqli_fetch_assoc($seatCheckResult)) {
        if ($seatCheck['seatEmpty'] == 0) {
            $unavailableSeats[] = $seatCheck['seatNumber'];
        }
    }
    mysqli_stmt_close($stmt);
    
    if (!empty($unavailableSeats)) {
        throw new Exception("座位已被占用: " . implode(', ', $unavailableSeats));
    }
    
    // 2. 檢查會員餘額
    $balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ? FOR UPDATE";
    $stmt = mysqli_prepare($conn, $balanceSql);
    mysqli_stmt_bind_param($stmt, "s", $memberId);
    mysqli_stmt_execute($stmt);
    $balanceResult = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($balanceResult) === 0) {
        throw new Exception("找不到會員儲值卡資料");
    }
    
    $balanceData = mysqli_fetch_assoc($balanceResult);
    $currentBalance = $balanceData['balance'];
    mysqli_stmt_close($stmt);
    
    if ($currentBalance < $grandTotalPrice) {
        throw new Exception("餘額不足");
    }
    
    // 3. 生成訂單編號
    $orderNumber = 'ORD' . date('Ymd') . sprintf('%04d', rand(1, 9999));
    
    // 檢查訂單編號是否重複
    $checkOrderSql = "SELECT orderNumber FROM bookingRecord WHERE orderNumber = ?";
    $stmt = mysqli_prepare($conn, $checkOrderSql);
    mysqli_stmt_bind_param($stmt, "s", $orderNumber);
    mysqli_stmt_execute($stmt);
    $checkResult = mysqli_stmt_get_result($stmt);
    
    // 如果重複，重新生成
    while (mysqli_num_rows($checkResult) > 0) {
        $orderNumber = 'ORD' . date('Ymd') . sprintf('%04d', rand(1, 9999));
        mysqli_stmt_bind_param($stmt, "s", $orderNumber);
        mysqli_stmt_execute($stmt);
        $checkResult = mysqli_stmt_get_result($stmt);
    }
    mysqli_stmt_close($stmt);
    
    // 4. 插入訂票記錄
    $bookingSql = "INSERT INTO bookingRecord (orderNumber, memberId, showingId, time, seat, chooseMeal, ticketTypeId, ticketNums, orderStatusId, totalPrice, getTicketNum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $bookingSql);
    
    $currentTime = date('Y-m-d H:i:s');
    $chooseMealStr = !empty($selectedMeals) ? json_encode($selectedMeals) : '';
    $ticketTypeId = 0; // 一般票
    $orderStatusId = 1; // 已完成
    $getTicketNum = rand(100000, 999999); // 取票號碼
    
    mysqli_stmt_bind_param($stmt, "ssssssiiiis", 
        $orderNumber, $memberId, $showingId, $currentTime, $selectedSeats, 
        $chooseMealStr, $ticketTypeId, $ticketCount, $orderStatusId, 
        $grandTotalPrice, $getTicketNum);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("插入訂票記錄失敗");
    }
    mysqli_stmt_close($stmt);
    
    // 5. 更新座位狀態
    foreach ($seatArray as $seatNumber) {
        $updateSeatSql = "UPDATE seatCondition SET seatEmpty = 0 WHERE showingId = ? AND seatNumber = ?";
        $stmt = mysqli_prepare($conn, $updateSeatSql);
        mysqli_stmt_bind_param($stmt, "ss", $showingId, $seatNumber);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("更新座位狀態失敗");
        }
        mysqli_stmt_close($stmt);
    }
    
    // 6. 扣除會員餘額
    $newBalance = $currentBalance - $grandTotalPrice;
    $updateBalanceSql = "UPDATE memberCashCard SET balance = ? WHERE memberId = ?";
    $stmt = mysqli_prepare($conn, $updateBalanceSql);
    mysqli_stmt_bind_param($stmt, "is", $newBalance, $memberId);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("更新餘額失敗");
    }
    mysqli_stmt_close($stmt);
    
    // 7. 記錄交易
    $transactionId = 'TXN' . date('YmdHis') . rand(100, 999);
    $transactionSql = "INSERT INTO topupTransaction (transactionId, memberId, transactionType, amount, balanceBefore, balanceAfter, transactionDate, description, status) VALUES (?, ?, 'CONSUME', ?, ?, ?, ?, ?, 'SUCCESS')";
    $stmt = mysqli_prepare($conn, $transactionSql);
    
    $description = "購票消費 - 訂單號碼: " . $orderNumber;
    mysqli_stmt_bind_param($stmt, "ssiisss", 
        $transactionId, $memberId, $grandTotalPrice, $currentBalance, 
        $newBalance, $currentTime, $description);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("記錄交易失敗");
    }
    mysqli_stmt_close($stmt);
    
    // 提交交易
    mysqli_commit($conn);
    
    // 訂票成功，重導向到成功頁面
    header("Location: success.php?orderNumber=" . urlencode($orderNumber) . "&getTicketNum=" . urlencode($getTicketNum));
    exit();
    
} catch (Exception $e) {
    // 回滾交易
    mysqli_rollback($conn);
    
    // 記錄錯誤（在實際應用中應該記錄到日誌文件）
    error_log("訂票處理錯誤: " . $e->getMessage());
    
    // 重導向到錯誤頁面
    $errorMsg = urlencode($e->getMessage());
    header("Location: confirm.php?error=booking_failed&message=" . $errorMsg);
    exit();
    
} finally {
    // 恢復自動提交
    mysqli_autocommit($conn, true);
    closeConnection($conn);
}
?>
