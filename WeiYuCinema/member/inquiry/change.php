<?php
/**
 * 修改訂票頁面 (修正版：由 PHP 生成座位圖，解決 JS 錯誤)
 */
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

$memberId = $_SESSION['memberId'];
$orderNumber = isset($_GET['orderNumber']) ? $_GET['orderNumber'] : '';

if (empty($orderNumber)) {
    echo "<script>alert('無效的訂單編號'); location.href='../../index.php';</script>";
    exit();
}

// 1. 查詢訂票記錄
$sql = "SELECT br.*, 
               m.movieName,
               c.cinemaName,
               t.theaterName, t.seatNumber as totalSeats,
               sh.showingDate, sh.startTime,
               os.orderStatusName
        FROM bookingRecord br
        JOIN showing sh ON br.showingId = sh.showingId
        JOIN movie m ON sh.movieId = m.movieId
        JOIN theater t ON sh.theaterId = t.theaterId
        JOIN cinema c ON t.cinemaId = c.cinemaId
        JOIN orderStatus os ON br.orderStatusId = os.orderStatusId
        WHERE br.orderNumber = ? AND br.memberId = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $orderNumber, $memberId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$record = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$record) {
    echo "<script>alert('找不到訂單'); location.href='../../index.php';</script>";
    exit();
}

// 2. 檢查是否可以修改 (邏輯保持不變)
$canChange = false;
$changeMessage = '';

if ($record['orderStatusId'] != 1) {
    $changeMessage = '此訂單狀態無法修改';
} else {
    $showDateTime = $record['showingDate'] . ' ' . $record['startTime'];
    if ((strtotime($showDateTime) - time()) < 7200) {
        $changeMessage = '開演前2小時內無法修改訂票';
    } else {
        $canChange = true;
    }
}

// 3. 準備座位資料
$currentSeats = explode(',', $record['seat']);
$occupiedSeats = []; // 別人佔用的

if ($canChange) {
    // 取得該場次所有被佔用的座位
    $seatSql = "SELECT seatNumber, seatEmpty FROM seatCondition WHERE showingId = ?";
    $seatStmt = mysqli_prepare($conn, $seatSql);
    mysqli_stmt_bind_param($seatStmt, "s", $record['showingId']);
    mysqli_stmt_execute($seatStmt);
    $seatResult = mysqli_stmt_get_result($seatStmt);
    
    while ($row = mysqli_fetch_assoc($seatResult)) {
        // 如果 seatEmpty=0 (佔用)，且不是我自己原本的座位 -> 視為被別人佔用
        if ($row['seatEmpty'] == 0 && !in_array($row['seatNumber'], $currentSeats)) {
            $occupiedSeats[] = $row['seatNumber'];
        }
    }
    mysqli_stmt_close($seatStmt);
}

// 4. 處理表單提交 (修改座位)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canChange) {
    $newSeats = isset($_POST['seats']) ? $_POST['seats'] : [];
    
    if (count($newSeats) != $record['ticketNums']) {
        $changeMessage = '座位數量不符';
    } else {
        mysqli_begin_transaction($conn);
        try {
            // A. 釋放舊座位
            $releaseSql = "UPDATE seatCondition SET seatEmpty = 1 WHERE showingId = ? AND seatNumber = ?";
            $stmt = mysqli_prepare($conn, $releaseSql);
            foreach ($currentSeats as $seat) {
                mysqli_stmt_bind_param($stmt, "ss", $record['showingId'], $seat);
                mysqli_stmt_execute($stmt);
            }
            
            // B. 佔用新座位
            $occupySql = "UPDATE seatCondition SET seatEmpty = 0 WHERE showingId = ? AND seatNumber = ?";
            $stmt = mysqli_prepare($conn, $occupySql);
            foreach ($newSeats as $seat) {
                mysqli_stmt_bind_param($stmt, "ss", $record['showingId'], $seat);
                mysqli_stmt_execute($stmt);
            }
            
            // C. 更新訂單
            $newSeatStr = implode(',', $newSeats);
            $updateOrder = "UPDATE bookingRecord SET seat = ? WHERE orderNumber = ?";
            $stmt = mysqli_prepare($conn, $updateOrder);
            mysqli_stmt_bind_param($stmt, "ss", $newSeatStr, $orderNumber);
            mysqli_stmt_execute($stmt);
            
            mysqli_commit($conn);
            // 成功後重新整理頁面
            echo "<script>alert('座位修改成功！'); location.href='../../index.php';</script>";
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $changeMessage = "修改失敗：" . $e->getMessage();
        }
    }
}

// 5. 生成座位圖 HTML (這裡生成字串，傳給 HTML 檔)
$seatMapHtml = '<div class="screen">SCREEN 銀幕</div>';
$totalSeats = $record['totalSeats'];
$seatsPerRow = 10;
$rows = ceil($totalSeats / $seatsPerRow);
$seatCounter = 1;

for ($r = 1; $r <= $rows; $r++) {
    $rowLabel = chr(64 + $r);
    $seatMapHtml .= '<div class="seat-row">';
    $seatMapHtml .= '<div class="row-label">' . $rowLabel . '</div>';
    
    for ($c = 1; $c <= $seatsPerRow; $c++) {
        if ($seatCounter > $totalSeats) break;
        $seatId = $rowLabel . $c;
        
        // 判斷樣式
        if (in_array($seatId, $occupiedSeats)) {
            $class = 'occupied'; // 紅色 (別人佔用)
        } elseif (in_array($seatId, $currentSeats)) {
            $class = 'selected'; // 黃色 (我的舊位)
        } else {
            $class = 'available'; // 綠色 (可選)
        }
        
        // 注意：這裡不加 disabled，改用 JS 控制
        $seatMapHtml .= "<div class='seat $class' data-id='$seatId' onclick='toggleSeat(this)'>$c</div>";
        $seatCounter++;
    }
    $seatMapHtml .= '</div>'; // end row
}

// 6. 載入並替換模板
$templateFile = 'templates/change.html';
if (file_exists($templateFile)) {
    $html = file_get_contents($templateFile);
    
    // 替換基本資訊
    $html = str_replace('{{MOVIE_NAME}}', htmlspecialchars($record['movieName']), $html);
    $html = str_replace('{{CINEMA_INFO}}', htmlspecialchars($record['cinemaName'] . ' (' . $record['theaterName'] . ')'), $html);
    $html = str_replace('{{SHOWING_TIME}}', htmlspecialchars($record['showingDate'] . ' ' . $record['startTime']), $html);
    $html = str_replace('{{CURRENT_SEATS}}', htmlspecialchars($record['seat']), $html);
    $html = str_replace('{{TICKET_COUNT}}', $record['ticketNums'], $html);
    $html = str_replace('{{MESSAGE}}', $changeMessage, $html);
    
    // 替換核心功能
    $html = str_replace('{{SEAT_MAP}}', $seatMapHtml, $html);
    
    // 替換 JS 變數 (這裡是關鍵，讓 JS 讀到正確的陣列)
    $html = str_replace('{{JS_MAX_SEATS}}', $record['ticketNums'], $html);
    $html = str_replace('{{JS_CURRENT_SEATS}}', json_encode($currentSeats), $html);
    
    echo $html;
} else {
    echo "錯誤：找不到模板 templates/change.html";
}

closeConnection($conn);
?>