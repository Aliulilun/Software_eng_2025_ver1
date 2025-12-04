# 威宇影城儲值卡系統 (Top Up) 使用說明

## 📁 檔案結構

已建立的檔案：

```
WeiYuCinema/
├── member/topup/                        # 儲值卡子系統
│   ├── index.php                       # 儲值卡首頁（PHP 控制層）
│   ├── topup_process.php               # 儲值處理後端
│   ├── transaction_history.php         # 完整交易紀錄查詢
│   └── templates/                      # HTML 模板目錄
│       ├── index.html                  # 儲值卡首頁模板
│       └── transaction_history.html    # 交易紀錄查詢模板
│
├── topup_transaction_table.sql         # 交易紀錄表建立腳本
├── add_topup_test_data.sql             # 測試資料腳本
└── docs/
    └── TOPUP_SYSTEM_README.md          # 本檔案
```

---

## 🎯 功能說明

### 1. 儲值卡首頁 (member/topup/index.php)

**功能：**
- ✅ 顯示會員儲值卡資訊（餘額、會員編號）
- ✅ 線上儲值功能（100-10,000 元）
- ✅ 多種付款方式（信用卡、金融卡、銀行轉帳、行動支付）
- ✅ 快速金額選擇（500、1,000、2,000、5,000 元）
- ✅ 顯示最近 10 筆交易紀錄
- ✅ 前端表單驗證

**訪問路徑：**
```
http://localhost/WeiYuCinema/member/topup/index.php
```

### 2. 儲值處理 (member/topup/topup_process.php)

**功能：**
- ✅ 儲值金額驗證（100-10,000 元）
- ✅ 付款方式驗證
- ✅ 資料庫交易處理（ACID 特性）
- ✅ 自動產生交易編號
- ✅ 更新會員餘額
- ✅ 記錄交易紀錄
- ✅ 錯誤處理和回滾機制

### 3. 交易紀錄查詢 (member/topup/transaction_history.php)

**功能：**
- ✅ 完整交易紀錄查詢
- ✅ 交易類型篩選（儲值/消費）
- ✅ 日期篩選
- ✅ 分頁顯示（每頁 20 筆）
- ✅ 交易狀態顯示（成功/失敗）

**訪問路徑：**
```
http://localhost/WeiYuCinema/member/topup/transaction_history.php
```

---

## 🗄️ 資料庫設計

### 1. memberCashCard 表（已存在）

| 欄位 | 類型 | 說明 |
|-----|------|------|
| memberId | varchar(10) | 會員編號（主鍵） |
| balance | int(11) | 目前餘額 |

### 2. topupTransaction 表（新增）

| 欄位 | 類型 | 說明 |
|-----|------|------|
| transactionId | varchar(20) | 交易編號（主鍵） |
| memberId | varchar(10) | 會員編號（外鍵） |
| transactionType | varchar(10) | 交易類型（TOPUP/CONSUME） |
| amount | int(11) | 交易金額 |
| balanceBefore | int(11) | 交易前餘額 |
| balanceAfter | int(11) | 交易後餘額 |
| transactionDate | datetime | 交易時間 |
| description | varchar(100) | 交易描述 |
| status | varchar(10) | 交易狀態（SUCCESS/FAILED） |

---

## 🚀 安裝步驟

### Step 1: 建立交易紀錄表

在 phpMyAdmin 執行以下 SQL：

```sql
-- 方法 1：執行完整腳本
source topup_transaction_table.sql;

-- 方法 2：執行測試資料腳本（包含表格建立）
source add_topup_test_data.sql;
```

### Step 2: 確認資料表建立成功

```sql
-- 檢查表格是否存在
SHOW TABLES LIKE '%topup%';

-- 檢查資料
SELECT COUNT(*) FROM topupTransaction;
SELECT * FROM memberCashCard;
```

### Step 3: 測試功能

1. 登入系統：`http://localhost/WeiYuCinema/auth/login.php`
2. 進入儲值卡：`http://localhost/WeiYuCinema/member/topup/index.php`

---

## 🧪 測試指南

### 測試帳號

| 會員 | Email | 密碼 | 初始餘額 | 交易紀錄 |
|-----|-------|------|---------|---------|
| 王小明 | ming123@gmail.com | pw1234 | NT$ 1,500 | 6 筆 |
| 陳美麗 | meili88@gmail.com | abc5678 | NT$ 800 | 3 筆 |
| 張大偉 | wei5566@gmail.com | xyz7788 | NT$ 2,300 | 4 筆 |
| 林育成 | yucheng77@gmail.com | pass2020 | NT$ 1,200 | 2 筆 |
| 管理員 | admin@weiyucinema.com | admin | NT$ 99,999 | 2 筆 |

### 測試流程

#### 1. 基本功能測試

1. **登入測試**
   - 使用 `ming123@gmail.com` / `pw1234` 登入
   - 確認跳轉到會員首頁

2. **儲值卡首頁測試**
   - 點擊導覽列「儲值卡 (Top Up)」
   - 確認顯示餘額：NT$ 1,500
   - 確認顯示最近交易紀錄

3. **儲值功能測試**
   - 輸入儲值金額：1000
   - 選擇付款方式：信用卡
   - 點擊「確認儲值」
   - 確認儲值成功，餘額變為 NT$ 2,500

#### 2. 表單驗證測試

1. **金額驗證**
   - 輸入 50（小於最小值）→ 應顯示錯誤
   - 輸入 15000（大於最大值）→ 應顯示錯誤
   - 輸入 -100（負數）→ 應顯示錯誤

