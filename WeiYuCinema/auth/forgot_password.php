<?php
/**
 * 忘記密碼頁面
 * 威宇影城售票系統
 */
session_start();

// 如果已經登入，導向首頁
if (isset($_SESSION['memberId'])) {
    header("Location: /WeiYuCinema/member/index.php");
    exit();
}

// 取得錯誤訊息（如果有）
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// 處理錯誤訊息
$errorMessage = '';
if ($error) {
    switch ($error) {
        case 'email_not_found':
            $errorMessage = '此電子信箱尚未註冊';
            break;
        case 'empty':
            $errorMessage = '請填寫所有欄位';
            break;
        case 'password_mismatch':
            $errorMessage = '兩次輸入的密碼不一致';
            break;
        case 'invalid_email':
            $errorMessage = '電子信箱格式不正確';
            break;
        default:
            $errorMessage = '操作失敗，請稍後再試';
    }
}

// 載入 HTML 模板
include 'templates/forgot_password.html';
?>

