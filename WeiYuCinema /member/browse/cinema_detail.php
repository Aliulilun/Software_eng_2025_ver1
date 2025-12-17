<?php
/**
 * 影城詳細資訊頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得影城 ID
$cinemaId = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($cinemaId)) {
    header("Location: browse_cinemas.php?error=invalid_id");
    exit();
}

// 查詢影城詳細資訊
$sql = "SELECT * FROM cinema WHERE cinemaId = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $cinemaId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: browse_cinemas.php?error=not_found");
    exit();
}

$cinema = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 查詢此影城的影廳
$theaterSql = "SELECT * FROM theater WHERE cinemaId = ? ORDER BY theaterId";
$theaterStmt = mysqli_prepare($conn, $theaterSql);
mysqli_stmt_bind_param($theaterStmt, "s", $cinemaId);
mysqli_stmt_execute($theaterStmt);
$theaterResult = mysqli_stmt_get_result($theaterStmt);

// 查詢此影城的場次
$showingSql = "SELECT s.*, m.movieName, m.movieImg, t.theaterName, v.versionName
               FROM showing s
               LEFT JOIN movie m ON s.movieId = m.movieId
               LEFT JOIN theater t ON s.theaterId = t.theaterId
               LEFT JOIN playVersion v ON s.versionId = v.versionId
               WHERE t.cinemaId = ?
               ORDER BY s.showingDate, s.startTime
               LIMIT 20";

$showingStmt = mysqli_prepare($conn, $showingSql);
mysqli_stmt_bind_param($showingStmt, "s", $cinemaId);
mysqli_stmt_execute($showingStmt);
$showingResult = mysqli_stmt_get_result($showingStmt);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cinema['cinemaName']); ?> - 威宇影城</title>
</head>
<body>
    <!-- 導覽列 -->
    <header>
        <h1>威宇影城 - 影城詳細資訊</h1>
        <nav style="text-align: right;">
            <span>歡迎，<?php echo htmlspecialchars($memberName); ?>！</span> | 
            <a href="index.php">會員首頁</a> | 
            <a href="browse_movies.php">瀏覽電影</a> | 
            <a href="browse_cinemas.php">瀏覽影城</a> | 
            <a href="browse_showings.php">查詢場次</a> | 
            <a href="booking.php">購票服務</a> | 
            <a href="inquiry.php">訂票紀錄</a> | 
            <a href="topup.php">儲值卡</a> | 
            <a href="profile.php">會員資料</a> | 
            <a href="../logout.php">登出</a>
        </nav>
    </header>
    <hr>
    
    <!-- 影城詳細資訊 -->
    <main>
        <section>
            <h2><?php echo htmlspecialchars($cinema['cinemaName']); ?></h2>
            
            <div style="display: flex; gap: 20px;">
                <!-- 左側：照片 -->
                <div>
                    <?php if (!empty($cinema['cinemaImg'])): ?>
                        <img src="../images/cinemas/<?php echo htmlspecialchars($cinema['cinemaImg']); ?>" 
                             alt="<?php echo htmlspecialchars($cinema['cinemaName']); ?>" 
                             width="400">
                    <?php else: ?>
                        <div style="width:400px;height:300px;background:#ccc;display:flex;align-items:center;justify-content:center;">
                            無照片
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- 右側：基本資訊 -->
                <div style="flex: 1;">
                    <table border="1" cellpadding="8" cellspacing="0">
                        <tr>
                            <th>影城名稱</th>
                            <td><?php echo htmlspecialchars($cinema['cinemaName']); ?></td>
                        </tr>
                        <tr>
                            <th>地址</th>
                            <td><?php echo htmlspecialchars($cinema['cinemaAddress']); ?></td>
                        </tr>
                        <tr>
                            <th>電話</th>
                            <td><?php echo htmlspecialchars($cinema['cinemaTele']); ?></td>
                        </tr>
                        <tr>
                            <th>交通資訊</th>
                            <td><?php echo nl2br(htmlspecialchars($cinema['cinemaBusTwo'])); ?></td>
                        </tr>
                    </table>
                    
                    <br>
                    <div>
                        <a href="booking.php?cinema=<?php echo $cinema['cinemaId']; ?>">
                            <button style="padding: 10px 20px; font-size: 16px;">在此影城購票</button>
                        </a>
                        <a href="browse_showings.php?cinema=<?php echo $cinema['cinemaId']; ?>">
                            <button style="padding: 10px 20px; font-size: 16px;">查看場次</button>
                        </a>
                        <a href="browse_cinemas.php">
                            <button style="padding: 10px 20px; font-size: 16px;">返回列表</button>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        
        <hr>
        
        <!-- 影城介紹 -->
        <section>
            <h3>影城介紹</h3>
            <p><?php echo nl2br(htmlspecialchars($cinema['cinemaInfo'])); ?></p>
        </section>
        
        <hr>
        
        <!-- Google 地圖 -->
        <?php if (!empty($cinema['googleMap'])): ?>
            <section>
                <h3>地理位置</h3>
                <p><a href="<?php echo htmlspecialchars($cinema['googleMap']); ?>" target="_blank">在 Google 地圖中查看</a></p>
            </section>
            <hr>
        <?php endif; ?>
        
        <!-- 影廳資訊 -->
        <section>
            <h3>影廳資訊</h3>
            
            <?php if (mysqli_num_rows($theaterResult) > 0): ?>
                <table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>影廳編號</th>
                            <th>影廳名稱</th>
                            <th>座位數</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($theater = mysqli_fetch_assoc($theaterResult)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($theater['theaterId']); ?></td>
                                <td><?php echo htmlspecialchars($theater['theaterName']); ?></td>
                                <td><?php echo htmlspecialchars($theater['seatNumber']); ?> 個座位</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>此影城目前沒有影廳資訊。</p>
            <?php endif; ?>
        </section>
        
        <hr>
        
        <!-- 近期場次 -->
        <section>
            <h3>近期場次（最多顯示 20 筆）</h3>
            
            <?php if (mysqli_num_rows($showingResult) > 0): ?>
                <table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>場次編號</th>
                            <th>電影名稱</th>
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
                                <td><?php echo htmlspecialchars($showing['showingId']); ?></td>
                                <td><?php echo htmlspecialchars($showing['movieName']); ?></td>
                                <td><?php echo htmlspecialchars($showing['theaterName']); ?></td>
                                <td><?php echo htmlspecialchars($showing['versionName']); ?></td>
                                <td><?php echo htmlspecialchars($showing['showingDate']); ?></td>
                                <td><?php echo htmlspecialchars($showing['startTime']); ?></td>
                                <td>
                                    <a href="movie_detail.php?id=<?php echo $showing['movieId']; ?>">電影詳情</a><br>
                                    <a href="booking.php?showing=<?php echo $showing['showingId']; ?>">購票</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>此影城目前沒有放映場次。</p>
            <?php endif; ?>
        </section>
    </main>
    
    <?php
    mysqli_stmt_close($theaterStmt);
    mysqli_stmt_close($showingStmt);
    closeConnection($conn);
    ?>
</body>
</html>

