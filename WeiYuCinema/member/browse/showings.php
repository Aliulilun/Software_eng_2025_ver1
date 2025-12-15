<?php
/**
 * 場次查詢 (Browse Showings)
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得查詢條件
$movieFilter = isset($_GET['movie']) ? $_GET['movie'] : '';
$cinemaFilter = isset($_GET['cinema']) ? $_GET['cinema'] : '';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// 建立查詢語句
$sql = "SELECT s.*, m.movieName, c.cinemaName, t.theaterName, pv.versionName
        FROM showing s
        JOIN movie m ON s.movieId = m.movieId
        JOIN theater t ON s.theaterId = t.theaterId
        JOIN cinema c ON t.cinemaId = c.cinemaId
        JOIN playVersion pv ON s.versionId = pv.versionId
        WHERE s.showingDate >= CURDATE()";

$params = [];
$types = "";

// 電影篩選
if (!empty($movieFilter)) {
    $sql .= " AND m.movieId = ?";
    $params[] = $movieFilter;
    $types .= "i";
}

// 影城篩選
if (!empty($cinemaFilter)) {
    $sql .= " AND c.cinemaId = ?";
    $params[] = $cinemaFilter;
    $types .= "s";
}

// 日期篩選
if (!empty($dateFilter)) {
    $sql .= " AND s.showingDate = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$sql .= " ORDER BY s.showingDate, s.startTime";

// 執行查詢
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// 將結果轉為陣列
$showings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $showings[] = $row;
}

// 取得電影列表（用於篩選）
$moviesResult = mysqli_query($conn, "SELECT movieId, movieName FROM movie ORDER BY movieName");
$movies = [];
while ($row = mysqli_fetch_assoc($moviesResult)) {
    $movies[] = $row;
}

// 取得影城列表（用於篩選）
$cinemasResult = mysqli_query($conn, "SELECT cinemaId, cinemaName FROM cinema ORDER BY cinemaName");
$cinemas = [];
while ($row = mysqli_fetch_assoc($cinemasResult)) {
    $cinemas[] = $row;
}

include 'templates/showings.html';
closeConnection($conn);
?>