2. **付款方式驗證**
   - 不選擇付款方式 → 應顯示錯誤

#### 3. 交易紀錄測試

1. **完整紀錄查詢**
   - 點擊「查看完整交易紀錄」
   - 確認顯示所有交易

2. **篩選功能測試**
   - 篩選「儲值」類型 → 只顯示儲值紀錄
   - 篩選「消費」類型 → 只顯示消費紀錄
   - 選擇特定日期 → 只顯示該日期紀錄

3. **分頁功能測試**
   - 如果紀錄超過 20 筆，測試分頁導覽

---

## 💡 功能特色

### 1. 安全性設計

#### 資料庫交易處理
```php
// 使用資料庫交易確保資料一致性
mysqli_autocommit($conn, FALSE);
try {
    // 鎖定餘額記錄
    $balanceSql = "SELECT balance FROM memberCashCard WHERE memberId = ? FOR UPDATE";
    
    // 更新餘額
    // 新增交易紀錄
    
    mysqli_commit($conn);  // 提交
} catch (Exception $e) {
    mysqli_rollback($conn);  // 回滾
}
```

#### 輸入驗證
- ✅ 前端 JavaScript 驗證
- ✅ 後端 PHP 驗證
- ✅ SQL Injection 防護
- ✅ XSS 防護

### 2. 使用者體驗

#### 直觀的介面設計
- 💳 儲值卡樣式設計
- 🎨 交易類型顏色區分（綠色=儲值，紅色=消費）
- 📱 響應式設計（適合手機瀏覽）

#### 便利功能
- ⚡ 快速金額選擇按鈕
- 🔍 交易紀錄篩選和搜尋
- 📄 分頁顯示避免頁面過長

### 3. 完整的交易追蹤

#### 詳細紀錄
- 交易編號（唯一識別）
- 交易前後餘額
- 交易時間（精確到秒）
- 交易描述（包含付款方式）
- 交易狀態（成功/失敗）

---

## 🔧 技術實現

### 1. PHP/HTML 分離架構

**PHP 控制層：**
```php
// member/topup/index.php
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 處理業務邏輯
$balance = getBalance($memberId);
$transactions = getRecentTransactions($memberId);

// 載入模板
include 'templates/index.html';
```

**HTML 模板：**
```html
<!-- member/topup/templates/index.html -->
<div class="balance">NT$ <?php echo number_format($balance); ?></div>
```

### 2. 交易編號生成

```php
// 格式：T + 年月日 + 3位隨機數
$transactionId = 'T' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

// 檢查重複並重新生成
while (transactionExists($transactionId)) {
    $transactionId = 'T' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}
```

### 3. 分頁實現

```php
$page = max(1, intval($_GET['page']));
$limit = 20;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM topupTransaction WHERE memberId = ? 
        ORDER BY transactionDate DESC 
        LIMIT ? OFFSET ?";
```

---

## 📊 測試資料說明

### 交易紀錄範例

系統已預設以下測試資料：

#### 王小明 (M0001) 的交易紀錄：
1. 2025-12-01 10:30 - 儲值 NT$ 1,000（信用卡）
2. 2025-12-01 14:20 - 消費 NT$ 200（購買電影票）
3. 2025-12-02 09:15 - 儲值 NT$ 500（行動支付）
4. 2025-12-02 19:45 - 消費 NT$ 150（購買餐點）
5. 2025-12-03 08:00 - 儲值 NT$ 1,000（金融卡）
6. 2025-12-03 20:30 - 消費 NT$ 650（購買電影票+餐點）

**最終餘額：** NT$ 1,500

---

## ⚠️ 注意事項

### 1. 資料庫需求

**必須先執行：**
```sql
source add_topup_test_data.sql;
```

### 2. 權限檢查

- 需要登入才能訪問
- 只能查看自己的儲值卡和交易紀錄
- 管理者和一般會員使用相同功能

### 3. 金額限制

- **最小儲值：** NT$ 100
- **最大儲值：** NT$ 10,000（單次）
- **餘額上限：** 無限制（實際應用建議設定上限）

### 4. 付款方式

目前為模擬付款，實際應用需整合：
- 信用卡金流 API
- 銀行轉帳 API
- 行動支付 API（Apple Pay、Google Pay 等）

---

## 🚀 未來擴展建議

### 1. 功能擴展

- **自動儲值：** 餘額不足時自動儲值
- **儲值優惠：** 大額儲值享折扣
- **儲值禮品：** 儲值送電影票或餐點
- **家庭共享：** 家庭成員共用儲值卡

### 2. 安全性增強

- **交易密碼：** 大額交易需輸入密碼
- **簡訊驗證：** 儲值時發送驗證碼
- **異常監控：** 偵測異常交易行為
- **交易限額：** 每日/每月儲值限額

### 3. 報表功能

- **消費分析：** 月度消費統計
- **儲值趨勢：** 儲值行為分析
- **優惠推薦：** 基於消費習慣推薦優惠

---

## 🐛 已知問題

目前無已知問題。

---

## 📞 技術支援

如有問題請參考：
- `docs/PROJECT_STRUCTURE.md` - 專案架構說明
- `docs/MIGRATION_GUIDE.md` - 遷移指南

---

**版本：** 1.0  
**更新日期：** 2025-12-04  
**功能狀態：** ✅ 完整實現  
**開發者：** 威宇影城開發團隊
