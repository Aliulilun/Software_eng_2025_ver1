<?php
/**
 * 座位選擇頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 檢查場次ID
if (!isset($_GET['showingId']) || empty($_GET['showingId'])) {
    header("Location: booking.php?error=no_showing");
    exit();
}

$showingId = $_GET['showingId'];

// 取得場次詳細資訊
$showingSql = "SELECT s.showingId, s.showingDate, s.startTime,
                      m.movieId, m.movieName, m.movieLength, m.movieGrade,
                      c.cinemaId, c.cinemaName, c.cinemaAddress,
                      t.theaterId, t.theaterName, t.seatNumber,
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

// 取得座位狀態
$seatsSql = "SELECT seatNumber, seatEmpty FROM seatCondition WHERE showingId = ?";
$stmt = mysqli_prepare($conn, $seatsSql);
mysqli_stmt_bind_param($stmt, "s", $showingId);
mysqli_stmt_execute($stmt);
$seatsResult = mysqli_stmt_get_result($stmt);

$seatStatus = [];
while ($seat = mysqli_fetch_assoc($seatsResult)) {
    $seatStatus[$seat['seatNumber']] = $seat['seatEmpty']; // 1=空位, 0=已占用
}
mysqli_stmt_close($stmt);

// 生成座位圖（假設座位編號格式為 A1, A2, B1, B2 等）
$totalSeats = $showing['seatNumber'];
$seatsPerRow = 10; // 每排10個座位
$rows = ceil($totalSeats / $seatsPerRow);

// 載入HTML模板
$templateFile = 'templates/select_seat.html';
if (file_exists($templateFile)) {
    $template = file_get_contents($templateFile);
    
    // 替換基本變數
    $template = str_replace('{{MEMBER_NAME}}', htmlspecialchars($memberName), $template);
    $template = str_replace('{{SHOWING_ID}}', htmlspecialchars($showing['showingId']), $template);
    $template = str_replace('{{MOVIE_NAME}}', htmlspecialchars($showing['movieName']), $template);
    $template = str_replace('{{CINEMA_NAME}}', htmlspecialchars($showing['cinemaName']), $template);
    $template = str_replace('{{THEATER_NAME}}', htmlspecialchars($showing['theaterName']), $template);
    $template = str_replace('{{SHOWING_DATE}}', htmlspecialchars($showing['showingDate']), $template);
    $template = str_replace('{{START_TIME}}', htmlspecialchars($showing['startTime']), $template);
    $template = str_replace('{{VERSION_NAME}}', htmlspecialchars($showing['versionName']), $template);
    $template = str_replace('{{TICKET_PRICE}}', htmlspecialchars($showing['ticketPrice']), $template);
    
    // 生成座位圖
    $seatMap = '<div class="screen">螢幕</div>';
    $seatMap .= '<div class="seat-map">';
    
    $seatNumber = 1;
    for ($row = 1; $row <= $rows; $row++) {
        $rowLetter = chr(64 + $row); // A, B, C, ...
        $seatMap .= '<div class="seat-row">';
        $seatMap .= '<div class="row-label">' . $rowLetter . '</div>';
        
        for ($col = 1; $col <= $seatsPerRow && $seatNumber <= $totalSeats; $col++) {
            $seatId = $rowLetter . $col;
            $isOccupied = isset($seatStatus[$seatId]) && $seatStatus[$seatId] == 0;
            $seatClass = $isOccupied ? 'seat occupied' : 'seat available';
            
            $seatMap .= '<div class="' . $seatClass . '" data-seat="' . $seatId . '">';
            $seatMap .= $col;
            $seatMap .= '</div>';
            
            $seatNumber++;
        }
        
        $seatMap .= '</div>';
    }
    
    $seatMap .= '</div>';
    $template = str_replace('{{SEAT_MAP}}', $seatMap, $template);
    
    echo $template;
} else {
    // 如果模板不存在，顯示基本頁面
    ?>
    <!DOCTYPE html>
    <html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>選擇座位 - 威宇影城</title>
        <style>
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .showing-info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .screen { background: #333; color: white; text-align: center; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
            .seat-map { display: flex; flex-direction: column; gap: 10px; }
            .seat-row { display: flex; gap: 5px; align-items: center; justify-content: center; }
            .row-label { width: 30px; text-align: center; font-weight: bold; }
            .seat { width: 30px; height: 30px; border: 2px solid #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; }
            .seat.available { background: #28a745; color: white; }
            .seat.available:hover { background: #218838; }
            .seat.occupied { background: #dc3545; color: white; cursor: not-allowed; }
            .seat.selected { background: #ffc107; color: black; }
            .legend { display: flex; gap: 20px; justify-content: center; margin: 20px 0; }
            .legend-item { display: flex; align-items: center; gap: 5px; }
            .legend-seat { width: 20px; height: 20px; border-radius: 3px; }
            .booking-summary { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 20px; }
            .btn { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn:hover { background-color: #0056b3; }
            .btn:disabled { background-color: #6c757d; cursor: not-allowed; }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <h1>威宇影城 - 選擇座位</h1>
                <nav style="text-align: right;">
                    <span>歡迎，<?php echo htmlspecialchars($memberName); ?>！</span> | 
                    <a href="../index.php">會員首頁</a> | 
                    <a href="../../auth/logout.php">登出</a>
                </nav>
            </header>
            <hr>
            
            <main>
                <!-- 場次資訊 -->
                <div class="showing-info">
                    <h2><?php echo htmlspecialchars($showing['movieName']); ?></h2>
                    <p><strong>影城：</strong><?php echo htmlspecialchars($showing['cinemaName']); ?></p>
                    <p><strong>影廳：</strong><?php echo htmlspecialchars($showing['theaterName']); ?></p>
                    <p><strong>日期：</strong><?php echo htmlspecialchars($showing['showingDate']); ?></p>
                    <p><strong>時間：</strong><?php echo htmlspecialchars($showing['startTime']); ?></p>
                    <p><strong>版本：</strong><?php echo htmlspecialchars($showing['versionName']); ?></p>
                    <p><strong>票價：</strong>NT$ <?php echo htmlspecialchars($showing['ticketPrice']); ?></p>
                </div>
                
                <!-- 座位圖 -->
                <div class="screen">螢幕</div>
                <div class="seat-map">
                    <?php
                    $seatNumber = 1;
                    for ($row = 1; $row <= $rows; $row++) {
                        $rowLetter = chr(64 + $row);
                        echo '<div class="seat-row">';
                        echo '<div class="row-label">' . $rowLetter . '</div>';
                        
                        for ($col = 1; $col <= $seatsPerRow && $seatNumber <= $totalSeats; $col++) {
                            $seatId = $rowLetter . $col;
                            $isOccupied = isset($seatStatus[$seatId]) && $seatStatus[$seatId] == 0;
                            $seatClass = $isOccupied ? 'seat occupied' : 'seat available';
                            
                            echo '<div class="' . $seatClass . '" data-seat="' . $seatId . '">';
                            echo $col;
                            echo '</div>';
                            
                            $seatNumber++;
                        }
                        
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <!-- 圖例 -->
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-seat" style="background: #28a745;"></div>
                        <span>可選</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat" style="background: #ffc107;"></div>
                        <span>已選</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat" style="background: #dc3545;"></div>
                        <span>已售</span>
                    </div>
                </div>
                
                <!-- 訂票摘要 -->
                <div class="booking-summary">
                    <h3>訂票摘要</h3>
                    <p><strong>已選座位：</strong><span id="selectedSeats">尚未選擇</span></p>
                    <p><strong>票數：</strong><span id="ticketCount">0</span> 張</p>
                    <p><strong>單價：</strong>NT$ <?php echo htmlspecialchars($showing['ticketPrice']); ?></p>
                    <p><strong>總金額：</strong>NT$ <span id="totalPrice">0</span></p>
                    
                    <form action="select_meal.php" method="POST" id="seatForm">
                        <input type="hidden" name="showingId" value="<?php echo htmlspecialchars($showing['showingId']); ?>">
                        <input type="hidden" name="selectedSeats" id="selectedSeatsInput">
                        <input type="hidden" name="ticketCount" id="ticketCountInput">
                        <input type="hidden" name="totalPrice" id="totalPriceInput">
                        
                        <button type="submit" class="btn" id="nextBtn" disabled>下一步：選擇餐點</button>
                    </form>
                </div>
                
                <p><a href="booking.php" class="btn" style="background-color: #6c757d;">← 返回場次選擇</a></p>
            </main>
        </div>
        
        <script>
            const ticketPrice = <?php echo $showing['ticketPrice']; ?>;
            let selectedSeats = [];
            
            // 座位點擊事件
            document.querySelectorAll('.seat.available').forEach(seat => {
                seat.addEventListener('click', function() {
                    const seatId = this.dataset.seat;
                    
                    if (this.classList.contains('selected')) {
                        // 取消選擇
                        this.classList.remove('selected');
                        selectedSeats = selectedSeats.filter(s => s !== seatId);
                    } else {
                        // 選擇座位
                        this.classList.add('selected');
                        selectedSeats.push(seatId);
                    }
                    
                    updateBookingSummary();
                });
            });
            
            function updateBookingSummary() {
                const ticketCount = selectedSeats.length;
                const totalPrice = ticketCount * ticketPrice;
                
                document.getElementById('selectedSeats').textContent = 
                    selectedSeats.length > 0 ? selectedSeats.join(', ') : '尚未選擇';
                document.getElementById('ticketCount').textContent = ticketCount;
                document.getElementById('totalPrice').textContent = totalPrice;
                
                // 更新隱藏欄位
                document.getElementById('selectedSeatsInput').value = selectedSeats.join(',');
                document.getElementById('ticketCountInput').value = ticketCount;
                document.getElementById('totalPriceInput').value = totalPrice;
                
                // 啟用/禁用下一步按鈕
                document.getElementById('nextBtn').disabled = ticketCount === 0;
            }
        </script>
    </body>
    </html>
    <?php
}

closeConnection($conn);
?>
