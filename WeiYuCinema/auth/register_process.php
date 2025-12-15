<?php
/**
 * 註冊處理後端
 * 威宇影城售票系統
 */
session_start();
require_once '../config/db_connect.php';

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// 取得表單資料
$memberName = isset($_POST['memberName']) ? trim($_POST['memberName']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';
$memberPhone = isset($_POST['memberPhone']) ? trim($_POST['memberPhone']) : '';
$memberBirth = isset($_POST['memberBirth']) ? trim($_POST['memberBirth']) : '';
$memberPayAccount = isset($_POST['memberPayAccount']) ? trim($_POST['memberPayAccount']) : '';

// 驗證必填欄位
if (empty($memberName) || empty($email) || empty($password) || empty($confirmPassword) || empty($memberPhone) || empty($memberBirth)) {
    header("Location: register.php?error=empty");
    exit();
}

// 驗證密碼是否一致
if ($password !== $confirmPassword) {
    header("Location: register.php?error=password_mismatch");
    exit();
}

// 驗證電子信箱格式
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=invalid_email");
    exit();
}

// 驗證手機格式
if (!preg_match('/^09[0-9]{8}$/', $memberPhone)) {
    header("Location: register.php?error=invalid_phone");
    exit();
}

// 防止 SQL Injection
$memberName = mysqli_real_escape_string($conn, $memberName);
$email = mysqli_real_escape_string($conn, $email);
$password = mysqli_real_escape_string($conn, $password);
$memberPhone = mysqli_real_escape_string($conn, $memberPhone);
$memberBirth = mysqli_real_escape_string($conn, $memberBirth);
$memberPayAccount = mysqli_real_escape_string($conn, $memberPayAccount);

// 檢查電子信箱是否已被註冊
$checkEmail = "SELECT memberId FROM memberProfile WHERE member = ?";
$stmt = mysqli_prepare($conn, $checkEmail);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: register.php?error=email_exists");
    exit();
}
mysqli_stmt_close($stmt);

// 產生會員編號
$sql = "SELECT memberId FROM memberProfile ORDER BY memberId DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$lastMember = mysqli_fetch_assoc($result);

if ($lastMember) {
    $lastId = intval(substr($lastMember['memberId'], 1));
    $newId = 'M' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
} else {
    $newId = 'M0001';
}

// 如果付款帳號為空，產生預設帳號
if (empty($memberPayAccount)) {
    $memberPayAccount = 'ACC' . str_pad($lastId + 1, 11, '0', STR_PAD_LEFT);
}

// 插入會員資料（注意：實際應用應使用 password_hash）
$insertSql = "INSERT INTO memberProfile 
              (memberId, memberName, member, memberPwd, memberPhone, memberBirth, memberPayAccount, memberConfirm, role_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'yes', 0)";

$stmt = mysqli_prepare($conn, $insertSql);
mysqli_stmt_bind_param($stmt, "sssssss", $newId, $memberName, $email, $password, $memberPhone, $memberBirth, $memberPayAccount);

if (mysqli_stmt_execute($stmt)) {
    // 註冊成功，同時建立會員儲值卡（初始餘額為 0）
    $insertCard = "INSERT INTO memberCashCard (memberId, balance) VALUES (?, 0)";
    $stmt2 = mysqli_prepare($conn, $insertCard);
    mysqli_stmt_bind_param($stmt2, "s", $newId);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
    
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: register.php?success=1");
    exit();
} else {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: register.php?error=unknown");
    exit();
}
?>

