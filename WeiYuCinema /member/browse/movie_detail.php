<?php
/**
 * 電影詳細資訊頁面 (最終路徑修正版)
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 定義完整網域路徑
$full_domain = 'http://localhost' . BASE_URL; 
// 預期結果: http://localhost/WeiYuCinema/

// 取得電影 ID
$movieId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 如果沒有 ID，導回電影列表
if ($movieId <= 0) {
    header("Location: " . $full_domain . "member/browse/movies.php?error=invalid_id");
    exit();
}

// 查詢電影詳細資訊
$sql = "SELECT m.*, g.gradeName, mt.movieTypeName 
        FROM movie m
        LEFT JOIN grade g ON m.gradeId = g.gradeId
        LEFT JOIN movieType mt ON m.movieTypeId = mt.movieTypeId
        WHERE m.movieId = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $movieId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: " . $full_domain . "member/browse/movies.php?error=not_found");
    exit();
}

$movie = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 查詢此電影的場次
$showingSql = "SELECT s.*, c.cinemaName, t.theaterName, v.versionName
               FROM showing s
               LEFT JOIN theater t ON s.theaterId = t.theaterId
               LEFT JOIN cinema c ON t.cinemaId = c.cinemaId
               LEFT JOIN playVersion v ON s.versionId = v.versionId
               WHERE s.movieId = ?
               ORDER BY s.showingDate, s.startTime";

$showingStmt = mysqli_prepare($conn, $showingSql);
mysqli_stmt_bind_param($showingStmt, "i", $movieId);
mysqli_stmt_execute($showingStmt);
$showingResult = mysqli_stmt_get_result($showingStmt);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['movieName']); ?> - 威宇影城</title>
    <link rel="stylesheet" href="<?php echo $full_domain; ?>static/css/style.css">
    <style>
        /* --- 頁面專用 CSS --- */
        :root {
            --primary-color: #ffc107;
            --secondary-color: #17a2b8;
            --bg-dark: #1a1a1a;
            --bg-card: #2c2c2c;
            --text-light: #eee;
            --text-muted: #888;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            font-family: sans-serif;
            margin: 0;
        }

        a { text-decoration: none; color: var(--primary-color); }
        a:hover { text-decoration: underline; }

        /* 導覽列 */
        header {
            background: #111;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
        }
        header h1 { margin: 0; font-size: 24px; color: var(--primary-color); }
        header nav span { color: var(--text-muted); margin-right: 10px; }
        header nav a { margin-left: 10px; color: #ccc; font-size: 14px; }
        header nav a:hover { color: white; }
        hr { border: 0; border-top: 1px solid #333; margin: 0; }

        /* 容器與區塊 */
        .container { max-width: 1000px; margin: 0 auto; padding: 30px 20px; }
        .section-box {
            background: var(--bg-card); padding: 25px; border-radius: 12px;
            margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        h2, h3 { color: var(--primary-color); margin-top: 0; }

        /* 電影資訊排版 */
        .movie-hero { display: flex; gap: 30px; }
        .poster-wrapper { flex-shrink: 0; width: 300px; }
        .poster-wrapper img { width: 100%; height: auto; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
        .poster-placeholder { width: 100%; height: 450px; background: #333; color: #666; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 18px; }
        .info-wrapper { flex: 1; }

        /* 表格樣式 */
        .styled-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .styled-table th, .styled-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #444; }
        .styled-table th { color: var(--text-muted); font-weight: normal; width: 100px; white-space: nowrap; }
        .styled-table td { color: var(--text-light); }

        /* 按鈕樣式 */
        .action-buttons { margin-top: 25px; display: flex; gap: 15px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 10px 20px; font-size: 15px; font-weight: bold; text-align: center; border-radius: 6px; cursor: pointer; transition: all 0.2s; border: none; }
        .btn:hover { text-decoration: none; transform: translateY(-2px); }
        .btn-gold { background-color: var(--primary-color); color: #000; }
        .btn-gold:hover { background-color: #e0a800; }
        .btn-blue { background-color: var(--secondary-color); color: #fff; }
        .btn-blue:hover { background-color: #138496; }
        .btn-outline { background-color: transparent; border: 1px solid #666; color: #ccc; }
        .btn-outline:hover { border-color: #fff; color: #fff; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }

        .synopsis-text { line-height: 1.6; color: #ccc; }
        .time-highlight { color: var(--primary-color); font-weight: bold; font-size: 1.1em; }

        @media (max-width: 768px) {
            header { flex-direction: column; gap: 15px; text-align: center; }
            .movie-hero { flex-direction: column; align-items: center; }
            .poster-wrapper { width: 240px; }
            .action-buttons { flex-direction: column; }
            .btn { width: 100%; box-sizing: border-box; }
            .showings-table th:nth-child(1), .showings-table td:nth-child(1),
            .showings-table th:nth-child(3), .showings-table td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>
    <header>
        <h1>威宇影城 - 電影詳細資訊</h1>
        <nav>
            <span>歡迎，<?php echo htmlspecialchars($memberName); ?>！</span>
            <a href="<?php echo $full_domain; ?>member/index.php">會員首頁</a> | 
            <a href="<?php echo $full_domain; ?>member/browse/movies.php">瀏覽電影</a> | 
            <a href="<?php echo $full_domain; ?>member/browse/cinemas.php">瀏覽影城</a> | 
            <a href="<?php echo $full_domain; ?>member/browse/showings.php">查詢場次</a> | 
            <a href="<?php echo $full_domain; ?>member/booking/booking.php">購票服務</a> | 
            <a href="<?php echo $full_domain; ?>member/inquiry/index.php">訂票紀錄</a> | 
            <a href="<?php echo $full_domain; ?>member/topup/index.php">儲值卡</a> | 
            <a href="<?php echo $full_domain; ?>member/profile/profile.php">會員資料</a> | 
            <a href="<?php echo $full_domain; ?>logout.php">登出</a>
        </nav>
    </header>
    
    <main class="container">
        <section class="section-box">
            <h2><?php echo htmlspecialchars($movie['movieName']); ?></h2>
            
            <div class="movie-hero">
                <div class="poster-wrapper">
                    <?php if (!empty($movie['movieImg'])): ?>
                        <img src="<?php echo $full_domain; ?>images/movies/<?php echo htmlspecialchars($movie['movieImg']); ?>" 
                             alt="<?php echo htmlspecialchars($movie['movieName']); ?>">
                    <?php else: ?>
                        <div class="poster-placeholder">無海報</div>
                    <?php endif; ?>
                </div>
                
                <div class="info-wrapper">
                    <table class="styled-table">
                        <tr><th>電影名稱</th><td><?php echo htmlspecialchars($movie['movieName']); ?></td></tr>
                        <tr><th>片長</th><td><?php echo htmlspecialchars($movie['movieTime']); ?> 分鐘</td></tr>
                        <tr><th>分級</th><td><span style="background:#444; padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($movie['gradeName']); ?></span></td></tr>
                        <tr><th>類型</th><td><?php echo htmlspecialchars($movie['movieTypeName']); ?></td></tr>
                        <tr><th>導演</th><td><?php echo htmlspecialchars($movie['director']); ?></td></tr>
                        <tr><th>主演</th><td><?php echo htmlspecialchars($movie['actors']); ?></td></tr>
                        <tr><th>上映日期</th><td><?php echo htmlspecialchars($movie['movieStart']); ?></td></tr>
                    </table>
                    
                    <div class="action-buttons">
                        <a href="<?php echo $full_domain; ?>member/booking/booking.php?movieId=<?php echo $movie['movieId']; ?>" class="btn btn-gold">
                            🎫 立即購票
                        </a>
                        <a href="#showings-section" class="btn btn-blue">
                            🕒 查看場次
                        </a>
                        <a href="<?php echo $full_domain; ?>member/browse/movies.php" class="btn btn-outline">
                            ← 返回列表
                        </a>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="section-box">
            <h3>電影介紹</h3>
            <div class="synopsis-text">
                <?php echo nl2br(htmlspecialchars($movie['movieInfo'])); ?>
            </div>
        </section>
        
        <section class="section-box" id="showings-section">
            <h3>放映場次</h3>
            
            <?php if (mysqli_num_rows($showingResult) > 0): ?>
                <table class="styled-table showings-table">
                    <thead>
                        <tr>
                            <th>場次編號</th>
                            <th>影城</th>
                            <th>影廳</th>
                            <th>版本</th>
                            <th>日期</th>
                            <th>時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($showing = mysqli_fetch_assoc($showingResult)): ?>
                            <tr>
                                <td style="color: #666;">#<?php echo htmlspecialchars($showing['showingId']); ?></td>
                                <td><?php echo htmlspecialchars($showing['cinemaName']); ?></td>
                                <td><?php echo htmlspecialchars($showing['theaterName']); ?></td>
                                <td>
                                    <span style="border:1px solid #555; padding:1px 5px; border-radius:3px; font-size:12px;">
                                        <?php echo htmlspecialchars($showing['versionName']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y/m/d (D)', strtotime($showing['showingDate'])); ?></td>
                                <td class="time-highlight"><?php echo date('H:i', strtotime($showing['startTime'])); ?></td>
                                <td>
                                    <a href="<?php echo $full_domain; ?>member/booking/booking.php?showingId=<?php echo $showing['showingId']; ?>" class="btn btn-blue btn-sm">
                                        購票
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                    <p style="font-size: 40px; margin-bottom: 10px;">🎬</p>
                    <p>目前此電影沒有放映場次。</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <?php
    mysqli_stmt_close($showingStmt);
    closeConnection($conn);
    ?>
</body>
</html>