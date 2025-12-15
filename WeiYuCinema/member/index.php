<?php
/**
 * 會員首頁
 * 威宇影城售票系統
 */
require_once '../includes/check_login.php';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員中心 - 威宇影城</title>
    <link rel="stylesheet" href="/WeiYuCinema/static/css/style.css">
</head>
<body>
    <!-- 頁首佔位符 -->
    <div id="header-placeholder"></div>
    
    <!-- 主要內容 -->
    <div class="container">
        <div class="page-title">
            <h1>會員中心</h1>
            <p>歡迎回來，<?php echo htmlspecialchars($memberName); ?>！</p>
        </div>
        
        <div class="section-box">
            <h2 class="section-title">🎬 快速功能</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <a href="browse/movies.php" class="feature-card">
                    <div style="text-align: center; padding: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">📽️</div>
                        <h3 style="margin: 0 0 10px 0;">瀏覽電影資訊</h3>
                        <p style="margin: 0; opacity: 0.9;">查看最新電影、劇情介紹</p>
                    </div>
                </a>
                
                <a href="booking/booking.php" class="feature-card">
                    <div style="text-align: center; padding: 25px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">🎟️</div>
                        <h3 style="margin: 0 0 10px 0;">立即購票</h3>
                        <p style="margin: 0; opacity: 0.9;">線上選位、便利購票</p>
                    </div>
                </a>
                
                <a href="inquiry/index.php" class="feature-card">
                    <div style="text-align: center; padding: 25px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">📋</div>
                        <h3 style="margin: 0 0 10px 0;">訂票紀錄查詢</h3>
                        <p style="margin: 0; opacity: 0.9;">查看我的訂票記錄</p>
                    </div>
                </a>
                
                <a href="topup/index.php" class="feature-card">
                    <div style="text-align: center; padding: 25px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">💳</div>
                        <h3 style="margin: 0 0 10px 0;">儲值卡管理</h3>
                        <p style="margin: 0; opacity: 0.9;">儲值、查看餘額</p>
                    </div>
                </a>
                
                <a href="profile/profile.php" class="feature-card">
                    <div style="text-align: center; padding: 25px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">👤</div>
                        <h3 style="margin: 0 0 10px 0;">個人資料管理</h3>
                        <p style="margin: 0; opacity: 0.9;">修改個人資料、密碼</p>
                    </div>
                </a>
                
                <a href="browse/cinemas.php" class="feature-card">
                    <div style="text-align: center; padding: 25px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 12px; text-decoration: none; color: #333; transition: transform 0.3s;">
                        <div style="font-size: 48px; margin-bottom: 15px;">🏢</div>
                        <h3 style="margin: 0 0 10px 0;">影城資訊</h3>
                        <p style="margin: 0; opacity: 0.8;">查看影城位置、設施</p>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="section-box">
            <h3 class="section-title">📊 會員資訊</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="background: #2c2c2c; padding: 20px; border-radius: 10px; text-align: center;">
                    <h4 style="color: var(--primary-color); margin-top: 0;">會員編號</h4>
                    <p style="font-size: 18px; font-weight: bold; margin: 0;"><?php echo htmlspecialchars($memberId); ?></p>
                </div>
                <div style="background: #2c2c2c; padding: 20px; border-radius: 10px; text-align: center;">
                    <h4 style="color: var(--primary-color); margin-top: 0;">會員姓名</h4>
                    <p style="font-size: 18px; font-weight: bold; margin: 0;"><?php echo htmlspecialchars($memberName); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 頁尾佔位符 -->
    <div id="footer-placeholder"></div>
    
    <!-- 載入核心JavaScript -->
    <script src="/WeiYuCinema/static/js/script.js"></script>
    
    <script>
        // 功能卡片懸停效果
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.firstElementChild.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.firstElementChild.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
