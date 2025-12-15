<?php
/**
 * 座位選擇頁面 (升級版：支援多種票價)
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

if (!isset($_GET['showingId']) || empty($_GET['showingId'])) {
    header("Location: booking.php?error=no_showing");
    exit();
}

$showingId = $_GET['showingId'];

// 1. 取得場次資訊 (這裡只需要基本的電影資訊，不用再強制 JOIN 票價了)
$showingSql = "SELECT s.showingId, s.showingDate, s.startTime,
                      m.movieId, m.movieName, m.movieTime, m.gradeId,
                      c.cinemaId, c.cinemaName, c.cinemaAddress,
                      t.theaterId, t.theaterName, t.seatNumber,
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

// 2. 取得所有「票種」資訊 (全票、優待票、敬老票)
$ticketSql = "SELECT * FROM ticketClass ORDER BY ticketClassPrice DESC";
$ticketResult = mysqli_query($conn, $ticketSql);
$ticketOptions = "";
$defaultPrice = 330; // 預設價格

// 製作下拉選單的 HTML
while ($row = mysqli_fetch_assoc($ticketResult)) {
    // 預設選中全票 (ID=1)
    $selected = ($row['ticketClassId'] == 1) ? 'selected' : '';
    // 將價格存入 data-price 屬性，讓 JS 讀取
    $ticketOptions .= "<option value='{$row['ticketClassId']}' data-price='{$row['ticketClassPrice']}' {$selected}>{$row['ticketClassName']} (\${$row['ticketClassPrice']})</option>";
    
    if ($row['ticketClassId'] == 1) {
        $defaultPrice = $row['ticketClassPrice'];
    }
}

// 3. 取得座位狀態
$seatsSql = "SELECT seatNumber, seatEmpty FROM seatCondition WHERE showingId = ?";
$stmt = mysqli_prepare($conn, $seatsSql);
mysqli_stmt_bind_param($stmt, "s", $showingId);
mysqli_stmt_execute($stmt);
$seatsResult = mysqli_stmt_get_result($stmt);

$seatStatus = [];
while ($seat = mysqli_fetch_assoc($seatsResult)) {
    $seatStatus[$seat['seatNumber']] = $seat['seatEmpty']; 
}
mysqli_stmt_close($stmt);

// 計算排數
$totalSeats = $showing['seatNumber'];
$seatsPerRow = 10; 
$rows = ceil($totalSeats / $seatsPerRow);

// 載入模板
$templateFile = 'templates/select_seat.html';
if (file_exists($templateFile)) {
    $template = file_get_contents($templateFile);
    
    // 替換變數
    $template = str_replace('{{MEMBER_NAME}}', htmlspecialchars($memberName), $template);
    $template = str_replace('{{SHOWING_ID}}', htmlspecialchars($showing['showingId']), $template);
    $template = str_replace('{{MOVIE_NAME}}', htmlspecialchars($showing['movieName']), $template);
    $template = str_replace('{{CINEMA_NAME}}', htmlspecialchars($showing['cinemaName']), $template);
    $template = str_replace('{{THEATER_NAME}}', htmlspecialchars($showing['theaterName']), $template);
    $template = str_replace('{{SHOWING_DATE}}', htmlspecialchars($showing['showingDate']), $template);
    $template = str_replace('{{START_TIME}}', htmlspecialchars($showing['startTime']), $template);
    $template = str_replace('{{VERSION_NAME}}', htmlspecialchars($showing['versionName']), $template);
    
    // 注入我們剛剛做好的「票種選單」
    $template = str_replace('{{TICKET_OPTIONS}}', $ticketOptions, $template);
    $template = str_replace('{{DEFAULT_PRICE}}', $defaultPrice, $template);
    
    // 生成座位圖 HTML
    $seatMap = '<div class="screen">螢幕</div><div class="seat-map">';
    $seatNumber = 1;
    for ($row = 1; $row <= $rows; $row++) {
        $rowLetter = chr(64 + $row); 
        $seatMap .= '<div class="seat-row"><div class="row-label">' . $rowLetter . '</div>';
        for ($col = 1; $col <= $seatsPerRow && $seatNumber <= $totalSeats; $col++) {
            $seatId = $rowLetter . $col;
            $isOccupied = (isset($seatStatus[$seatId]) && $seatStatus[$seatId] == 0);
            $seatClass = $isOccupied ? 'seat occupied' : 'seat available';
            $seatMap .= '<div class="' . $seatClass . '" data-seat="' . $seatId . '">' . $col . '</div>';
            $seatNumber++;
        }
        $seatMap .= '</div>';
    }
    $seatMap .= '</div>';
    
    $template = str_replace('{{SEAT_MAP}}', $seatMap, $template);
    echo $template;
} else {
    echo "錯誤：找不到模板檔案";
}
closeConnection($conn);
?>