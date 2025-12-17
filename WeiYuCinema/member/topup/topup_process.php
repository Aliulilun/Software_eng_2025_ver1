<?php
/**
 * 儲值處理核心 (正式版)
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 1. 安全檢查
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$memberId = $_SESSION['memberId'];
$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '未指定';

// 簡單驗證
if ($amount <= 0) {
    echo "<script>alert('金額錯誤'); window.history.back();</script>";
    exit();
}

// 2. 開啟資料庫交易
mysqli_begin_transaction($conn);

try {
    // A. 檢查並鎖定餘額
    $balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ? FOR UPDATE";
    $stmt = mysqli_prepare($conn, $balanceSql);
    mysqli_stmt_bind_param($stmt, "s", $memberId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    
    // 如果沒有卡片，自動開卡
    if (!$row) {
        $initSql = "INSERT INTO memberCashCard (memberId, balance) VALUES (?, 0)";
        $initStmt = mysqli_prepare($conn, $initSql);
        mysqli_stmt_bind_param($initStmt, "s", $memberId);
        mysqli_stmt_execute($initStmt);
        $currentBalance = 0;
    } else {
        $currentBalance = $row['balance'];
    }

    // B. 更新餘額
    $newBalance = $currentBalance + $amount;
    $updateSql = "UPDATE memberCashCard SET balance = ? WHERE memberId = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($updateStmt, "is", $newBalance, $memberId);
    
    if (!mysqli_stmt_execute($updateStmt)) {
        throw new Exception("餘額更新失敗");
    }

    // C. 寫入交易紀錄
    $transactionId = 'T' . date('YmdHis') . rand(100, 999);
    $desc = "線上儲值 ($paymentMethod)";
    $type = 'TOPUP';
    $status = 'SUCCESS';
    
    // 欄位: transactionId, memberId, transactionType, amount, balanceBefore, balanceAfter, description, status
    $insertSql = "INSERT INTO topupTransaction 
                  (transactionId, memberId, transactionType, amount, balanceBefore, balanceAfter, description, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $insertSql);
    mysqli_stmt_bind_param($stmt, "sssiisss", 
        $transactionId, $memberId, $type, $amount, 
        $currentBalance, $newBalance, $desc, $status
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("交易紀錄寫入失敗");
    }

    // 全部成功，提交
    mysqli_commit($conn);
    
    // D. 成功提示並導向
    // 這裡修正了路徑：導向 ../booking/booking.php (購票首頁)
    echo "<script>
            alert('🎉 儲值成功！\\n目前餘額：NT$ " . number_format($newBalance) . "');
            window.location.href = '../booking/booking.php'; 
          </script>";

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<script>
            alert('❌ 儲值失敗：" . addslashes($e->getMessage()) . "');
            window.history.back();
          </script>";
}

closeConnection($conn);
?>