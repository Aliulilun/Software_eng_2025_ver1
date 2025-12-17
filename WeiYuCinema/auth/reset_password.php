<?php
/**
 * 密碼重設處理後端
 * 威宇影城售票系統
 */
session_start();
require_once '../config/db_connect.php';

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit();
}

// 取得表單資料
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$memberPhone = isset($_POST['memberPhone']) ? trim($_POST['memberPhone']) : '';
$memberBirth = isset($_POST['memberBirth']) ? trim($_POST['memberBirth']) : '';
$newPassword = isset($_POST['newPassword']) ? trim($_POST['newPassword']) : '';
$confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';

// 驗證必填欄位
if (empty($email) || empty($memberPhone) || empty($memberBirth) || empty($newPassword) || empty($confirmPassword)) {
    header("Location: forgot_password.php?error=empty");
    exit();
}

// 驗證密碼是否一致
if ($newPassword !== $confirmPassword) {
    header("Location: forgot_password.php?error=password_mismatch");
    exit();
}

// 驗證電子信箱格式
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: forgot_password.php?error=invalid_email");
    exit();
}

// 驗證手機號碼格式
if (!preg_match('/^09[0-9]{8}$/', $memberPhone)) {
    header("Location: forgot_password.php?error=invalid_phone");
    exit();
}

// 驗證生日格式
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $memberBirth)) {
    header("Location: forgot_password.php?error=invalid_birth");
    exit();
}

// 防止 SQL Injection
$email = mysqli_real_escape_string($conn, $email);
$memberPhone = mysqli_real_escape_string($conn, $memberPhone);
$memberBirth = mysqli_real_escape_string($conn, $memberBirth);
$newPassword = mysqli_real_escape_string($conn, $newPassword);

// 檢查會員資料是否完全匹配（電子信箱、電話、生日）
$checkMember = "SELECT memberId FROM memberprofile WHERE member = ? AND memberPhone = ? AND memberBirth = ?";
$stmt = mysqli_prepare($conn, $checkMember);
mysqli_stmt_bind_param($stmt, "sss", $email, $memberPhone, $memberBirth);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: forgot_password.php?error=member_info_mismatch");
    exit();
}

$member = mysqli_fetch_assoc($result);
$memberId = $member['memberId'];
mysqli_stmt_close($stmt);

// 更新密碼（注意：實際應用應使用 password_hash）
$updateSql = "UPDATE memberprofile SET memberPwd = ? WHERE memberId = ?";
$stmt = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($stmt, "ss", $newPassword, $memberId);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: forgot_password.php?success=1");
    exit();
} else {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: forgot_password.php?error=unknown");
    exit();
}
?>

