<?php
/**
 * 會員資料管理 (Member Change) - 主頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

$memberId = $_SESSION['memberId'];
$memberName = $_SESSION['memberName'];

$profile = [];
$message = '';
$messageType = '';

// 取得會員詳細資料
$sql = "SELECT memberName, member, memberPhone, memberBirth, memberPayAccount FROM memberProfile WHERE memberId = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $memberId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result && mysqli_num_rows($result) > 0) {
    $profile = mysqli_fetch_assoc($result);
}
mysqli_stmt_close($stmt);

// 處理訊息參數
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $messageType = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'info';
}

// 處理錯誤訊息
if (isset($_GET['error'])) {
    $messageType = 'error';
    switch ($_GET['error']) {
        case 'update_failed':
            $message = '資料更新失敗，請稍後再試。';
            break;
        case 'password_mismatch':
            $message = '新密碼與確認密碼不一致。';
            break;
        case 'current_password_wrong':
            $message = '目前密碼不正確。';
            break;
        case 'invalid_email':
            $message = '電子信箱格式不正確。';
            break;
        case 'invalid_phone':
            $message = '手機號碼格式不正確（需為10位數字）。';
            break;
        case 'invalid_birth':
            $message = '生日格式不正確。';
            break;
        case 'invalid_pay_account':
            $message = '付款帳號格式不正確。';
            break;
        case 'email_exists':
            $message = '此電子信箱已被其他會員使用。';
            break;
        case 'pay_account_exists':
            $message = '此付款帳號已被其他會員使用。';
            break;
        case 'empty_fields':
            $message = '請填寫所有必填欄位。';
            break;
        case 'password_length':
            $message = '密碼長度需介於6-50個字元之間。';
            break;
        case 'member_not_found':
            $message = '會員資料不存在。';
            break;
        default:
            $message = '操作失敗，請稍後再試。';
    }
}

// 處理成功訊息
if (isset($_GET['success'])) {
    $messageType = 'success';
    switch ($_GET['success']) {
        case 'profile_updated':
            $message = '會員資料更新成功！';
            break;
        case 'password_updated':
            $message = '密碼更新成功！';
            break;
        default:
            $message = '操作成功！';
    }
}

include 'templates/profile.html';
closeConnection($conn);
?>