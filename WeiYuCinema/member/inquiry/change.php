<?php
/**
 * 修改訂票頁面
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
               t.theaterName, t.seatNumber as totalSeats,
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

// 檢查是否可以修改
$canChange = false;
$changeMessage = '';

if ($record['orderStatusId'] != 1) {
    $changeMessage = '此訂單狀態無法修改';
} else {
    // 檢查是否在開演前2小時
    $showDateTime = $record['showingDate'] . ' ' . $record['startTime'];
    $showTimestamp = strtotime($showDateTime);
    $currentTimestamp = time();
    $timeDiff = $showTimestamp - $currentTimestamp;
    
    if ($timeDiff < 7200) { // 2小時 = 7200秒
        $changeMessage = '開演前2小時內無法修改訂票';
    } else {
        $canChange = true;
    }
}

// 如果可以修改，取得座位資訊
$currentSeats = explode(',', $record['seat']);
$availableSeats = [];
$occupiedSeats = [];

if ($canChange) {
    // 取得該場次的座位狀況
    $seatSql = "SELECT seatNumber, seatEmpty FROM seatCondition WHERE showingId = ? ORDER BY seatNumber";
    $seatStmt = mysqli_prepare($conn, $seatSql);
    mysqli_stmt_bind_param($seatStmt, "s", $record['showingId']);
    mysqli_stmt_execute($seatStmt);
    $seatResult = mysqli_stmt_get_result($seatStmt);
    
    while ($seatRow = mysqli_fetch_assoc($seatResult)) {
        if ($seatRow['seatEmpty'] == 1 || in_array($seatRow['seatNumber'], $currentSeats)) {
            $availableSeats[] = $seatRow['seatNumber'];
        } else {
            $occupiedSeats[] = $seatRow['seatNumber'];
        }
    }
    mysqli_stmt_close($seatStmt);
}

// 處理座位修改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canChange) {
    $newSeats = isset($_POST['seats']) ? $_POST['seats'] : [];
    
    if (empty($newSeats)) {
        $changeMessage = '請選擇座位';
    } elseif (count($newSeats) != $record['ticketNums']) {
        $changeMessage = '選擇的座位數量必須與原訂票數量相同 (' . $record['ticketNums'] . ' 張)';
    } else {
        // 檢查新座位是否都可用
        $invalidSeats = array_diff($newSeats, $availableSeats);
        if (!empty($invalidSeats)) {
            $changeMessage = '選擇的座位中有不可用的座位：' . implode(', ', $invalidSeats);
        } else {
            // 開始資料庫交易
            mysqli_autocommit($conn, false);
            
            try {
                // 1. 釋放原座位
                foreach ($currentSeats as $seat) {
                    $updateSeatSql = "UPDATE seatCondition SET seatEmpty = 1 WHERE showingId = ? AND seatNumber = ?";
                    $stmt = mysqli_prepare($conn, $updateSeatSql);
                    mysqli_stmt_bind_param($stmt, "ss", $record['showingId'], $seat);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("釋放原座位失敗");
                    }
                    mysqli_stmt_close($stmt);
                }
                
                // 2. 佔用新座位
                foreach ($newSeats as $seat) {
                    $updateSeatSql = "UPDATE seatCondition SET seatEmpty = 0 WHERE showingId = ? AND seatNumber = ?";
                    $stmt = mysqli_prepare($conn, $updateSeatSql);
                    mysqli_stmt_bind_param($stmt, "ss", $record['showingId'], $seat);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("佔用新座位失敗");
                    }
                    mysqli_stmt_close($stmt);
                }
                
                // 3. 更新訂票記錄
                $newSeatString = implode(',', $newSeats);
                $updateOrderSql = "UPDATE bookingRecord SET seat = ? WHERE orderNumber = ?";
                $stmt = mysqli_prepare($conn, $updateOrderSql);
                mysqli_stmt_bind_param($stmt, "ss", $newSeatString, $orderNumber);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("更新訂票記錄失敗");
                }
                mysqli_stmt_close($stmt);
                
                // 提交交易
                mysqli_commit($conn);
                
                // 重定向到成功頁面
                header("Location: detail.php?orderNumber=" . urlencode($orderNumber) . "&message=" . urlencode("座位修改成功") . "&type=success");
                exit();
                
            } catch (Exception $e) {
                // 回滾交易
                mysqli_rollback($conn);
                $changeMessage = "修改失敗：" . $e->getMessage();
            }
            
            // 恢復自動提交
            mysqli_autocommit($conn, true);
        }
    }
}

// 載入HTML模板
include 'templates/change.html';
closeConnection($conn);
?>
