<?php
/**
 * 電影資訊查詢 (Browse Movies)
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得查詢條件
$searchKeyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$filterGrade = isset($_GET['grade']) ? $_GET['grade'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';

// 建立查詢語句
$sql = "SELECT m.*, g.gradeName, mt.movieTypeName 
        FROM movie m
        LEFT JOIN grade g ON m.gradeId = g.gradeId
        LEFT JOIN movieType mt ON m.movieTypeId = mt.movieTypeId
        WHERE 1=1";

$params = [];
$types = "";

// 關鍵字搜尋（電影名稱、導演、演員）
if (!empty($searchKeyword)) {
    $sql .= " AND (m.movieName LIKE ? OR m.director LIKE ? OR m.actors LIKE ?)";
    $searchPattern = "%$searchKeyword%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $types .= "sss";
}

// 分級篩選
if (!empty($filterGrade)) {
    $sql .= " AND m.gradeId = ?";
    $params[] = $filterGrade;
    $types .= "i";
}

// 類型篩選
if (!empty($filterType)) {
    $sql .= " AND m.movieTypeId = ?";
    $params[] = $filterType;
    $types .= "i";
}

$sql .= " ORDER BY m.movieStart DESC";

// 執行查詢
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 取得分級列表（用於篩選）
$gradeList = mysqli_query($conn, "SELECT * FROM grade ORDER BY gradeId");

// 取得類型列表（用於篩選）
$typeList = mysqli_query($conn, "SELECT * FROM movieType ORDER BY movieTypeId");

// 載入 HTML 模板
include 'templates/movies.html';

mysqli_stmt_close($stmt);
closeConnection($conn);
?>

