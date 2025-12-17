<?php
/**
 * 登入處理後端
 * 威宇影城售票系統
 */
session_start();
require_once '../config/db_connect.php';

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// 取得表單資料
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// 驗證必填欄位
if (empty($email) || empty($password)) {
    header("Location: login.php?error=empty");
    exit();
}

// 防止 SQL Injection
$email = mysqli_real_escape_string($conn, $email);
$password = mysqli_real_escape_string($conn, $password);

// 查詢會員資料
$sql = "SELECT memberId, memberName, memberPwd, memberConfirm, role_id 
        FROM memberProfile 
        WHERE member = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 檢查帳號是否存在
if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: login.php?error=invalid");
    exit();
}

// 取得會員資料
$member = mysqli_fetch_assoc($result);

// 驗證密碼（注意：實際應用應使用 password_hash 和 password_verify）
if ($password !== $member['memberPwd']) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: login.php?error=invalid");
    exit();
}

// 檢查帳號是否已驗證
if ($member['memberConfirm'] !== 'yes') {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: login.php?error=notconfirmed");
    exit();
}

// 登入成功，設定 Session
$_SESSION['memberId'] = $member['memberId'];
$_SESSION['memberName'] = $member['memberName'];
$_SESSION['role_id'] = $member['role_id'];
$_SESSION['login_time'] = time();

mysqli_stmt_close($stmt);
closeConnection($conn);

// 根據角色導向不同頁面
if ($member['role_id'] == 1) {
    // 管理者導向管理者頁面
    header("Location: /WeiYuCinema/admin/index.php");
} else {
    // 一般會員導向會員頁面
    header("Location: /WeiYuCinema/member/index.php");
}
exit();
?>
