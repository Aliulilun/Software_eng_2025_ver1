<?php
/**
 * 電影詳細資訊頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 取得電影 ID
$movieId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($movieId <= 0) {
    header("Location: browse_movies.php?error=invalid_id");
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
    header("Location: browse_movies.php?error=not_found");
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
</head>
<body>
    <!-- 導覽列 -->
    <header>
        <h1>威宇影城 - 電影詳細資訊</h1>
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
    
    <!-- 電影詳細資訊 -->
    <main>
        <section>
            <h2><?php echo htmlspecialchars($movie['movieName']); ?></h2>
            
            <div style="display: flex; gap: 20px;">
                <!-- 左側：海報 -->
                <div>
                    <?php if (!empty($movie['movieImg'])): ?>
                        <img src="../images/movies/<?php echo htmlspecialchars($movie['movieImg']); ?>" 
                             alt="<?php echo htmlspecialchars($movie['movieName']); ?>" 
                             width="300">
                    <?php else: ?>
                        <div style="width:300px;height:450px;background:#ccc;display:flex;align-items:center;justify-content:center;">
                            無海報
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- 右側：詳細資訊 -->
                <div style="flex: 1;">
                    <table border="1" cellpadding="8" cellspacing="0">
                        <tr>
                            <th>電影名稱</th>
                            <td><?php echo htmlspecialchars($movie['movieName']); ?></td>
                        </tr>
                        <tr>
                            <th>片長</th>
                            <td><?php echo htmlspecialchars($movie['movieTime']); ?></td>
                        </tr>
                        <tr>
                            <th>分級</th>
                            <td><?php echo htmlspecialchars($movie['gradeName']); ?></td>
                        </tr>
                        <tr>
                            <th>類型</th>
                            <td><?php echo htmlspecialchars($movie['movieTypeName']); ?></td>
                        </tr>
                        <tr>
                            <th>導演</th>
                            <td><?php echo htmlspecialchars($movie['director']); ?></td>
                        </tr>
                        <tr>
                            <th>主演</th>
                            <td><?php echo htmlspecialchars($movie['actors']); ?></td>
                        </tr>
                        <tr>
                            <th>上映日期</th>
                            <td><?php echo htmlspecialchars($movie['movieStart']); ?></td>
                        </tr>
                    </table>
                    
                    <br>
                    <div>
                        <a href="booking.php?movie=<?php echo $movie['movieId']; ?>">
                            <button style="padding: 10px 20px; font-size: 16px;">立即購票</button>
                        </a>
                        <a href="browse_showings.php?movie=<?php echo $movie['movieId']; ?>">
                            <button style="padding: 10px 20px; font-size: 16px;">查看場次</button>
                        </a>
                        <a href="browse_movies.php">
                            <button style="padding: 10px 20px; font-size: 16px;">返回列表</button>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        
        <hr>
        
        <!-- 電影介紹 -->
        <section>
            <h3>電影介紹</h3>
            <p><?php echo nl2br(htmlspecialchars($movie['movieInfo'])); ?></p>
        </section>
        
        <hr>
        
        <!-- 場次資訊 -->
        <section>
            <h3>放映場次</h3>
            
            <?php if (mysqli_num_rows($showingResult) > 0): ?>
                <table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
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
                                <td><?php echo htmlspecialchars($showing['showingId']); ?></td>
                                <td><?php echo htmlspecialchars($showing['cinemaName']); ?></td>
                                <td><?php echo htmlspecialchars($showing['theaterName']); ?></td>
                                <td><?php echo htmlspecialchars($showing['versionName']); ?></td>
                                <td><?php echo htmlspecialchars($showing['showingDate']); ?></td>
                                <td><?php echo htmlspecialchars($showing['startTime']); ?></td>
                                <td>
                                    <a href="booking.php?showing=<?php echo $showing['showingId']; ?>">購票</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>目前此電影沒有放映場次。</p>
            <?php endif; ?>
        </section>
    </main>
    
    <?php
    mysqli_stmt_close($showingStmt);
    closeConnection($conn);
    ?>
</body>
</html>

