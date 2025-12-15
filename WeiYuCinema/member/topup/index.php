<?php
/**
 * 儲值卡管理首頁 (Top Up)
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得會員儲值卡資訊
$cardSql = "SELECT balance FROM memberCashCard WHERE memberId = ?";
$cardStmt = mysqli_prepare($conn, $cardSql);
mysqli_stmt_bind_param($cardStmt, "s", $memberId);
mysqli_stmt_execute($cardStmt);
$cardResult = mysqli_stmt_get_result($cardStmt);

if (mysqli_num_rows($cardResult) === 0) {
    // 如果沒有儲值卡，自動建立一張（餘額為 0）
    $createCard = "INSERT INTO memberCashCard (memberId, balance) VALUES (?, 0)";
    $createStmt = mysqli_prepare($conn, $createCard);
    mysqli_stmt_bind_param($createStmt, "s", $memberId);
    mysqli_stmt_execute($createStmt);
    mysqli_stmt_close($createStmt);
    $balance = 0;
} else {
    $cardData = mysqli_fetch_assoc($cardResult);
    $balance = $cardData['balance'];
}
mysqli_stmt_close($cardStmt);

// 取得最近 10 筆交易紀錄
$transactionSql = "SELECT * FROM topupTransaction 
                   WHERE memberId = ? 
                   ORDER BY transactionDate DESC 
                   LIMIT 10";
$transactionStmt = mysqli_prepare($conn, $transactionSql);
mysqli_stmt_bind_param($transactionStmt, "s", $memberId);
mysqli_stmt_execute($transactionStmt);
$transactionResult = mysqli_stmt_get_result($transactionStmt);

// 取得成功訊息（如果有）
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// 載入 HTML 模板
include 'templates/index.html';

mysqli_stmt_close($transactionStmt);
closeConnection($conn);
?>
