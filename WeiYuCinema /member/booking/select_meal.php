<?php
/**
 * 餐點選擇頁面 (已修正：強制使用全票價格)
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
// 修正重點：JOIN ticketClass tc ON tc.ticketClassId = 1
// 這樣會強制抓取 ID 為 1 的票種 (全票) 價格，解決票價為 0 的問題
$showingSql = "SELECT s.showingId, s.showingDate, s.startTime,
                      m.movieId, m.movieName, m.movieTime, m.gradeId,
                      c.cinemaId, c.cinemaName,
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
if ($mealsResult) {
    while ($meal = mysqli_fetch_assoc($mealsResult)) {
        $meals[$meal['mealsTypeName']][] = $meal;
    }
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
    if (empty($meals)) {
        $mealsHtml = '<div class="no-meals-message">目前沒有供應餐點</div>';
    } else {
        foreach ($meals as $typeName => $typeItems) {
            $mealsHtml .= '<div class="meal-category">';
            $mealsHtml .= '<h3>' . htmlspecialchars($typeName) . '</h3>';
            $mealsHtml .= '<div class="meal-items">';
    
        foreach ($typeItems as $meal) {
        // 【修改 1】 這裡拿掉了原本多餘的 'meals/'，改成直接對應你的圖片資料夾
            $imgSrc = '../../static/images/' . htmlspecialchars($meal['mealsPhoto']);
        
            $mealsHtml .= '<div class="meal-item">';
            $mealsHtml .= '<div class="meal-image">';
        // 圖片讀不到時顯示預設圖
            $mealsHtml .= '<img src="' . $imgSrc . '" alt="' . htmlspecialchars($meal['mealsName']) . '" onerror="this.onerror=null;this.src=\'../../static/images/default_meal.jpg\'">';
            $mealsHtml .= '</div>';
            $mealsHtml .= '<div class="meal-info">';
            $mealsHtml .= '<h4>' . htmlspecialchars($meal['mealsName']) . '</h4>';
            $mealsHtml .= '<p class="meal-price">NT$ ' . htmlspecialchars($meal['mealsPrice']) . '</p>';
            $mealsHtml .= '</div>';
            $mealsHtml .= '<div class="meal-quantity">';
            $mealsHtml .= '<button type="button" class="qty-btn minus" data-meal-id="' . htmlspecialchars($meal['mealsId']) . '">-</button>';
        
        // 【修改 2】 將 type="number" 改為 type="text"，確保數字 '0' 一定會顯示出來
            $mealsHtml .= '<input type="text" class="qty-input" data-meal-id="' . htmlspecialchars($meal['mealsId']) . '" data-meal-name="' . htmlspecialchars($meal['mealsName']) . '" data-meal-price="' . htmlspecialchars($meal['mealsPrice']) . '" value="0" readonly>';
        
            $mealsHtml .= '<button type="button" class="qty-btn plus" data-meal-id="' . htmlspecialchars($meal['mealsId']) . '">+</button>';
            $mealsHtml .= '</div>';
            $mealsHtml .= '</div>';
    }
    
    $mealsHtml .= '</div>';
    $mealsHtml .= '</div>';
}
    }
    
    $template = str_replace('{{MEALS_LIST}}', $mealsHtml, $template);
    
    echo $template;
} else {
    // 如果模板不存在，顯示錯誤訊息
    echo "錯誤：找不到模板檔案 ($templateFile)";
}

closeConnection($conn);
?>