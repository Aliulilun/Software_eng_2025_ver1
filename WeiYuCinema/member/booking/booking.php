<?php
/**
 * 購票服務 (Booking) - 場次選擇頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得篩選參數
$selectedMovie = isset($_GET['movieId']) ? $_GET['movieId'] : '';
$selectedCinema = isset($_GET['cinemaId']) ? $_GET['cinemaId'] : '';
$selectedDate = isset($_GET['showDate']) ? $_GET['showDate'] : '';

// 保持向後相容
$movieFilter = $selectedMovie;
$cinemaFilter = $selectedCinema;
$dateFilter = $selectedDate ?: date('Y-m-d');

// 建立查詢條件
$whereConditions = [];
$params = [];
$types = '';

if (!empty($movieFilter)) {
    $whereConditions[] = "m.movieId = ?";
    $params[] = $movieFilter;
    $types .= 'i';
}

if (!empty($cinemaFilter)) {
    $whereConditions[] = "c.cinemaId = ?";
    $params[] = $cinemaFilter;
    $types .= 's';
}

if (!empty($dateFilter)) {
    $whereConditions[] = "s.showingDate = ?";
    $params[] = $dateFilter;
    $types .= 's';
}

// 只顯示今天及以後的場次
$whereConditions[] = "s.showingDate >= ?";
$params[] = date('Y-m-d');
$types .= 's';

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// 查詢場次資料
$sql = "SELECT s.showingId, s.showingDate, s.startTime,
               m.movieId, m.movieName, m.movieTime, m.gradeId, m.movieImg,
               c.cinemaId, c.cinemaName, c.cinemaAddress,
               t.theaterId, t.theaterName, t.seatNumber,
               pv.versionName,
               tc.ticketClassPrice,
               g.gradeName,
               mt.movieTypeName
        FROM showing s
        JOIN movie m ON s.movieId = m.movieId
        JOIN theater t ON s.theaterId = t.theaterId
        JOIN cinema c ON t.cinemaId = c.cinemaId
        JOIN playVersion pv ON s.versionId = pv.versionId
        JOIN ticketClass tc ON m.gradeId = tc.ticketTypeId
        JOIN grade g ON m.gradeId = g.gradeId
        JOIN movieType mt ON m.movieTypeId = mt.movieTypeId
        {$whereClause}
        ORDER BY s.showingDate ASC, s.startTime ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$showingsResult = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// 將結果轉為陣列供模板使用
$showings = [];
while ($row = mysqli_fetch_assoc($showingsResult)) {
    // 計算座位狀況
    $seatSql = "SELECT COUNT(*) as totalSeats, 
                       SUM(seatEmpty) as availableSeats 
                FROM seatCondition 
                WHERE showingId = ?";
    $seatStmt = mysqli_prepare($conn, $seatSql);
    mysqli_stmt_bind_param($seatStmt, "s", $row['showingId']);
    mysqli_stmt_execute($seatStmt);
    $seatResult = mysqli_stmt_get_result($seatStmt);
    $seatData = mysqli_fetch_assoc($seatResult);
    mysqli_stmt_close($seatStmt);
    
    $row['totalSeats'] = $seatData['totalSeats'] ?: $row['seatNumber'];
    $row['availableSeats'] = $seatData['availableSeats'] ?: $row['seatNumber'];
    $row['ticketPrice'] = $row['ticketClassPrice']; // 為了相容性
    $row['showDate'] = $row['showingDate']; // 為了相容性
    $row['showTime'] = $row['startTime']; // 為了相容性
    
    $showings[] = $row;
}

// 取得電影列表（用於篩選）
$moviesSql = "SELECT DISTINCT m.movieId, m.movieName 
              FROM movie m 
              JOIN showing s ON m.movieId = s.movieId 
              WHERE s.showingDate >= CURDATE()
              ORDER BY m.movieName";
$moviesResult = mysqli_query($conn, $moviesSql);
$movies = [];
while ($row = mysqli_fetch_assoc($moviesResult)) {
    $movies[] = $row;
}

// 取得影城列表（用於篩選）
$cinemasSql = "SELECT DISTINCT c.cinemaId, c.cinemaName 
               FROM cinema c 
               JOIN theater t ON c.cinemaId = t.cinemaId
               JOIN showing s ON t.theaterId = s.theaterId 
               WHERE s.showingDate >= CURDATE()
               ORDER BY c.cinemaName";
$cinemasResult = mysqli_query($conn, $cinemasSql);
$cinemas = [];
while ($row = mysqli_fetch_assoc($cinemasResult)) {
    $cinemas[] = $row;
}

// 取得可用日期列表
$datesSql = "SELECT DISTINCT showingDate 
             FROM showing 
             WHERE showingDate >= CURDATE()
             ORDER BY showingDate 
             LIMIT 14";
$datesResult = mysqli_query($conn, $datesSql);
$availableDates = [];
while ($row = mysqli_fetch_assoc($datesResult)) {
    $availableDates[] = $row['showingDate'];
}

// 載入HTML模板
include 'templates/booking.html';

closeConnection($conn);
?>

