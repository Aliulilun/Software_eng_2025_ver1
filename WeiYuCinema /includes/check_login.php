<?php
/**
 * 會員登入狀態檢查
 * 確保只有已登入的一般會員可以訪問此頁面
 * 威宇影城售票系統
 */
session_start();

// 檢查是否已登入
if (!isset($_SESSION['memberId'])) {
    // 未登入，導向登入頁面
    header("Location: /WeiYuCinema/auth/login.php?error=not_logged_in");
    exit();
}

// 檢查是否為一般會員（非管理者）
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
    // 管理者應該訪問管理者頁面
    header("Location: /WeiYuCinema/admin/index.php");
    exit();
}

// 更新最後活動時間
$_SESSION['last_activity'] = time();

// 定義會員資訊變數供頁面使用
$memberId = $_SESSION['memberId'];
$memberName = $_SESSION['memberName'];
$roleId = $_SESSION['role_id'];
?>
