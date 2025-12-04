<?php
/**
 * 會員密碼更新處理
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
$currentPassword = isset($_POST['currentPassword']) ? trim($_POST['currentPassword']) : '';
$newPassword = isset($_POST['newPassword']) ? trim($_POST['newPassword']) : '';
$confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';

// 驗證必填欄位
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    header("Location: profile.php?error=empty_fields");
    exit();
}

// 驗證新密碼與確認密碼是否一致
if ($newPassword !== $confirmPassword) {
    header("Location: profile.php?error=password_mismatch");
    exit();
}

// 驗證密碼長度
if (strlen($newPassword) < 6 || strlen($newPassword) > 50) {
    header("Location: profile.php?error=password_length");
    exit();
}

// 檢查目前密碼是否正確
$checkPasswordSql = "SELECT memberPwd FROM memberProfile WHERE memberId = ?";
$stmt = mysqli_prepare($conn, $checkPasswordSql);
mysqli_stmt_bind_param($stmt, "s", $memberId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: profile.php?error=member_not_found");
    exit();
}

$memberData = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 驗證目前密碼（注意：實際應用中應該使用 password_verify）
if ($currentPassword !== $memberData['memberPwd']) {
    closeConnection($conn);
    header("Location: profile.php?error=current_password_wrong");
    exit();
}

// 更新密碼（注意：實際應用中應該使用 password_hash）
$updatePasswordSql = "UPDATE memberProfile SET memberPwd = ? WHERE memberId = ?";
$stmt = mysqli_prepare($conn, $updatePasswordSql);
mysqli_stmt_bind_param($stmt, "ss", $newPassword, $memberId);

if (mysqli_stmt_execute($stmt)) {
    // 密碼更新成功
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: profile.php?success=password_updated");
    exit();
} else {
    // 密碼更新失敗
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: profile.php?error=update_failed");
    exit();
}
?>
