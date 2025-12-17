# 威宇影城會員子系統說明文件

## 📋 系統概述

威宇影城會員子系統包含以下三個主要功能模組：
1. **訂票紀錄查詢 (Inquiry)** - 查看、退票、修改訂票
2. **儲值卡管理 (Top Up)** - 儲值、查看餘額、交易記錄
3. **會員資料管理 (Member Change)** - 修改個人資料、變更密碼

## 🎫 訂票紀錄查詢系統 (Inquiry)

### 功能特色
- ✅ 訂票記錄查詢與篩選
- ✅ 訂票詳情查看
- ✅ 線上退票申請
- ✅ 座位修改功能
- ✅ 取票資訊顯示

### 檔案結構
```
member/inquiry/
├── index.php              # 主查詢頁面
├── detail.php             # 訂票詳情頁面
├── refund.php             # 退票申請頁面
├── change.php             # 修改訂票頁面
├── inquiry.php            # 重定向檔案
└── templates/
    ├── index.html         # 主頁面模板
    ├── detail.html        # 詳情頁面模板
    ├── refund.html        # 退票頁面模板
    └── change.html        # 修改頁面模板
```

### 主要功能

#### 1. 訂票記錄查詢
- **路徑**: `/member/inquiry/index.php`
- **功能**: 顯示會員所有訂票記錄
- **篩選條件**: 訂單編號、訂票日期、訂單狀態
- **顯示資訊**: 電影資訊、場次資訊、座位、餐點、金額

#### 2. 訂票詳情查看
- **路徑**: `/member/inquiry/detail.php?orderNumber={訂單編號}`
- **功能**: 顯示單筆訂票的完整資訊
- **包含內容**: 電影詳情、場次資訊、座位配置、餐點明細、付款資訊、取票說明

#### 3. 退票申請
- **路徑**: `/member/inquiry/refund.php?orderNumber={訂單編號}`
- **限制條件**: 
  - 訂單狀態必須為「已完成」
  - 必須在開演前2小時完成
- **退款方式**: 全額退回會員儲值卡
- **處理流程**: 更新訂單狀態 → 釋放座位 → 退款 → 記錄交易

#### 4. 修改訂票
- **路徑**: `/member/inquiry/change.php?orderNumber={訂單編號}`
- **限制條件**: 
  - 訂單狀態必須為「已完成」
  - 必須在開演前2小時完成
- **修改範圍**: 僅限座位修改，不可更改場次或票數
- **處理流程**: 釋放原座位 → 佔用新座位 → 更新訂票記錄

### 資料庫相關

#### 主要資料表
- `bookingRecord` - 訂票記錄
- `showing` - 電影場次
- `seatCondition` - 座位狀況
- `orderStatus` - 訂單狀態
- `topupTransaction` - 交易記錄（退款）

#### 訂單狀態
- `1` - 已完成
- `2` - 已取消  
- `3` - 已退票
- `4` - 處理中

## 💳 儲值卡管理系統 (Top Up)

### 功能特色
- ✅ 線上儲值功能
- ✅ 餘額查詢
- ✅ 交易記錄查看
- ✅ 自動建立儲值卡
- ✅ 交易安全保護

### 檔案結構
```
member/topup/
├── index.php                    # 儲值卡主頁面
├── topup_process.php           # 儲值處理邏輯
├── transaction_history.php     # 交易記錄頁面
└── templates/
    ├── index.html              # 主頁面模板
    └── transaction_history.html # 交易記錄模板
```

### 主要功能

#### 1. 儲值卡管理
- **路徑**: `/member/topup/index.php`
- **功能**: 顯示目前餘額、進行儲值、查看最近交易
- **儲值選項**: 100, 300, 500, 1000, 2000, 5000 元或自訂金額
- **安全機制**: 金額驗證、交易記錄

#### 2. 交易記錄
- **路徑**: `/member/topup/transaction_history.php`
- **功能**: 查看完整交易歷史
- **篩選條件**: 交易類型、日期範圍
- **交易類型**: TOPUP(儲值)、CONSUME(消費)、REFUND(退款)

### 資料庫相關

#### 主要資料表
- `memberCashCard` - 會員儲值卡
- `topupTransaction` - 交易記錄

#### 交易類型
- `TOPUP` - 儲值
- `CONSUME` - 消費（購票）
- `REFUND` - 退款

