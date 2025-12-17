<?php
/**
 * Session 狀態 API
 * 威宇影城售票系統 - 返回 JSON 格式的登入狀態
 */
session_start();

// 設置 JSON 回應標頭
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 檢查登入狀態
$response = [
    'isLoggedIn' => false,
    'role' => null,
    'memberName' => null,
    'memberId' => null,
    'timestamp' => date('Y-m-d H:i:s')
];

if (isset($_SESSION['memberId']) && isset($_SESSION['memberName'])) {
    $response['isLoggedIn'] = true;
    $response['memberId'] = $_SESSION['memberId'];
    $response['memberName'] = $_SESSION['memberName'];
    $response['role'] = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0;
}

// 輸出 JSON 回應
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

