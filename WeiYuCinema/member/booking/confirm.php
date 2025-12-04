<?php
/**
 * 訂單確認頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 檢查必要的POST資料
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['showingId']) || !isset($_POST['selectedSeats']) || 
    !isset($_POST['ticketCount']) || !isset($_POST['ticketTotalPrice']) ||
    !isset($_POST['selectedMeals']) || !isset($_POST['mealTotalPrice']) ||
    !isset($_POST['grandTotalPrice'])) {
    header("Location: booking.php?error=invalid_request");
    exit();
}

$showingId = $_POST['showingId'];
$selectedSeats = $_POST['selectedSeats'];
$ticketCount = (int)$_POST['ticketCount'];
$ticketTotalPrice = (int)$_POST['ticketTotalPrice'];
$selectedMealsJson = $_POST['selectedMeals'];
$mealTotalPrice = (int)$_POST['mealTotalPrice'];
$grandTotalPrice = (int)$_POST['grandTotalPrice'];

// 解析餐點資料
$selectedMeals = json_decode($selectedMealsJson, true);
if ($selectedMeals === null) {
    $selectedMeals = [];
}

// 驗證資料
if (empty($selectedSeats) || $ticketCount <= 0 || $grandTotalPrice <= 0) {
    header("Location: select_seat.php?showingId=" . urlencode($showingId) . "&error=invalid_data");
    exit();
}

// 取得場次資訊
$showingSql = "SELECT s.showingId, s.showingDate, s.startTime,
                      m.movieId, m.movieName, m.movieLength, m.movieGrade,
                      c.cinemaId, c.cinemaName, c.cinemaAddress,
                      t.theaterId, t.theaterName,
                      pv.versionName,
                      tc.ticketPrice
               FROM showing s
               JOIN movie m ON s.movieId = m.movieId
               JOIN theater t ON s.theaterId = t.theaterId
               JOIN cinema c ON t.cinemaId = c.cinemaId
               JOIN playVersion pv ON s.versionId = pv.versionId
               JOIN ticketClass tc ON m.movieGrade = tc.gradeId
               WHERE s.showingId = ?";

$stmt = mysqli_prepare($conn, $showingSql);
mysqli_stmt_bind_param($stmt, "s", $showingId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    header("Location: booking.php?error=showing_not_found");
    exit();
}

$showing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 取得會員餘額
$balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ?";
$stmt = mysqli_prepare($conn, $balanceSql);
mysqli_stmt_bind_param($stmt, "s", $memberId);
mysqli_stmt_execute($stmt);
$balanceResult = mysqli_stmt_get_result($stmt);

$memberBalance = 0;
if (mysqli_num_rows($balanceResult) > 0) {
    $balanceData = mysqli_fetch_assoc($balanceResult);
    $memberBalance = $balanceData['balance'];
}
mysqli_stmt_close($stmt);

// 檢查座位是否仍然可用
$seatArray = explode(',', $selectedSeats);
$seatCheckSql = "SELECT seatNumber, seatEmpty FROM seatCondition WHERE showingId = ? AND seatNumber IN (" . 
                str_repeat('?,', count($seatArray) - 1) . "?)";
$stmt = mysqli_prepare($conn, $seatCheckSql);
$types = str_repeat('s', count($seatArray) + 1);
mysqli_stmt_bind_param($stmt, $types, $showingId, ...$seatArray);
mysqli_stmt_execute($stmt);
$seatCheckResult = mysqli_stmt_get_result($stmt);

$unavailableSeats = [];
while ($seatCheck = mysqli_fetch_assoc($seatCheckResult)) {
    if ($seatCheck['seatEmpty'] == 0) { // 座位已被占用
        $unavailableSeats[] = $seatCheck['seatNumber'];
    }
}
mysqli_stmt_close($stmt);

// 載入HTML模板
$templateFile = 'templates/confirm.html';
if (file_exists($templateFile)) {
    $template = file_get_contents($templateFile);
    
    // 替換基本變數
    $template = str_replace('{{MEMBER_NAME}}', htmlspecialchars($memberName), $template);
    $template = str_replace('{{MEMBER_ID}}', htmlspecialchars($memberId), $template);
    $template = str_replace('{{SHOWING_ID}}', htmlspecialchars($showing['showingId']), $template);
    $template = str_replace('{{MOVIE_NAME}}', htmlspecialchars($showing['movieName']), $template);
    $template = str_replace('{{CINEMA_NAME}}', htmlspecialchars($showing['cinemaName']), $template);
    $template = str_replace('{{THEATER_NAME}}', htmlspecialchars($showing['theaterName']), $template);
    $template = str_replace('{{SHOWING_DATE}}', htmlspecialchars($showing['showingDate']), $template);
    $template = str_replace('{{START_TIME}}', htmlspecialchars($showing['startTime']), $template);
    $template = str_replace('{{VERSION_NAME}}', htmlspecialchars($showing['versionName']), $template);
    $template = str_replace('{{SELECTED_SEATS}}', htmlspecialchars($selectedSeats), $template);
    $template = str_replace('{{TICKET_COUNT}}', htmlspecialchars($ticketCount), $template);
    $template = str_replace('{{TICKET_TOTAL_PRICE}}', htmlspecialchars($ticketTotalPrice), $template);
    $template = str_replace('{{MEAL_TOTAL_PRICE}}', htmlspecialchars($mealTotalPrice), $template);
    $template = str_replace('{{GRAND_TOTAL_PRICE}}', htmlspecialchars($grandTotalPrice), $template);
    $template = str_replace('{{MEMBER_BALANCE}}', htmlspecialchars($memberBalance), $template);
    
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
    
    // 處理座位可用性警告
    $seatWarning = '';
    if (!empty($unavailableSeats)) {
        $seatWarning = '<div class="alert alert-danger">';
        $seatWarning .= '<h4>⚠️ 座位已被占用</h4>';
        $seatWarning .= '<p>以下座位已被其他人選購：' . implode(', ', $unavailableSeats) . '</p>';
        $seatWarning .= '<p>請返回重新選擇座位。</p>';
        $seatWarning .= '<a href="select_seat.php?showingId=' . urlencode($showingId) . '" class="btn btn-warning">重新選擇座位</a>';
        $seatWarning .= '</div>';
    }
    $template = str_replace('{{SEAT_WARNING}}', $seatWarning, $template);
    
    // 處理餘額不足警告
    $balanceWarning = '';
    if ($memberBalance < $grandTotalPrice) {
        $shortfall = $grandTotalPrice - $memberBalance;
        $balanceWarning = '<div class="alert alert-warning">';
        $balanceWarning .= '<h4>💰 餘額不足</h4>';
        $balanceWarning .= '<p>您的餘額：NT$ ' . number_format($memberBalance) . '</p>';
        $balanceWarning .= '<p>訂單金額：NT$ ' . number_format($grandTotalPrice) . '</p>';
        $balanceWarning .= '<p>不足金額：NT$ ' . number_format($shortfall) . '</p>';
        $balanceWarning .= '<a href="../topup/index.php" class="btn btn-info">前往儲值</a>';
        $balanceWarning .= '</div>';
    }
    $template = str_replace('{{BALANCE_WARNING}}', $balanceWarning, $template);
    
    // 決定是否可以確認訂單
    $canConfirm = empty($unavailableSeats) && $memberBalance >= $grandTotalPrice;
    $confirmButton = '';
    if ($canConfirm) {
        $confirmButton = '<button type="submit" class="btn btn-success btn-lg">💳 確認付款並完成訂票</button>';
    } else {
        $confirmButton = '<button type="button" class="btn btn-secondary btn-lg" disabled>無法完成訂票</button>';
    }
    $template = str_replace('{{CONFIRM_BUTTON}}', $confirmButton, $template);
    
    echo $template;
} else {
    // 如果模板不存在，顯示基本頁面
    ?>
    <!DOCTYPE html>
    <html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>確認訂單 - 威宇影城</title>
        <style>
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .order-details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
            .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
            .btn { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn:hover { background-color: #0056b3; }
            .btn:disabled { background-color: #6c757d; cursor: not-allowed; }
            .btn-success { background-color: #28a745; }
            .btn-success:hover { background-color: #218838; }
            .btn-warning { background-color: #ffc107; color: #212529; }
            .btn-info { background-color: #17a2b8; }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <h1>威宇影城 - 確認訂單</h1>
                <nav style="text-align: right;">
                    <span>歡迎，<?php echo htmlspecialchars($memberName); ?>！</span> | 
                    <a href="../index.php">會員首頁</a> | 
                    <a href="../../auth/logout.php">登出</a>
                </nav>
            </header>
            <hr>
            
            <main>
                <!-- 座位可用性檢查 -->
                <?php if (!empty($unavailableSeats)): ?>
                <div class="alert alert-danger">
                    <h4>⚠️ 座位已被占用</h4>
                    <p>以下座位已被其他人選購：<?php echo implode(', ', $unavailableSeats); ?></p>
                    <p>請返回重新選擇座位。</p>
                    <a href="select_seat.php?showingId=<?php echo urlencode($showingId); ?>" class="btn btn-warning">重新選擇座位</a>
                </div>
                <?php endif; ?>
                
                <!-- 餘額檢查 -->
                <?php if ($memberBalance < $grandTotalPrice): ?>
                <div class="alert alert-warning">
                    <h4>💰 餘額不足</h4>
                    <p>您的餘額：NT$ <?php echo number_format($memberBalance); ?></p>
                    <p>訂單金額：NT$ <?php echo number_format($grandTotalPrice); ?></p>
                    <p>不足金額：NT$ <?php echo number_format($grandTotalPrice - $memberBalance); ?></p>
                    <a href="../topup/index.php" class="btn btn-info">前往儲值</a>
                </div>
                <?php endif; ?>
                
                <!-- 訂單詳情 -->
                <div class="order-details">
                    <h2>訂單詳情</h2>
                    <h3><?php echo htmlspecialchars($showing['movieName']); ?></h3>
                    <p><strong>影城：</strong><?php echo htmlspecialchars($showing['cinemaName']); ?> - <?php echo htmlspecialchars($showing['theaterName']); ?></p>
                    <p><strong>場次：</strong><?php echo htmlspecialchars($showing['showingDate']); ?> <?php echo htmlspecialchars($showing['startTime']); ?></p>
                    <p><strong>版本：</strong><?php echo htmlspecialchars($showing['versionName']); ?></p>
                    <p><strong>座位：</strong><?php echo htmlspecialchars($selectedSeats); ?></p>
                    <p><strong>票數：</strong><?php echo $ticketCount; ?> 張</p>
                    
                    <?php if (!empty($selectedMeals)): ?>
                    <h4>選購餐點：</h4>
                    <ul>
                        <?php foreach ($selectedMeals as $mealId => $meal): ?>
                        <li><?php echo htmlspecialchars($meal['name']); ?> x <?php echo $meal['quantity']; ?> = NT$ <?php echo $meal['subtotal']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p>未選擇餐點</p>
                    <?php endif; ?>
                    
                    <hr>
                    <p><strong>票價小計：</strong>NT$ <?php echo number_format($ticketTotalPrice); ?></p>
                    <p><strong>餐點小計：</strong>NT$ <?php echo number_format($mealTotalPrice); ?></p>
                    <p><strong>總金額：</strong>NT$ <?php echo number_format($grandTotalPrice); ?></p>
                    <p><strong>您的餘額：</strong>NT$ <?php echo number_format($memberBalance); ?></p>
                </div>
                
                <!-- 確認按鈕 -->
                <?php if (empty($unavailableSeats) && $memberBalance >= $grandTotalPrice): ?>
                <form action="process.php" method="POST">
                    <input type="hidden" name="showingId" value="<?php echo htmlspecialchars($showingId); ?>">
                    <input type="hidden" name="selectedSeats" value="<?php echo htmlspecialchars($selectedSeats); ?>">
                    <input type="hidden" name="ticketCount" value="<?php echo htmlspecialchars($ticketCount); ?>">
                    <input type="hidden" name="ticketTotalPrice" value="<?php echo htmlspecialchars($ticketTotalPrice); ?>">
                    <input type="hidden" name="selectedMeals" value="<?php echo htmlspecialchars($selectedMealsJson); ?>">
                    <input type="hidden" name="mealTotalPrice" value="<?php echo htmlspecialchars($mealTotalPrice); ?>">
                    <input type="hidden" name="grandTotalPrice" value="<?php echo htmlspecialchars($grandTotalPrice); ?>">
                    
                    <button type="submit" class="btn btn-success" style="font-size: 18px; padding: 15px 30px;">💳 確認付款並完成訂票</button>
                </form>
                <?php else: ?>
                <button type="button" class="btn" disabled style="font-size: 18px; padding: 15px 30px;">無法完成訂票</button>
                <?php endif; ?>
                
                <p><a href="select_meal.php" class="btn" style="background-color: #6c757d;" onclick="history.back(); return false;">← 返回上一步</a></p>
            </main>
        </div>
    </body>
    </html>
    <?php
}

closeConnection($conn);
?>
