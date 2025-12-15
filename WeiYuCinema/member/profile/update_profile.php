<?php
/**
 * 會員基本資料更新處理
 * 威宇影城售票系統
 */
session_start();
require_once '../../config/db_connect.php';

// 檢查登入狀態
if (!isset($_SESSION['memberId'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php");
    exit();
}

$memberId = $_SESSION['memberId'];

// 取得表單資料
$memberName = isset($_POST['memberName']) ? trim($_POST['memberName']) : '';
$member = isset($_POST['member']) ? trim($_POST['member']) : '';
$memberPhone = isset($_POST['memberPhone']) ? trim($_POST['memberPhone']) : '';
$memberBirth = isset($_POST['memberBirth']) ? trim($_POST['memberBirth']) : '';
$memberPayAccount = isset($_POST['memberPayAccount']) ? trim($_POST['memberPayAccount']) : '';

// 驗證必填欄位
if (empty($memberName) || empty($member) || empty($memberPhone) || empty($memberBirth) || empty($memberPayAccount)) {
    header("Location: profile.php?error=empty_fields");
    exit();
}

// 驗證電子信箱格式
if (!filter_var($member, FILTER_VALIDATE_EMAIL)) {
    header("Location: profile.php?error=invalid_email");
    exit();
}

// 驗證手機號碼格式（10位數字）
if (!preg_match('/^[0-9]{10}$/', $memberPhone)) {
    header("Location: profile.php?error=invalid_phone");
    exit();
}

// 驗證生日格式
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $memberBirth)) {
    header("Location: profile.php?error=invalid_birth");
    exit();
}

// 驗證付款帳號格式（3個英文字母 + 11個數字）
if (!preg_match('/^[A-Z]{3}[0-9]{11}$/', $memberPayAccount)) {
    header("Location: profile.php?error=invalid_pay_account");
    exit();
}

// 檢查電子信箱是否已被其他會員使用
$checkEmailSql = "SELECT memberId FROM memberProfile WHERE member = ? AND memberId != ?";
$stmt = mysqli_prepare($conn, $checkEmailSql);
mysqli_stmt_bind_param($stmt, "ss", $member, $memberId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: profile.php?error=email_exists");
    exit();
}
mysqli_stmt_close($stmt);

// 檢查付款帳號是否已被其他會員使用
$checkPayAccountSql = "SELECT memberId FROM memberProfile WHERE memberPayAccount = ? AND memberId != ?";
$stmt = mysqli_prepare($conn, $checkPayAccountSql);
mysqli_stmt_bind_param($stmt, "ss", $memberPayAccount, $memberId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: profile.php?error=pay_account_exists");
    exit();
}
mysqli_stmt_close($stmt);

// 更新會員資料
$updateSql = "UPDATE memberProfile SET 
              memberName = ?, 
              member = ?, 
              memberPhone = ?, 
              memberBirth = ?, 
              memberPayAccount = ? 
              WHERE memberId = ?";

$stmt = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($stmt, "ssssss", $memberName, $member, $memberPhone, $memberBirth, $memberPayAccount, $memberId);

if (mysqli_stmt_execute($stmt)) {
    // 更新成功，同時更新 session 中的會員姓名
    $_SESSION['memberName'] = $memberName;
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: profile.php?success=profile_updated");
    exit();
} else {
    // 更新失敗
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: profile.php?error=update_failed");
    exit();
}
?>
