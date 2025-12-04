<?php
/**
 * 登出功能
 * 威宇影城售票系統
 */
session_start();

// 清除所有 Session 變數
$_SESSION = array();

// 刪除 Session Cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// 銷毀 Session
session_destroy();

// 導向登入頁面
header("Location: login.php");
exit();
?>
