<?php
/**
 * 威宇影城售票系統 - 首頁（入口頁面）
 */
session_start();

// 如果已經登入，根據角色導向不同頁面
if (isset($_SESSION['memberId'])) {
    if ($_SESSION['role_id'] == 1) {
        header("Location: /WeiYuCinema/admin/index.php");
    } else {
        header("Location: /WeiYuCinema/member/index.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>威宇影城售票系統</title>
    <link rel="stylesheet" href="/WeiYuCinema/static/css/style.css">
</head>
<body>
    <!-- 頁首佔位符 -->
    <div id="header-placeholder"></div>
    
    <!-- 主要內容 -->
    <div class="container">
        <div class="page-title">
            <h1>IMAX 震撼視聽</h1>
            <p>全台頂級影廳，給您身歷其境的感動</p>
        </div>
        
        <div class="section-box text-center">
            <h2 class="section-title">🎬 立即訂票</h2>
            <p class="text-muted mb-3">體驗最新電影，享受頂級視聽盛宴</p>
            
            <div style="margin: 30px 0;">
                <a href="/WeiYuCinema/auth/login.php" class="btn btn-primary" style="margin: 10px; font-size: 18px; padding: 15px 30px;">
                    會員登入
                </a>
                <a href="/WeiYuCinema/auth/register.php" class="btn btn-warning" style="margin: 10px; font-size: 18px; padding: 15px 30px;">
                    註冊新帳號
                </a>
            </div>
        </div>
        
        <div class="section-box">
            <h3 class="section-title">🔥 現正熱映</h3>
            <div class="text-center">

            </div>
        </div>
        
        <div class="section-box">
            <h3 class="section-title">🎯 系統功能</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="text-align: center; padding: 20px; background: #2c2c2c; border-radius: 10px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🎥</div>
                    <h4>電影資訊查詢</h4>
                    <p class="text-muted">最新電影資訊、劇情介紹</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #2c2c2c; border-radius: 10px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🏢</div>
                    <h4>影城資訊查詢</h4>
                    <p class="text-muted">全台影城位置、設施介紹</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #2c2c2c; border-radius: 10px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🎬</div>
                    <h4>場次查詢</h4>
                    <p class="text-muted">即時場次、座位狀況查詢</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #2c2c2c; border-radius: 10px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🎟️</div>
                    <h4>線上訂票</h4>
                    <p class="text-muted">便利訂票、選位服務</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #2c2c2c; border-radius: 10px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">💳</div>
                    <h4>會員儲值卡</h4>
                    <p class="text-muted">儲值優惠、消費紀錄</p>
                </div>
                <div style="text-align: center; padding: 20px; background: #2c2c2c; border-radius: 10px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">👤</div>
                    <h4>會員資料管理</h4>
                    <p class="text-muted">個人資料、密碼變更</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 頁尾佔位符 -->
    <div id="footer-placeholder"></div>
    
    <!-- 載入核心JavaScript -->
    <script src="/WeiYuCinema/static/js/script.js"></script>
</body>
</html>
