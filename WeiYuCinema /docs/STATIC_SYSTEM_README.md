# 威宇影城 - 統一樣式系統使用指南

## 📁 檔案結構

```
WeiYuCinema/
├── static/
│   ├── css/
│   │   └── style.css          # 統一CSS樣式
│   ├── js/
│   │   └── script.js          # 核心JavaScript功能
│   └── templates/
│       ├── header.html        # 頁首模板
│       ├── footer.html        # 頁尾模板
│       └── page_template.html # 頁面模板範例
└── includes/
    └── session.php            # Session狀態API (已更新)
```

## 🎨 設計風格

- **主色調**：黑色 (#121212) + 威秀黃 (#ffc107)
- **風格**：現代化、電影院風格
- **響應式**：支援手機、平板、桌面
- **動畫效果**：載入動畫、懸停效果、漸變效果

## 🚀 使用方式

### 1. 基本頁面結構

每個頁面都應該使用以下結構：

```html
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>頁面標題 - 威宇影城</title>
    <link rel="stylesheet" href="/WeiYuCinema/static/css/style.css">
</head>
<body>
    <!-- 頁首佔位符 -->
    <div id="header-placeholder"></div>
    
    <!-- 主要內容 -->
    <div class="container">
        <div class="page-title">
            <h1>頁面標題</h1>
            <p>頁面描述</p>
        </div>
        
        <div class="section-box">
            <!-- 頁面內容 -->
        </div>
    </div>
    
    <!-- 頁尾佔位符 -->
    <div id="footer-placeholder"></div>
    
    <!-- 載入核心JavaScript -->
    <script src="/WeiYuCinema/static/js/script.js"></script>
</body>
</html>
```

### 2. 自動功能

JavaScript系統會自動：

- ✅ 載入頁首和頁尾模板
- ✅ 檢查使用者登入狀態
- ✅ 根據角色顯示對應導覽選單
- ✅ 提供通知訊息功能
- ✅ 增強表單互動效果

### 3. 導覽選單邏輯

系統會根據登入狀態自動顯示：

- **未登入**：首頁、會員登入/註冊、後台管理
- **一般會員**：會員首頁、瀏覽電影、購票服務、訂票紀錄、儲值卡、會員資料
- **管理員**：管理首頁、電影管理、影城管理、訂單管理、會員管理

## 🎯 CSS 類別參考

### 佈局類別

```css
.container          /* 主要內容容器 */
.section-box        /* 內容區塊 */
.page-title         /* 頁面標題區域 */
.section-title      /* 區塊標題 */
```

### 按鈕類別

```css
.btn                /* 基本按鈕 */
.btn-primary        /* 主要按鈕 (黃色) */
.btn-secondary      /* 次要按鈕 (灰色) */
.btn-success        /* 成功按鈕 (綠色) */
.btn-danger         /* 危險按鈕 (紅色) */
.btn-warning        /* 警告按鈕 (黃色) */
.btn-info           /* 資訊按鈕 (藍色) */
```

### 表單類別

```css
.form-group         /* 表單群組 */
input, select, textarea  /* 表單元件 */
```

### 工具類別

```css
.hidden             /* 隱藏元素 */
.text-center        /* 文字置中 */
.text-muted         /* 淡色文字 */
.mb-1, .mb-2, .mb-3 /* 下邊距 */
.mt-1, .mt-2, .mt-3 /* 上邊距 */
.fade-in            /* 淡入動畫 */
.glow               /* 發光效果 */
```

## 📱 JavaScript API

### 全域函數

```javascript
// 顯示通知訊息
showNotification(message, type);
// type: 'success', 'error', 'warning', 'info'

// 獲取登入狀態
window.weiYuCinema.getSessionData();
```

### 特殊功能

```javascript
// 初始化座位選擇 (用於購票頁面)
window.initSeatSelection(ticketPrice);

// 初始化餐點選擇 (用於購票頁面)
window.initMealSelection(ticketTotalPrice);
```

## 🔧 自訂化

### 修改顏色主題

在 `static/css/style.css` 的 `:root` 區塊修改：

```css
:root {
    --primary-color: #ffc107;    /* 主色調 */
    --bg-color: #121212;         /* 背景色 */
    --card-bg: #1e1e1e;          /* 卡片背景 */
    --text-color: #ffffff;       /* 文字顏色 */
    /* ... 其他顏色變數 */
}
```

### 新增自訂樣式

在 `static/css/style.css` 底部新增：

```css
/* 自訂樣式 */
.my-custom-class {
    /* 您的樣式 */
}
```

## 📋 遷移現有頁面

### 步驟 1：更新HTML結構

1. 加入CSS連結：`<link rel="stylesheet" href="/WeiYuCinema/static/css/style.css">`
2. 替換現有header為：`<div id="header-placeholder"></div>`
3. 替換現有footer為：`<div id="footer-placeholder"></div>`
4. 加入JavaScript：`<script src="/WeiYuCinema/static/js/script.js"></script>`

### 步驟 2：更新CSS類別

- 將內容包在 `<div class="container">` 中
- 使用 `<div class="section-box">` 包裝內容區塊
- 更新按鈕類別為 `.btn .btn-primary` 等

### 步驟 3：測試功能

1. 檢查頁首/頁尾是否正確載入
2. 確認登入狀態顯示正確
3. 測試導覽選單切換
4. 驗證響應式設計

## 🐛 常見問題

### Q: 頁首/頁尾沒有載入？
A: 檢查JavaScript是否正確載入，確認網路路徑正確。

### Q: 登入狀態不正確？
A: 檢查 `includes/session.php` 是否正常運作，確認Session設定正確。

### Q: 樣式顯示異常？
A: 確認CSS檔案路徑正確，檢查是否有CSS衝突。

### Q: 通知訊息不顯示？
A: 確認JavaScript載入完成，檢查瀏覽器控制台是否有錯誤。

## 📞 技術支援

如有問題，請檢查：
1. 瀏覽器開發者工具的控制台錯誤
2. 網路請求是否成功
3. 檔案路徑是否正確
4. Session狀態是否正常

---

**威宇影城售票系統** - 統一樣式系統 v1.0
