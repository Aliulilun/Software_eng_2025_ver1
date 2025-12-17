<?php
/**
 * 訂單確認頁面 (已修正：強制使用全票價格)
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 1. 檢查必要的 POST 資料
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['showingId']) || !isset($_POST['selectedSeats']) || 
    !isset($_POST['ticketCount']) || !isset($_POST['ticketTotalPrice']) ||
    !isset($_POST['selectedMeals']) || !isset($_POST['mealTotalPrice']) ||
    !isset($_POST['grandTotalPrice'])) {
    // 資料不全，踢回上一頁
    header("Location: booking.php?error=invalid_request");
    exit();
}

// 2. 接收資料
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

// 3. 取得場次資訊
// 修正重點：JOIN ticketClass tc ON tc.ticketClassId = 1
// 這樣會強制抓取 ID 為 1 的票種 (全票) 價格，解決票價為 0 的問題
$showingSql = "SELECT s.showingId, s.showingDate, s.startTime,
                      m.movieId, m.movieName, m.movieTime, m.gradeId,
                      c.cinemaId, c.cinemaName, c.cinemaAddress,
                      t.theaterId, t.theaterName,
                      pv.versionName,
                      tc.ticketClassPrice AS ticketPrice
               FROM showing s
               JOIN movie m ON s.movieId = m.movieId
               JOIN theater t ON s.theaterId = t.theaterId
               JOIN cinema c ON t.cinemaId = c.cinemaId
               JOIN playVersion pv ON s.versionId = pv.versionId
               JOIN ticketClass tc ON tc.ticketClassId = 1  /* <-- 關鍵修改：強制對應全票 (ID=1) */
               WHERE s.showingId = ?";

$stmt = mysqli_prepare($conn, $showingSql);
mysqli_stmt_bind_param($stmt, "s", $showingId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$showing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 4. 取得會員餘額
$memberId = $_SESSION['memberId']; // 確保已登入
$balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ?";
$stmt = mysqli_prepare($conn, $balanceSql);
mysqli_stmt_bind_param($stmt, "s", $memberId); // 注意：如果 memberId 是字串(例如 M001)要用 s，如果是數字用 i
mysqli_stmt_execute($stmt);
$balanceResult = mysqli_stmt_get_result($stmt);
$memberBalance = 0;
if ($row = mysqli_fetch_assoc($balanceResult)) {
    $memberBalance = $row['balance'];
}
mysqli_stmt_close($stmt);

// 5. 再次檢查座位是否被搶走
$seatArray = explode(',', $selectedSeats);
$unavailableSeats = [];
// 這裡做簡單檢查：如果資料庫顯示 seatEmpty=0，代表被搶走了
$checkSql = "SELECT seatNumber FROM seatCondition WHERE showingId = ? AND seatNumber = ? AND seatEmpty = 0";
$stmt = mysqli_prepare($conn, $checkSql);
foreach ($seatArray as $seat) {
    mysqli_stmt_bind_param($stmt, "ss", $showingId, $seat);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_fetch($stmt)) {
        $unavailableSeats[] = $seat;
    }
}
mysqli_stmt_close($stmt);

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>確認訂單 - 威宇影城</title>
    <link rel="stylesheet" href="/WeiYuCinema/static/css/style.css">
    <style>
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        /* 我加上了 color: #333333; 讓白底上面的字變深灰色 */
        .confirm-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); color: #333333; }
        .section { border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .section:last-child { border-bottom: none; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1em; }
        .total-row { display: flex; justify-content: space-between; margin-top: 20px; font-size: 1.5em; font-weight: bold; color: #e74c3c; }
        .alert { padding: 15px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .btn-pay { width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 1.2em; cursor: pointer; }
        .btn-pay:hover { background: #218838; }
        .btn-disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirm-box">
            <h1 style="text-align: center; margin-bottom: 30px;">🧾 訂單確認</h1>
            
            <?php if (!empty($unavailableSeats)): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ 糟糕！座位已被搶走</strong><br>
                    以下座位剛被其他人訂走了：<?php echo implode(', ', $unavailableSeats); ?><br>
                    <a href="select_seat.php?showingId=<?php echo $showingId; ?>">返回重新選位</a>
                </div>
            <?php endif; ?>

            <?php if ($memberBalance < $grandTotalPrice): ?>
                <div class="alert">
                    <strong>💰 餘額不足</strong><br>
                    您的餘額：NT$ <?php echo number_format($memberBalance); ?><br>
                    還差：NT$ <?php echo number_format($grandTotalPrice - $memberBalance); ?><br>
                    <a href="../topup/index.php">前往儲值</a>
                </div>
            <?php endif; ?>

            <div class="section">
                <h3>🎬 <?php echo htmlspecialchars($showing['movieName']); ?></h3>
                <p>影城：<?php echo htmlspecialchars($showing['cinemaName']); ?> (<?php echo htmlspecialchars($showing['theaterName']); ?>)</p>
                <p>時間：<?php echo htmlspecialchars($showing['showingDate']); ?> <?php echo htmlspecialchars($showing['startTime']); ?></p>
                <p>版本：<?php echo htmlspecialchars($showing['versionName']); ?></p>
            </div>

            <div class="section">
                <h3>🛒 消費明細</h3>
                <div class="price-row">
                    <span>🎫 座位 (<?php echo htmlspecialchars($selectedSeats); ?>)</span>
                    <span>NT$ <?php echo $ticketTotalPrice; ?></span>
                </div>
                <?php if (!empty($selectedMeals)): ?>
                    <?php foreach ($selectedMeals as $meal): ?>
                    <div class="price-row">
                        <span>🍿 <?php echo htmlspecialchars($meal['name']); ?> x <?php echo $meal['quantity']; ?></span>
                        <span>NT$ <?php echo $meal['subtotal']; ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="total-row">
                    <span>總金額</span>
                    <span>NT$ <?php echo number_format($grandTotalPrice); ?></span>
                </div>
                <div style="text-align: right; color: #666; margin-top: 5px;">
                    (目前餘額: NT$ <?php echo number_format($memberBalance); ?>)
                </div>
            </div>

            <?php if (empty($unavailableSeats) && $memberBalance >= $grandTotalPrice): ?>
                <form action="checkout_process.php" method="POST">
                    <input type="hidden" name="showingId" value="<?php echo $showingId; ?>">
                    <input type="hidden" name="selectedSeats" value="<?php echo htmlspecialchars($selectedSeats); ?>">
                    <input type="hidden" name="ticketCount" value="<?php echo $ticketCount; ?>">
                    <input type="hidden" name="selectedMeals" value="<?php echo htmlspecialchars($selectedMealsJson); ?>">
                    <input type="hidden" name="grandTotalPrice" value="<?php echo $grandTotalPrice; ?>">
                    
                    <button type="submit" class="btn-pay">💳 確認付款並劃位</button>
                </form>
            <?php else: ?>
                <button class="btn-pay btn-disabled" disabled>無法完成訂單</button>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="javascript:history.back()" style="color: #999; text-decoration: none;">← 返回上一步</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php closeConnection($conn); ?>