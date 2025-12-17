<?php
/**
 * 訂票成功頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 檢查必要參數
if (!isset($_GET['orderNumber']) || !isset($_GET['getTicketNum'])) {
    header("Location: booking.php?error=invalid_access");
    exit();
}

$orderNumber = $_GET['orderNumber'];
$getTicketNum = $_GET['getTicketNum'];

// 取得訂單詳細資訊
$orderSql = "SELECT br.*, s.showingDate, s.startTime,
                    m.movieName, m.movieLength, m.movieGrade,
                    c.cinemaName, c.cinemaAddress,
                    t.theaterName,
                    pv.versionName,
                    os.statusName
             FROM bookingRecord br
             JOIN showing s ON br.showingId = s.showingId
             JOIN movie m ON s.movieId = m.movieId
             JOIN theater t ON s.theaterId = t.theaterId
             JOIN cinema c ON t.cinemaId = c.cinemaId
             JOIN playVersion pv ON s.versionId = pv.versionId
             JOIN orderStatus os ON br.orderStatusId = os.orderStatusId
             WHERE br.orderNumber = ? AND br.memberId = ?";

$stmt = mysqli_prepare($conn, $orderSql);
mysqli_stmt_bind_param($stmt, "ss", $orderNumber, $memberId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: booking.php?error=order_not_found");
    exit();
}

$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 解析餐點資料
$selectedMeals = [];
if (!empty($order['chooseMeal'])) {
    $selectedMeals = json_decode($order['chooseMeal'], true);
    if ($selectedMeals === null) {
        $selectedMeals = [];
    }
}

// 載入HTML模板
$templateFile = 'templates/success.html';
if (file_exists($templateFile)) {
    $template = file_get_contents($templateFile);
    
    // 替換基本變數
    $template = str_replace('{{MEMBER_NAME}}', htmlspecialchars($memberName), $template);
    $template = str_replace('{{ORDER_NUMBER}}', htmlspecialchars($order['orderNumber']), $template);
    $template = str_replace('{{GET_TICKET_NUM}}', htmlspecialchars($order['getTicketNum']), $template);
    $template = str_replace('{{MOVIE_NAME}}', htmlspecialchars($order['movieName']), $template);
    $template = str_replace('{{CINEMA_NAME}}', htmlspecialchars($order['cinemaName']), $template);
    $template = str_replace('{{THEATER_NAME}}', htmlspecialchars($order['theaterName']), $template);
    $template = str_replace('{{SHOWING_DATE}}', htmlspecialchars($order['showingDate']), $template);
    $template = str_replace('{{START_TIME}}', htmlspecialchars($order['startTime']), $template);
    $template = str_replace('{{VERSION_NAME}}', htmlspecialchars($order['versionName']), $template);
    $template = str_replace('{{SELECTED_SEATS}}', htmlspecialchars($order['seat']), $template);
    $template = str_replace('{{TICKET_COUNT}}', htmlspecialchars($order['ticketNums']), $template);
    $template = str_replace('{{TOTAL_PRICE}}', htmlspecialchars($order['totalPrice']), $template);
    $template = str_replace('{{ORDER_TIME}}', htmlspecialchars($order['time']), $template);
    $template = str_replace('{{ORDER_STATUS}}', htmlspecialchars($order['statusName']), $template);
    
    // 生成餐點列表
    $mealsHtml = '';
    if (!empty($selectedMeals)) {
        $mealsHtml = '<ul class="meal-list">';
        foreach ($selectedMeals as $mealId => $meal) {
            $mealsHtml .= '<li>';
            $mealsHtml .= '<span class="meal-name">' . htmlspecialchars($meal['name']) . '</span>';
            $mealsHtml .= ' x <span class="meal-qty">' . htmlspecialchars($meal['quantity']) . '</span>';
            $mealsHtml .= ' = <span class="meal-price">NT$ ' . htmlspecialchars($meal['subtotal']) . '</span>';
            $mealsHtml .= '</li>';
        }
        $mealsHtml .= '</ul>';
    } else {
        $mealsHtml = '<p class="no-meals">未選擇餐點</p>';
    }
    $template = str_replace('{{SELECTED_MEALS}}', $mealsHtml, $template);
    
    echo $template;
} else {
    // 如果模板不存在，顯示基本頁面
    ?>
    <!DOCTYPE html>
    <html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>訂票成功 - 威宇影城</title>
        <style>
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .success-header { text-align: center; color: #28a745; margin-bottom: 30px; }
            .success-header h1 { font-size: 36px; margin-bottom: 10px; }
            .order-info { background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 20px; }
            .order-info h2 { color: #2c3e50; margin-bottom: 20px; }
            .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 8px 0; border-bottom: 1px solid #e0e0e0; }
            .info-row:last-child { border-bottom: none; }
            .info-label { font-weight: bold; color: #34495e; }
            .info-value { color: #2c3e50; }
            .ticket-info { background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center; }
            .ticket-num { font-size: 24px; font-weight: bold; color: #27ae60; }
            .btn { padding: 12px 25px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
            .btn:hover { background-color: #0056b3; }
            .btn-success { background-color: #28a745; }
            .btn-info { background-color: #17a2b8; }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <h1>威宇影城 - 訂票成功</h1>
                <nav style="text-align: right;">
                    <span>歡迎，<?php echo htmlspecialchars($memberName); ?>！</span> | 
                    <a href="../index.php">會員首頁</a> | 
                    <a href="../../auth/logout.php">登出</a>
                </nav>
            </header>
            <hr>
            
            <main>
                <div class="success-header">
                    <h1>🎉 訂票成功！</h1>
                    <p>感謝您的購票，祝您觀影愉快！</p>
                </div>
                
                <div class="ticket-info">
                    <h3>📱 取票號碼</h3>
                    <div class="ticket-num"><?php echo htmlspecialchars($order['getTicketNum']); ?></div>
                    <p>請憑此號碼至影城櫃台或自動取票機取票</p>
                </div>
                
                <div class="order-info">
                    <h2>📋 訂單資訊</h2>
                    <div class="info-row">
                        <span class="info-label">訂單編號：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['orderNumber']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">電影：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['movieName']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">影城：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['cinemaName']); ?> - <?php echo htmlspecialchars($order['theaterName']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">場次：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['showingDate']); ?> <?php echo htmlspecialchars($order['startTime']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">版本：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['versionName']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">座位：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['seat']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">票數：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['ticketNums']); ?> 張</span>
                    </div>
                    
                    <?php if (!empty($selectedMeals)): ?>
                    <div class="info-row">
                        <span class="info-label">餐點：</span>
                        <span class="info-value">
                            <?php foreach ($selectedMeals as $mealId => $meal): ?>
                                <?php echo htmlspecialchars($meal['name']); ?> x <?php echo $meal['quantity']; ?><br>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-row">
                        <span class="info-label">總金額：</span>
                        <span class="info-value">NT$ <?php echo number_format($order['totalPrice']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">訂票時間：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['time']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">訂單狀態：</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['statusName']); ?></span>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="../inquiry/index.php" class="btn btn-info">📋 查看我的訂票紀錄</a>
                    <a href="booking.php" class="btn btn-success">🎬 繼續購票</a>
                    <a href="../index.php" class="btn">🏠 返回會員首頁</a>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
}

closeConnection($conn);
?>
