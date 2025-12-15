<?php
/**
 * 影城資訊查詢 (Browse Cinemas)
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得查詢條件
$searchKeyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// 建立查詢語句
$sql = "SELECT c.* FROM cinema c WHERE 1=1";
$params = [];
$types = "";

// 關鍵字搜尋（影城名稱、地址）
if (!empty($searchKeyword)) {
    $sql .= " AND (c.cinemaName LIKE ? OR c.cinemaAddress LIKE ?)";
    $searchPattern = "%$searchKeyword%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $types .= "ss";
}

$sql .= " ORDER BY c.cinemaName";

// 執行查詢
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// 將結果轉為陣列
$cinemas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cinemas[] = $row;
}

include 'templates/cinemas.html';
closeConnection($conn);
?>
