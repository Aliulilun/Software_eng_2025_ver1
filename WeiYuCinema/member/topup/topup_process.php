<?php
/**
 * 儲值處理後端
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// 取得表單資料
$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

// 驗證儲值金額
if ($amount <= 0) {
    header("Location: index.php?error=invalid_amount");
    exit();
}

if ($amount < 100) {
    header("Location: index.php?error=min_amount");
    exit();
}

if ($amount > 10000) {
    header("Location: index.php?error=max_amount");
    exit();
}

// 驗證付款方式
$validPaymentMethods = ['credit_card', 'debit_card', 'bank_transfer', 'mobile_pay'];
if (!in_array($paymentMethod, $validPaymentMethods)) {
    header("Location: index.php?error=invalid_payment");
    exit();
}

// 開始資料庫交易
mysqli_autocommit($conn, FALSE);

try {
    // 取得目前餘額
    $balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ? FOR UPDATE";
    $balanceStmt = mysqli_prepare($conn, $balanceSql);
    mysqli_stmt_bind_param($balanceStmt, "s", $memberId);
    mysqli_stmt_execute($balanceStmt);
    $balanceResult = mysqli_stmt_get_result($balanceStmt);
    
    if (mysqli_num_rows($balanceResult) === 0) {
        throw new Exception("找不到會員儲值卡");
    }
    
    $balanceData = mysqli_fetch_assoc($balanceResult);
    $currentBalance = $balanceData['balance'];
    $newBalance = $currentBalance + $amount;
    
    mysqli_stmt_close($balanceStmt);
    
    // 更新餘額
    $updateSql = "UPDATE memberCashCard SET balance = ? WHERE memberId = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($updateStmt, "is", $newBalance, $memberId);
    
    if (!mysqli_stmt_execute($updateStmt)) {
        throw new Exception("更新餘額失敗");
    }
    mysqli_stmt_close($updateStmt);
    
    // 產生交易編號
    $transactionId = 'T' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    
    // 檢查交易編號是否重複
    $checkTxnSql = "SELECT transactionId FROM topupTransaction WHERE transactionId = ?";
    $checkTxnStmt = mysqli_prepare($conn, $checkTxnSql);
    mysqli_stmt_bind_param($checkTxnStmt, "s", $transactionId);
    mysqli_stmt_execute($checkTxnStmt);
    $checkTxnResult = mysqli_stmt_get_result($checkTxnStmt);
    
    // 如果重複，重新產生
    while (mysqli_num_rows($checkTxnResult) > 0) {
        $transactionId = 'T' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        mysqli_stmt_execute($checkTxnStmt);
        $checkTxnResult = mysqli_stmt_get_result($checkTxnStmt);
    }
    mysqli_stmt_close($checkTxnStmt);
    
    // 付款方式中文對照
    $paymentNames = [
        'credit_card' => '信用卡',
        'debit_card' => '金融卡',
        'bank_transfer' => '銀行轉帳',
        'mobile_pay' => '行動支付'
    ];
    
    $description = '線上儲值 (' . $paymentNames[$paymentMethod] . ')';
    
    // 新增交易紀錄
    $transactionSql = "INSERT INTO topupTransaction 
                       (transactionId, memberId, transactionType, amount, balanceBefore, balanceAfter, description, status) 
                       VALUES (?, ?, 'TOPUP', ?, ?, ?, ?, 'SUCCESS')";
    
    $transactionStmt = mysqli_prepare($conn, $transactionSql);
    mysqli_stmt_bind_param($transactionStmt, "ssiiss", 
        $transactionId, $memberId, $amount, $currentBalance, $newBalance, $description);
    
    if (!mysqli_stmt_execute($transactionStmt)) {
        throw new Exception("新增交易紀錄失敗");
    }
    mysqli_stmt_close($transactionStmt);
    
    // 提交交易
    mysqli_commit($conn);
    
    // 儲值成功
    header("Location: index.php?success=topup");
    exit();
    
} catch (Exception $e) {
    // 回滾交易
    mysqli_rollback($conn);
    
    // 記錄錯誤（實際應用中應該寫入日誌檔）
    error_log("Topup failed for member $memberId: " . $e->getMessage());
    
    header("Location: index.php?error=db_error");
    exit();
    
} finally {
    // 恢復自動提交
    mysqli_autocommit($conn, TRUE);
    closeConnection($conn);
}
?>