## 👤 會員資料管理系統 (Member Change)

### 功能特色
- ✅ 個人資料修改
- ✅ 密碼變更
- ✅ 資料驗證
- ✅ 重複檢查
- ✅ 安全保護

### 檔案結構
```
member/profile/
├── profile.php           # 會員資料主頁面
├── update_profile.php    # 資料更新處理
├── update_password.php   # 密碼更新處理
└── templates/
    └── profile.html      # 主頁面模板
```

### 主要功能

#### 1. 個人資料管理
- **路徑**: `/member/profile/profile.php`
- **可修改項目**: 姓名、電子信箱、手機號碼、生日、付款帳號
- **驗證規則**: 
  - 電子信箱格式驗證
  - 手機號碼10位數字
  - 付款帳號格式（3英文+11數字）
  - 重複性檢查

#### 2. 密碼變更
- **安全驗證**: 需輸入目前密碼
- **密碼規則**: 6-50個字元
- **確認機制**: 新密碼需輸入兩次確認

### 資料庫相關

#### 主要資料表
- `memberProfile` - 會員基本資料

## 🔧 系統整合

### 統一模板系統
所有頁面都使用統一的模板系統：
- **CSS**: `/static/css/style.css`
- **JavaScript**: `/static/js/script.js`
- **Header**: `/static/templates/header.html`
- **Footer**: `/static/templates/footer.html`

### 導航整合
會員首頁 (`/member/index.php`) 提供所有功能的快速入口：
- 🎬 瀏覽電影資訊
- 🎟️ 立即購票
- 📋 訂票紀錄查詢
- 💳 儲值卡管理
- 👤 個人資料管理
- 🏢 影城資訊

### 安全機制
- **登入檢查**: 所有頁面都需要登入驗證
- **SQL注入防護**: 使用 prepared statements
- **XSS防護**: 使用 htmlspecialchars
- **CSRF保護**: 表單驗證
- **資料驗證**: 前端與後端雙重驗證

## 📊 測試資料

### 場次資料更新
執行 `update_showing_dates.sql` 更新場次日期：
```sql
-- 更新場次日期為今天和未來幾天
UPDATE showing SET 
    showingDate = CASE 
        WHEN showingId IN ('S00101', 'S00201') THEN DATE_ADD(CURDATE(), INTERVAL 0 DAY)
        WHEN showingId IN ('S00102', 'S00202') THEN DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        -- ... 更多場次
    END;
```

### 測試帳號
- **一般會員**: M0001 / password123
- **管理員**: (待實作)

## 🚀 使用說明

### 1. 啟動系統
1. 確保 XAMPP 服務運行
2. 導入 `WeiYuCinema.sql` 資料庫
3. 執行 `update_showing_dates.sql` 更新場次日期
4. 訪問 `http://localhost/WeiYuCinema/`

### 2. 功能測試
1. **登入系統**: 使用測試帳號登入
2. **儲值測試**: 進入儲值卡管理，進行儲值
3. **購票測試**: 選擇場次、座位、餐點，完成購票
4. **查詢測試**: 查看訂票記錄
5. **退票測試**: 申請退票（需在開演前2小時）
6. **修改測試**: 修改座位（需在開演前2小時）
7. **資料修改**: 更新個人資料或密碼

## 🔍 故障排除

### 常見問題
1. **購票頁面空白**: 檢查場次日期是否為未來日期
2. **資料庫連接失敗**: 確認 XAMPP MySQL 服務運行
3. **頁面顯示異常**: 檢查 CSS/JS 檔案路徑
4. **登入失效**: 檢查 session 設定

### 日誌檢查
- PHP 錯誤日誌: `/Applications/XAMPP/xamppfiles/logs/php_error_log`
- Apache 錯誤日誌: `/Applications/XAMPP/xamppfiles/logs/error_log`

## 📝 開發備註

### 待實作功能
- [ ] 管理員後台系統
- [ ] 電子郵件通知
- [ ] 手機簡訊通知
- [ ] 優惠券系統
- [ ] 會員等級制度

### 已知限制
- 座位修改僅限同場次
- 退票無手續費計算
- 餐點修改功能未實作
- 批量操作功能未實作

---

**開發完成日期**: 2025-12-04  
**版本**: v1.0  
**開發者**: AI Assistant  
**專案**: 威宇影城售票系統
