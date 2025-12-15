<?php
/**
 * 退票申請頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

$memberId = $_SESSION['memberId'];
$orderNumber = isset($_GET['orderNumber']) ? $_GET['orderNumber'] : '';

if (empty($orderNumber)) {
    header("Location: index.php?error=invalid_order");
    exit();
}

// 查詢訂票記錄
$sql = "SELECT br.*, 
               m.movieName,
               c.cinemaName,
               t.theaterName,
               sh.showingDate, sh.startTime,
               os.orderStatusName
        FROM bookingRecord br
        JOIN showing sh ON br.showingId = sh.showingId
        JOIN movie m ON sh.movieId = m.movieId
        JOIN theater t ON sh.theaterId = t.theaterId
        JOIN cinema c ON t.cinemaId = c.cinemaId
        JOIN orderStatus os ON br.orderStatusId = os.orderStatusId
        WHERE br.orderNumber = ? AND br.memberId = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $orderNumber, $memberId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php?error=order_not_found");
    exit();
}

$record = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 檢查是否可以退票
$canRefund = false;
$refundMessage = '';

if ($record['orderStatusId'] != 1) {
    $refundMessage = '此訂單狀態無法退票';
} else {
    // 檢查是否在開演前2小時
    $showDateTime = $record['showingDate'] . ' ' . $record['startTime'];
    $showTimestamp = strtotime($showDateTime);
    $currentTimestamp = time();
    $timeDiff = $showTimestamp - $currentTimestamp;
    
    if ($timeDiff < 7200) { // 2小時 = 7200秒
        $refundMessage = '開演前2小時內無法退票';
    } else {
        $canRefund = true;
    }
}

// 處理退票申請
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canRefund) {
    $refundReason = isset($_POST['refundReason']) ? trim($_POST['refundReason']) : '';
    
    if (empty($refundReason)) {
        $refundMessage = '請填寫退票原因';
    } else {
        // 開始資料庫交易
        mysqli_autocommit($conn, false);
        
        try {
            // 1. 更新訂單狀態為已退票 (假設 orderStatusId = 3 為已退票)
            $updateOrderSql = "UPDATE bookingRecord SET orderStatusId = 3 WHERE orderNumber = ?";
            $stmt = mysqli_prepare($conn, $updateOrderSql);
            mysqli_stmt_bind_param($stmt, "s", $orderNumber);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("更新訂單狀態失敗");
            }
            mysqli_stmt_close($stmt);
            
            // 2. 釋放座位
            $seatArray = explode(',', $record['seat']);
            foreach ($seatArray as $seatNumber) {
                $updateSeatSql = "UPDATE seatCondition SET seatEmpty = 1 WHERE showingId = ? AND seatNumber = ?";
                $stmt = mysqli_prepare($conn, $updateSeatSql);
                mysqli_stmt_bind_param($stmt, "ss", $record['showingId'], $seatNumber);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("釋放座位失敗");
                }
                mysqli_stmt_close($stmt);
            }
            
            // 3. 退款到儲值卡
            // 先取得目前餘額
            $balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ?";
            $stmt = mysqli_prepare($conn, $balanceSql);
            mysqli_stmt_bind_param($stmt, "s", $memberId);
            mysqli_stmt_execute($stmt);
            $balanceResult = mysqli_stmt_get_result($stmt);
            $currentBalance = mysqli_fetch_assoc($balanceResult)['balance'];
            mysqli_stmt_close($stmt);
            
            // 更新餘額
            $newBalance = $currentBalance + $record['totalPrice'];
            $updateBalanceSql = "UPDATE memberCashCard SET balance = ? WHERE memberId = ?";
            $stmt = mysqli_prepare($conn, $updateBalanceSql);
            mysqli_stmt_bind_param($stmt, "is", $newBalance, $memberId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("退款失敗");
            }
            mysqli_stmt_close($stmt);
            
            // 4. 記錄退款交易
            $transactionId = 'R' . date('Ymd') . sprintf('%03d', rand(1, 999));
            $description = "退票退款 - 訂單號: {$orderNumber}";
            
            $transactionSql = "INSERT INTO topupTransaction (transactionId, memberId, transactionType, amount, balanceBefore, balanceAfter, description) VALUES (?, ?, 'REFUND', ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $transactionSql);
            mysqli_stmt_bind_param($stmt, "sssiis", $transactionId, $memberId, $record['totalPrice'], $currentBalance, $newBalance, $description);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("記錄退款交易失敗");
            }
            mysqli_stmt_close($stmt);
            
            // 提交交易
            mysqli_commit($conn);
            
            // 重定向到成功頁面
            header("Location: index.php?message=" . urlencode("退票申請成功，退款金額 $" . number_format($record['totalPrice']) . " 已退回您的儲值卡") . "&type=success");
            exit();
            
        } catch (Exception $e) {
            // 回滾交易
            mysqli_rollback($conn);
            $refundMessage = "退票失敗：" . $e->getMessage();
        }
        
        // 恢復自動提交
        mysqli_autocommit($conn, true);
    }
}

// 載入HTML模板
include 'templates/refund.html';
closeConnection($conn);
?>
