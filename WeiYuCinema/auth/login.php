<?php
/**
 * 會員登入頁面
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
$errorMessage = '';

switch ($error) {
    case 'invalid':
        $errorMessage = '帳號或密碼錯誤，請重新輸入';
        break;
    case 'empty':
        $errorMessage = '請輸入帳號和密碼';
        break;
    case 'notconfirmed':
        $errorMessage = '您的帳號尚未驗證，請先完成驗證';
        break;
    case 'not_logged_in':
        $errorMessage = '請先登入才能訪問此頁面';
        break;
    default:
        if ($error) {
            $errorMessage = '登入失敗，請稍後再試';
        }
}

// 載入 HTML 模板
include 'templates/login.html';
?>
