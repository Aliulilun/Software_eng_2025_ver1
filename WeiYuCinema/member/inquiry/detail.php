<?php
/**
 * 訂票詳情頁面
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

// 查詢訂票記錄詳情
$sql = "SELECT br.*, 
               m.movieName, m.movieImg, m.movieTime, m.movieDirector, m.movieActor,
               c.cinemaName, c.cinemaAddress, c.cinemaPhone,
               t.theaterName, t.seatNumber,
               sh.showingDate, sh.startTime,
               os.orderStatusName,
               pv.versionName,
               g.gradeName,
               tt.ticketTypeName
        FROM bookingRecord br
        JOIN showing sh ON br.showingId = sh.showingId
        JOIN movie m ON sh.movieId = m.movieId
        JOIN theater t ON sh.theaterId = t.theaterId
        JOIN cinema c ON t.cinemaId = c.cinemaId
        JOIN orderStatus os ON br.orderStatusId = os.orderStatusId
        JOIN playVersion pv ON sh.versionId = pv.versionId
        JOIN grade g ON m.gradeId = g.gradeId
        JOIN ticketType tt ON br.ticketTypeId = tt.ticketTypeId
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

// 解析餐點資訊
$mealDetails = [];
if (!empty($record['chooseMeal'])) {
    $mealDetails = json_decode($record['chooseMeal'], true);
}

// 格式化座位資訊
$seatArray = explode(',', $record['seat']);

// 載入HTML模板
include 'templates/detail.html';
closeConnection($conn);
?>
