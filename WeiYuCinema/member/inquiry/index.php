<?php
/**
 * 訂票紀錄查詢 (Inquiry) - 主頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

$memberId = $_SESSION['memberId'];
$memberName = $_SESSION['memberName'];

// 取得查詢參數
$searchOrderNumber = isset($_GET['orderNumber']) ? trim($_GET['orderNumber']) : '';
$searchDate = isset($_GET['date']) ? $_GET['date'] : '';
$searchStatus = isset($_GET['status']) ? $_GET['status'] : '';

// 建立查詢條件
$whereConditions = ["br.memberId = ?"];
$params = [$memberId];
$types = "s";

if (!empty($searchOrderNumber)) {
    $whereConditions[] = "br.orderNumber LIKE ?";
    $params[] = "%{$searchOrderNumber}%";
    $types .= "s";
}

if (!empty($searchDate)) {
    $whereConditions[] = "DATE(br.time) = ?";
    $params[] = $searchDate;
    $types .= "s";
}

if (!empty($searchStatus)) {
    $whereConditions[] = "br.orderStatusId = ?";
    $params[] = $searchStatus;
    $types .= "i";
}

$whereClause = implode(' AND ', $whereConditions);

// 查詢訂票記錄
$sql = "SELECT br.*, 
               m.movieName, m.movieImg,
               c.cinemaName, 
               t.theaterName,
               sh.showingDate, sh.startTime,
               os.orderStatusName,
               pv.versionName
        FROM bookingRecord br
        JOIN showing sh ON br.showingId = sh.showingId
        JOIN movie m ON sh.movieId = m.movieId
        JOIN theater t ON sh.theaterId = t.theaterId
        JOIN cinema c ON t.cinemaId = c.cinemaId
        JOIN orderStatus os ON br.orderStatusId = os.orderStatusId
        JOIN playVersion pv ON sh.versionId = pv.versionId
        WHERE {$whereClause}
        ORDER BY br.time DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 將結果轉為陣列
$bookingRecords = [];
while ($row = mysqli_fetch_assoc($result)) {
    // 解析餐點資訊
    if (!empty($row['chooseMeal'])) {
        $row['mealDetails'] = json_decode($row['chooseMeal'], true);
    } else {
        $row['mealDetails'] = [];
    }
    
    // 格式化座位資訊
    $row['seatArray'] = explode(',', $row['seat']);
    
    $bookingRecords[] = $row;
}
mysqli_stmt_close($stmt);

// 取得訂單狀態列表（用於篩選）
$statusSql = "SELECT * FROM orderStatus ORDER BY orderStatusId";
$statusResult = mysqli_query($conn, $statusSql);
$orderStatuses = [];
while ($row = mysqli_fetch_assoc($statusResult)) {
    $orderStatuses[] = $row;
}

// 處理訊息
$message = '';
$messageType = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $messageType = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'info';
}

// 載入HTML模板
include 'templates/index.html';
closeConnection($conn);
?>
