<?php
/**
 * 完整交易紀錄查詢
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得篩選條件
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterDate = isset($_GET['date']) ? $_GET['date'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20; // 每頁顯示 20 筆
$offset = ($page - 1) * $limit;

// 建立查詢條件
$whereClauses = ["memberId = ?"];
$params = [$memberId];
$types = "s";

if (!empty($filterType)) {
    $whereClauses[] = "transactionType = ?";
    $params[] = $filterType;
    $types .= "s";
}

if (!empty($filterDate)) {
    $whereClauses[] = "DATE(transactionDate) = ?";
    $params[] = $filterDate;
    $types .= "s";
}

$whereClause = implode(" AND ", $whereClauses);

// 查詢總筆數
$countSql = "SELECT COUNT(*) as total FROM topupTransaction WHERE $whereClause";
$countStmt = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($countStmt, $types, ...$params);
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);
mysqli_stmt_close($countStmt);

// 查詢交易紀錄
$transactionSql = "SELECT * FROM topupTransaction 
                   WHERE $whereClause 
                   ORDER BY transactionDate DESC 
                   LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$transactionStmt = mysqli_prepare($conn, $transactionSql);
mysqli_stmt_bind_param($transactionStmt, $types, ...$params);
mysqli_stmt_execute($transactionStmt);
$transactionResult = mysqli_stmt_get_result($transactionStmt);

// 載入 HTML 模板
include 'templates/transaction_history.html';

mysqli_stmt_close($transactionStmt);
closeConnection($conn);
?>
