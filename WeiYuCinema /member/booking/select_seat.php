<?php
/**
 * 座位選擇頁面 (邏輯層：負責生成 HTML 字串)
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 檢查是否有傳入場次 ID
if (!isset($_GET['showingId']) || empty($_GET['showingId'])) {
    header("Location: booking.php?error=no_showing");
    exit();
}

$showingId = $_GET['showingId'];
$memberName = isset($_SESSION['memberName']) ? $_SESSION['memberName'] : '會員';

// 1. 取得場次與電影詳細資訊
$showingSql = "SELECT s.showingId, s.showingDate, s.startTime,
                      m.movieName, m.movieTime, m.gradeId, m.movieImg,
                      c.cinemaName, c.cinemaAddress,
                      t.theaterName, t.seatNumber,
                      pv.versionName
               FROM showing s
               JOIN movie m ON s.movieId = m.movieId
               JOIN theater t ON s.theaterId = t.theaterId
               JOIN cinema c ON t.cinemaId = c.cinemaId
               JOIN playVersion pv ON s.versionId = pv.versionId
               WHERE s.showingId = ?";

$stmt = mysqli_prepare($conn, $showingSql);
mysqli_stmt_bind_param($stmt, "s", $showingId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$showing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$showing) {
    header("Location: booking.php?error=showing_not_found");
    exit();
}

// 2. 生成「票種選項」HTML
$ticketSql = "SELECT * FROM ticketClass ORDER BY ticketClassPrice DESC";
$ticketResult = mysqli_query($conn, $ticketSql);
$ticketOptionsHtml = "";
$defaultPrice = 330; 

while ($row = mysqli_fetch_assoc($ticketResult)) {
    $selected = ($row['ticketClassId'] == 1) ? 'selected' : '';
    $ticketOptionsHtml .= "<option value='{$row['ticketClassId']}' data-price='{$row['ticketClassPrice']}' {$selected}>{$row['ticketClassName']} (NT\${$row['ticketClassPrice']})</option>";
    if ($row['ticketClassId'] == 1) $defaultPrice = $row['ticketClassPrice'];
}

// 3. 取得已售出座位 (關鍵：抓出 seatEmpty = 0 的資料)
$bookedSeats = [];
$seatsSql = "SELECT seatNumber FROM seatCondition WHERE showingId = ? AND seatEmpty = 0";
$stmt = mysqli_prepare($conn, $seatsSql);
mysqli_stmt_bind_param($stmt, "s", $showingId);
mysqli_stmt_execute($stmt);
$seatsResult = mysqli_stmt_get_result($stmt);

while ($seat = mysqli_fetch_assoc($seatsResult)) {
    // trim() 很重要！去除資料庫可能殘留的空白
    $bookedSeats[] = trim($seat['seatNumber']); 
}
mysqli_stmt_close($stmt);

// 4. 生成「座位圖」HTML 字串 (這裡就在 PHP 裡把顏色決定好)
$totalSeats = $showing['seatNumber'];
$seatsPerRow = 10; 
$rows = ceil($totalSeats / $seatsPerRow);
$seatMapHtml = ''; // 準備裝 HTML 的變數

for ($row = 1; $row <= $rows; $row++) {
    $rowLetter = chr(64 + $row); // A, B, C...
    
    $seatMapHtml .= '<div class="seat-row">';
    $seatMapHtml .= '<div class="row-label">' . $rowLetter . '</div>';
    
    for ($col = 1; $col <= $seatsPerRow; $col++) {
        $seatId = $rowLetter . $col;
        
        // --- 判斷生死：如果在已售出名單中 ---
        if (in_array($seatId, $bookedSeats)) {
            // 生成灰色的格子 (occupied)
            // 加上 style="pointer-events: none;" 確保真的點不到
            $seatMapHtml .= '<div class="seat occupied" data-seat="' . $seatId . '" style="background-color:#999 !important; cursor:not-allowed; pointer-events:none;">' . $col . '</div>';
        } else {
            // 生成綠色的格子 (available)
            $seatMapHtml .= '<div class="seat available" data-seat="' . $seatId . '">' . $col . '</div>';
        }
    }
    $seatMapHtml .= '</div>';
}

// 5. 載入模板並替換
$templateFile = 'templates/select_seat.html';

if (file_exists($templateFile)) {
    // 讀取純 HTML 檔案
    $template = file_get_contents($templateFile);
    
    // 把變數塞進去
    $template = str_replace('{{MEMBER_NAME}}', htmlspecialchars($memberName), $template);
    $template = str_replace('{{SHOWING_ID}}', htmlspecialchars($showing['showingId']), $template);
    $template = str_replace('{{MOVIE_NAME}}', htmlspecialchars($showing['movieName']), $template);
    $template = str_replace('{{CINEMA_NAME}}', htmlspecialchars($showing['cinemaName']), $template);
    $template = str_replace('{{THEATER_NAME}}', htmlspecialchars($showing['theaterName']), $template);
    $template = str_replace('{{SHOWING_DATE}}', htmlspecialchars($showing['showingDate']), $template);
    $template = str_replace('{{START_TIME}}', htmlspecialchars($showing['startTime']), $template);
    $template = str_replace('{{VERSION_NAME}}', htmlspecialchars($showing['versionName']), $template);
    
    // 塞入做好的 HTML 字串
    $template = str_replace('{{TICKET_OPTIONS}}', $ticketOptionsHtml, $template);
    $template = str_replace('{{DEFAULT_PRICE}}', $defaultPrice, $template);
    $template = str_replace('{{SEAT_MAP}}', $seatMapHtml, $template);
    
    echo $template;
} else {
    echo "錯誤：找不到模板檔案";
}

closeConnection($conn);
?>