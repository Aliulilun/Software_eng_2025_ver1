<?php
/**
 * 結帳處理核心 (已依照 bookingRecord 真實結構完全修正)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 1. 安全檢查
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: booking.php");
    exit();
}

// 2. 接收資料
$memberId = $_SESSION['memberId'];
$showingId = $_POST['showingId'];
$selectedSeats = $_POST['selectedSeats']; 
$grandTotalPrice = (int)$_POST['grandTotalPrice'];
$selectedMealsJson = $_POST['selectedMeals']; 

// --- 💰 金額自動救援 (防止 0 元錯誤) ---
if ($grandTotalPrice <= 0) {
    // 算出有幾張票 (根據逗號分隔)
    $seatArray = array_filter(explode(',', $selectedSeats));
    $ticketCount = count($seatArray);
    // 強制用 330 元計算
    $grandTotalPrice = $ticketCount * 330;
    
    // 如果有餐點，加上餐點錢 (這裡先做簡單處理，避免複雜化)
    $meals = json_decode($selectedMealsJson, true);
    if (!empty($meals)) {
        foreach ($meals as $m) {
            $grandTotalPrice += ($m['price'] * $m['quantity']);
        }
    }
}
// ----------------------------------------

// 開啟資料庫交易
mysqli_begin_transaction($conn);

try {
    // 步驟 A: 檢查餘額
    $balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ? FOR UPDATE";
    $stmt = mysqli_prepare($conn, $balanceSql);
    // 注意：看你的資料表 memberId 是 varchar(10)，所以這裡用 "s" (String)
    mysqli_stmt_bind_param($stmt, "s", $memberId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    
    if (!$row) throw new Exception("找不到會員資料");
    if ($row['balance'] < $grandTotalPrice) throw new Exception("餘額不足 (餘額: {$row['balance']}, 需付: $grandTotalPrice)");

    // 步驟 B: 執行扣款
    $newBalance = $row['balance'] - $grandTotalPrice;
    $updateBalanceSql = "UPDATE memberCashCard SET balance = ? WHERE memberId = ?";
    $stmt = mysqli_prepare($conn, $updateBalanceSql);
    mysqli_stmt_bind_param($stmt, "is", $newBalance, $memberId);
    if (!mysqli_stmt_execute($stmt)) throw new Exception("扣款失敗");

    // 步驟 C: 建立訂單 (完全對應 bookingRecord 結構)
    
    // 1. 準備資料
    // orderNumber (varchar 30): 產生一個唯一的訂單號 (年月日時分秒 + 4位亂數)
    $orderNumber = date('YmdHis') . rand(1000, 9999);
    
    // time (varchar 30): 訂單時間
    $time = date('Y-m-d H:i:s');
    
    // ticketTypeId (int): 假設 1=全票
    $ticketTypeId = 1;
    
    // ticketNums (int): 票數
    $ticketNums = count(explode(',', $selectedSeats));
    
    // orderStatusId (int): 假設 1=已付款
    $orderStatusId = 1;
    
    // getTicketNum (int): 取票狀態，預設 0
    $getTicketNum = 0;

    // 2. 執行插入 SQL
    $insertSql = "INSERT INTO bookingRecord 
                  (orderNumber, memberId, showingId, time, seat, chooseMeal, ticketTypeId, ticketNums, orderStatusId, totalPrice, getTicketNum) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                  
    $stmt = mysqli_prepare($conn, $insertSql);
    
    // 參數類型綁定: s=字串, i=整數
    // orderNumber(s), memberId(s), showingId(s), time(s), seat(s), chooseMeal(s)
    // ticketTypeId(i), ticketNums(i), orderStatusId(i), totalPrice(i), getTicketNum(i)
    // 總共 6個s, 5個i
    mysqli_stmt_bind_param($stmt, "ssssssiiiii", 
        $orderNumber, 
        $memberId, 
        $showingId, 
        $time, 
        $selectedSeats, 
        $selectedMealsJson, 
        $ticketTypeId, 
        $ticketNums, 
        $orderStatusId, 
        $grandTotalPrice, 
        $getTicketNum
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("訂單建立失敗: " . mysqli_error($conn));
    }

    // 步驟 D: 更新座位狀態 (seatCondition)
    $seatList = explode(',', $selectedSeats);
    $updateSeatSql = "UPDATE seatCondition SET seatEmpty = 0 WHERE showingId = ? AND seatNumber = ?";
    $stmt = mysqli_prepare($conn, $updateSeatSql);
    foreach ($seatList as $seat) {
        $seat = trim($seat);
        if(!empty($seat)) {
            mysqli_stmt_bind_param($stmt, "ss", $showingId, $seat);
            if (!mysqli_stmt_execute($stmt)) throw new Exception("劃位失敗 ($seat)");
        }
    }

    // 全部成功，提交！
    mysqli_commit($conn);
    
    // 跳轉到成功頁面 (帶上訂單編號)
    header("Location: success.php?bookingId=" . $orderNumber);
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    // 顯示詳細錯誤，方便除錯
    echo "<div style='padding:50px; text-align:center; font-family: sans-serif;'>";
    echo "<h1 style='color:red; font-size:48px;'>❌ 交易失敗</h1>";
    echo "<h3 style='color:#333;'>" . $e->getMessage() . "</h3>";
    echo "<br><a href='javascript:history.back()' style='padding:10px 20px; background:#666; color:white; text-decoration:none; border-radius:5px;'>返回上一頁</a>";
    echo "</div>";
    exit();
}

closeConnection($conn);
?>