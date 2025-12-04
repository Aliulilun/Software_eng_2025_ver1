<?php
/**
 * 資料庫連線設定檔
 * 威宇影城售票系統
 */

// 資料庫連線參數
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'phil930820');
define('DB_NAME', 'WeiYuCinema');

// 建立資料庫連線
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 設定字元編碼為 UTF-8
mysqli_set_charset($conn, "utf8mb4");

// 檢查連線
if (!$conn) {
    die("資料庫連線失敗: " . mysqli_connect_error());
}

// 函數：安全地關閉資料庫連線
function closeConnection($connection) {
    if ($connection) {
        mysqli_close($connection);
    }
}
?>
