<?php
/**
 * 餐點選擇頁面
 * 威宇影城售票系統
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 檢查必要的POST資料
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['showingId']) || !isset($_POST['selectedSeats']) || 
    !isset($_POST['ticketCount']) || !isset($_POST['totalPrice'])) {
    header("Location: booking.php?error=invalid_request");
    exit();
}

$showingId = $_POST['showingId'];
$selectedSeats = $_POST['selectedSeats'];
$ticketCount = (int)$_POST['ticketCount'];
$ticketTotalPrice = (int)$_POST['totalPrice'];

// 驗證資料
if (empty($selectedSeats) || $ticketCount <= 0) {
    header("Location: select_seat.php?showingId=" . urlencode($showingId) . "&error=invalid_seats");
    exit();
}

// 取得場次資訊
$showingSql = "SELECT s.showingId, s.showingDate, s.startTime,
                      m.movieId, m.movieName, m.movieLength, m.movieGrade,
                      c.cinemaId, c.cinemaName,
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

// 取得餐點資料
$mealsSql = "SELECT m.mealsId, m.mealsName, m.mealsPrice, m.mealsPhoto,
                    mt.mealsTypeName
             FROM meals m
             JOIN mealsType mt ON m.mealsTypeId = mt.mealsTypeId
             ORDER BY mt.mealsTypeId, m.mealsPrice ASC";

$mealsResult = mysqli_query($conn, $mealsSql);
$meals = [];
while ($meal = mysqli_fetch_assoc($mealsResult)) {
    $meals[$meal['mealsTypeName']][] = $meal;
}

// 載入HTML模板
$templateFile = 'templates/select_meal.html';
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
    $template = str_replace('{{SELECTED_SEATS}}', htmlspecialchars($selectedSeats), $template);
    $template = str_replace('{{TICKET_COUNT}}', htmlspecialchars($ticketCount), $template);
    $template = str_replace('{{TICKET_TOTAL_PRICE}}', htmlspecialchars($ticketTotalPrice), $template);
    
    // 生成餐點列表
    $mealsHtml = '';
    foreach ($meals as $typeName => $typeItems) {
        $mealsHtml .= '<div class="meal-category">';
        $mealsHtml .= '<h3>' . htmlspecialchars($typeName) . '</h3>';
        $mealsHtml .= '<div class="meal-items">';
        
        foreach ($typeItems as $meal) {
            $mealsHtml .= '<div class="meal-item">';
            $mealsHtml .= '<div class="meal-image">';
            $mealsHtml .= '<img src="../../images/meals/' . htmlspecialchars($meal['mealsPhoto']) . '" alt="' . htmlspecialchars($meal['mealsName']) . '" onerror="this.src=\'../../images/meals/default.jpg\'">';
            $mealsHtml .= '</div>';
            $mealsHtml .= '<div class="meal-info">';
            $mealsHtml .= '<h4>' . htmlspecialchars($meal['mealsName']) . '</h4>';
            $mealsHtml .= '<p class="meal-price">NT$ ' . htmlspecialchars($meal['mealsPrice']) . '</p>';
            $mealsHtml .= '</div>';
            $mealsHtml .= '<div class="meal-quantity">';
            $mealsHtml .= '<button type="button" class="qty-btn minus" data-meal-id="' . htmlspecialchars($meal['mealsId']) . '">-</button>';
            $mealsHtml .= '<input type="number" class="qty-input" data-meal-id="' . htmlspecialchars($meal['mealsId']) . '" data-meal-name="' . htmlspecialchars($meal['mealsName']) . '" data-meal-price="' . htmlspecialchars($meal['mealsPrice']) . '" value="0" min="0" max="10" readonly>';
            $mealsHtml .= '<button type="button" class="qty-btn plus" data-meal-id="' . htmlspecialchars($meal['mealsId']) . '">+</button>';
            $mealsHtml .= '</div>';
            $mealsHtml .= '</div>';
        }
        
        $mealsHtml .= '</div>';
        $mealsHtml .= '</div>';
    }
    
    $template = str_replace('{{MEALS_LIST}}', $mealsHtml, $template);
    
    echo $template;
} else {
    // 如果模板不存在，顯示基本頁面
    ?>
    <!DOCTYPE html>
    <html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>選擇餐點 - 威宇影城</title>
        <style>
            .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
            .booking-info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .meal-category { margin-bottom: 30px; }
            .meal-category h3 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
            .meal-items { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
            .meal-item { border: 1px solid #ddd; border-radius: 5px; padding: 15px; display: flex; gap: 15px; align-items: center; }
            .meal-image img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
            .meal-info { flex: 1; }
            .meal-info h4 { margin: 0 0 5px 0; }
            .meal-price { color: #e74c3c; font-weight: bold; margin: 0; }
            .meal-quantity { display: flex; align-items: center; gap: 10px; }
            .qty-btn { width: 30px; height: 30px; border: 1px solid #ddd; background: #f8f9fa; cursor: pointer; }
            .qty-input { width: 50px; text-align: center; border: 1px solid #ddd; }
            .order-summary { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 20px; }
            .btn { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn:hover { background-color: #0056b3; }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <h1>威宇影城 - 選擇餐點</h1>
                <nav style="text-align: right;">
                    <span>歡迎，<?php echo htmlspecialchars($memberName); ?>！</span> | 
                    <a href="../index.php">會員首頁</a> | 
                    <a href="../../auth/logout.php">登出</a>
                </nav>
            </header>
            <hr>
            
            <main>
                <!-- 訂票資訊 -->
                <div class="booking-info">
                    <h2><?php echo htmlspecialchars($showing['movieName']); ?></h2>
                    <p><strong>影城：</strong><?php echo htmlspecialchars($showing['cinemaName']); ?> - <?php echo htmlspecialchars($showing['theaterName']); ?></p>
                    <p><strong>場次：</strong><?php echo htmlspecialchars($showing['showingDate']); ?> <?php echo htmlspecialchars($showing['startTime']); ?></p>
                    <p><strong>座位：</strong><?php echo htmlspecialchars($selectedSeats); ?></p>
                    <p><strong>票數：</strong><?php echo $ticketCount; ?> 張</p>
                    <p><strong>票價小計：</strong>NT$ <?php echo $ticketTotalPrice; ?></p>
                </div>
                
                <!-- 餐點選擇 -->
                <h2>選擇餐點（可選）</h2>
                
                <?php foreach ($meals as $typeName => $typeItems): ?>
                <div class="meal-category">
                    <h3><?php echo htmlspecialchars($typeName); ?></h3>
                    <div class="meal-items">
                        <?php foreach ($typeItems as $meal): ?>
                        <div class="meal-item">
                            <div class="meal-image">
                                <img src="../../images/meals/<?php echo htmlspecialchars($meal['mealsPhoto']); ?>" 
                                     alt="<?php echo htmlspecialchars($meal['mealsName']); ?>" 
                                     onerror="this.src='../../images/meals/default.jpg'">
                            </div>
                            <div class="meal-info">
                                <h4><?php echo htmlspecialchars($meal['mealsName']); ?></h4>
                                <p class="meal-price">NT$ <?php echo htmlspecialchars($meal['mealsPrice']); ?></p>
                            </div>
                            <div class="meal-quantity">
                                <button type="button" class="qty-btn minus" data-meal-id="<?php echo htmlspecialchars($meal['mealsId']); ?>">-</button>
                                <input type="number" class="qty-input" 
                                       data-meal-id="<?php echo htmlspecialchars($meal['mealsId']); ?>"
                                       data-meal-name="<?php echo htmlspecialchars($meal['mealsName']); ?>"
                                       data-meal-price="<?php echo htmlspecialchars($meal['mealsPrice']); ?>"
                                       value="0" min="0" max="10" readonly>
                                <button type="button" class="qty-btn plus" data-meal-id="<?php echo htmlspecialchars($meal['mealsId']); ?>">+</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- 訂單摘要 -->
                <div class="order-summary">
                    <h3>訂單摘要</h3>
                    <div id="selectedMeals">尚未選擇餐點</div>
                    <p><strong>票價小計：</strong>NT$ <?php echo $ticketTotalPrice; ?></p>
                    <p><strong>餐點小計：</strong>NT$ <span id="mealTotal">0</span></p>
                    <p><strong>總金額：</strong>NT$ <span id="grandTotal"><?php echo $ticketTotalPrice; ?></span></p>
                    
                    <form action="confirm.php" method="POST" id="mealForm">
                        <input type="hidden" name="showingId" value="<?php echo htmlspecialchars($showing['showingId']); ?>">
                        <input type="hidden" name="selectedSeats" value="<?php echo htmlspecialchars($selectedSeats); ?>">
                        <input type="hidden" name="ticketCount" value="<?php echo htmlspecialchars($ticketCount); ?>">
                        <input type="hidden" name="ticketTotalPrice" value="<?php echo htmlspecialchars($ticketTotalPrice); ?>">
                        <input type="hidden" name="selectedMeals" id="selectedMealsInput">
                        <input type="hidden" name="mealTotalPrice" id="mealTotalPriceInput" value="0">
                        <input type="hidden" name="grandTotalPrice" id="grandTotalPriceInput" value="<?php echo $ticketTotalPrice; ?>">
                        
                        <button type="submit" class="btn">下一步：確認訂單</button>
                    </form>
                </div>
                
                <p><a href="select_seat.php?showingId=<?php echo urlencode($showingId); ?>" class="btn" style="background-color: #6c757d;">← 返回座位選擇</a></p>
            </main>
        </div>
        
        <script>
            const ticketTotalPrice = <?php echo $ticketTotalPrice; ?>;
            let selectedMeals = {};
            
            // 數量按鈕事件
            document.querySelectorAll('.qty-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const mealId = this.dataset.mealId;
                    const input = document.querySelector(`.qty-input[data-meal-id="${mealId}"]`);
                    let currentValue = parseInt(input.value);
                    
                    if (this.classList.contains('plus')) {
                        if (currentValue < 10) {
                            input.value = currentValue + 1;
                        }
                    } else if (this.classList.contains('minus')) {
                        if (currentValue > 0) {
                            input.value = currentValue - 1;
                        }
                    }
                    
                    updateMealSelection();
                });
            });
            
            function updateMealSelection() {
                selectedMeals = {};
                let mealTotal = 0;
                
                document.querySelectorAll('.qty-input').forEach(input => {
                    const quantity = parseInt(input.value);
                    if (quantity > 0) {
                        const mealId = input.dataset.mealId;
                        const mealName = input.dataset.mealName;
                        const mealPrice = parseInt(input.dataset.mealPrice);
                        
                        selectedMeals[mealId] = {
                            name: mealName,
                            price: mealPrice,
                            quantity: quantity,
                            subtotal: mealPrice * quantity
                        };
                        
                        mealTotal += mealPrice * quantity;
                    }
                });
                
                updateOrderSummary(mealTotal);
            }
            
            function updateOrderSummary(mealTotal) {
                const grandTotal = ticketTotalPrice + mealTotal;
                
                // 更新餐點列表顯示
                const selectedMealsDiv = document.getElementById('selectedMeals');
                if (Object.keys(selectedMeals).length === 0) {
                    selectedMealsDiv.innerHTML = '尚未選擇餐點';
                } else {
                    let mealsHtml = '<ul>';
                    for (const [mealId, meal] of Object.entries(selectedMeals)) {
                        mealsHtml += `<li>${meal.name} x ${meal.quantity} = NT$ ${meal.subtotal}</li>`;
                    }
                    mealsHtml += '</ul>';
                    selectedMealsDiv.innerHTML = mealsHtml;
                }
                
                // 更新金額
                document.getElementById('mealTotal').textContent = mealTotal;
                document.getElementById('grandTotal').textContent = grandTotal;
                
                // 更新隱藏欄位
                document.getElementById('selectedMealsInput').value = JSON.stringify(selectedMeals);
                document.getElementById('mealTotalPriceInput').value = mealTotal;
                document.getElementById('grandTotalPriceInput').value = grandTotal;
            }
        </script>
    </body>
    </html>
    <?php
}

closeConnection($conn);
?>
