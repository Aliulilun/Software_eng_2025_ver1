# 🎬 威宇影城售票系統

## 專案簡介

威宇影城售票系統是一個完整的線上訂票系統，提供電影資訊查詢、場次查詢、線上訂票、會員管理等功能。

---

## 🚀 快速開始

### 1. 環境需求
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 或以上
- MySQL 5.7 或以上

### 2. 安裝步驟

#### Step 1: 啟動 XAMPP
```
啟動 Apache
啟動 MySQL
```

#### Step 2: 匯入資料庫
```sql
-- 在 phpMyAdmin 執行
CREATE DATABASE WeiYuCinema CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE WeiYuCinema;
source WeiYuCinema.sql;
```

#### Step 3: 訪問系統
```
http://localhost/WeiYuCinema/index.php
```

### 3. 測試帳號

| 角色 | Email | 密碼 |
|-----|-------|------|
| 管理者 | admin@weiyucinema.com | admin |
| 一般會員 | ming123@gmail.com | pw1234 |

---

## 📁 專案架構

```
WeiYuCinema/
├── index.php                    # 系統入口
├── config/                      # 設定檔
├── includes/                    # 共用 PHP
├── auth/                        # 登入子系統
│   ├── *.php                   # PHP 控制層
│   └── templates/              # HTML 模板
├── member/                      # 會員功能
│   ├── browse/                 # Browse 子系統
│   ├── booking/                # 購票子系統
│   ├── inquiry/                # 訂單查詢
│   ├── topup/                  # 儲值卡
│   └── profile/                # 會員資料
├── images/                      # 圖片資源
└── docs/                        # 文件
```

**詳細架構說明：** 請查看 `docs/PROJECT_STRUCTURE.md`

---

## 📚 文件索引

| 文件 | 說明 |
|-----|------|
| `docs/PROJECT_STRUCTURE.md` | 完整專案架構說明 |
| `docs/MIGRATION_GUIDE.md` | 架構重組遷移指南 |
| `docs/LOGIN_SYSTEM_README.md` | 登入子系統說明 |
| `docs/BROWSE_SYSTEM_README.md` | Browse 子系統說明 |
| `docs/BROWSE_QUICK_TEST.md` | Browse 快速測試指南 |
| `docs/QUICK_START.md` | 快速開始指南 |

---

## ✨ 功能列表

### 已完成功能 ✅

#### 登入子系統
- [x] 會員登入
- [x] 會員註冊
- [x] 忘記密碼
- [x] 登出功能
- [x] Session 管理

#### Browse 相關資訊查詢
- [x] 電影資訊查詢（搜尋、篩選）
- [x] 影城資訊查詢（搜尋）
- [x] 場次查詢（多條件篩選）
- [x] 電影詳細資訊頁
- [x] 影城詳細資訊頁

### 開發中功能 🔄

#### 購票子系統
- [ ] 選擇場次
- [ ] 選擇座位
- [ ] 選擇餐點
- [ ] 確認訂單
- [ ] 付款處理

#### 訂單管理
- [ ] 訂票紀錄查詢
- [ ] 訂單詳情
- [ ] 退票功能
- [ ] 修改訂票

#### 會員功能
- [ ] 儲值卡管理
- [ ] 餘額查詢
- [ ] 儲值功能
- [ ] 會員資料修改
- [ ] 變更密碼

#### 管理者功能
- [ ] 電影管理
- [ ] 場次管理
- [ ] 訂單管理
- [ ] 會員管理

---

## 🛠️ 技術架構

### 前端
- HTML5
- CSS3（待加入）
- JavaScript（待加入）

### 後端
- PHP 8.x
- MySQL
- Session 管理

### 架構設計
- MVC 概念（部分實現）
- 模組化設計
- PHP/HTML 分離

---

## 📊 專案狀態

### 完成度
- **登入子系統：** ✅ 100%
- **Browse 子系統：** ✅ 100%
- **購票子系統：** ⏳ 0%
- **訂單管理：** ⏳ 0%
- **會員功能：** ⏳ 0%
- **管理者功能：** ⏳ 0%

**整體完成度：** 約 30%

---

## 🔗 訪問路徑

### 公開頁面
- 系統首頁：`http://localhost/WeiYuCinema/index.php`
- 會員登入：`http://localhost/WeiYuCinema/auth/login.php`
- 會員註冊：`http://localhost/WeiYuCinema/auth/register.php`

### 會員頁面（需登入）
- 會員首頁：`http://localhost/WeiYuCinema/member/index.php`
- 電影查詢：`http://localhost/WeiYuCinema/member/browse/movies.php`
- 影城查詢：`http://localhost/WeiYuCinema/member/browse/cinemas.php`
- 場次查詢：`http://localhost/WeiYuCinema/member/browse/showings.php`

---

## 🔧 開發指南

### 新增功能模組

1. **建立資料夾結構**
```bash
mkdir -p member/new_module/templates
```

2. **建立 PHP 控制層**
```php
// member/new_module/index.php
<?php
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 業務邏輯
$data = mysqli_query($conn, "SELECT * FROM table");

// 載入模板
include 'templates/index.html';
?>
```

3. **建立 HTML 模板**
```html
<!-- member/new_module/templates/index.html -->
<!DOCTYPE html>
<html>
<body>
    <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <p><?php echo htmlspecialchars($row['field']); ?></p>
    <?php endwhile; ?>
</body>
</html>
```

---

## 📝 路徑規則

### PHP 引用路徑
```php
// 從 member/browse/movies.php
require_once '../../includes/check_login.php';   // 往上兩層
require_once '../../config/db_connect.php';

// 從 member/index.php
require_once '../includes/check_login.php';      // 往上一層
```

### HTML 連結路徑
```html
<!-- 使用絕對路徑 -->
<a href="/WeiYuCinema/auth/login.php">登入</a>
<img src="/WeiYuCinema/images/movies/movie1.jpg">
```

---

## ⚠️ 注意事項

1. **密碼加密**：目前使用明文密碼，生產環境需改用 `password_hash()`
2. **圖片檔案**：images/ 資料夾中的圖片需自行準備
3. **Session 安全**：建議加入 Session timeout 機制
4. **CSRF 防護**：建議加入 CSRF Token
5. **XSS 防護**：所有輸出已使用 `htmlspecialchars()`

---

## 🐛 已知問題

1. **圖片顯示**：圖片檔案不存在，顯示佔位區塊（正常現象）
2. **密碼安全**：使用明文密碼（待改進）
3. **舊檔案**：舊架構檔案仍保留（待清理）

---

## 📞 開發團隊

- **專案名稱：** 威宇影城售票系統
- **開發團隊：** 長庚大學軟體工程課程
- **指導教授：** 林仲志 教授
- **開發成員：** 劉立綸、林威宇、林敬棠、姚睿、廖哲勛
- **開發時間：** 2025 年

---

## 📄 授權

本專案僅供學術研究與學習使用。

---

## 🔄 版本歷史

### v2.0（重構版）- 2025-12-04
- 重組專案架構
- 模組化設計
- PHP/HTML 分離
- 新增完整文件

### v1.0 - 2025-12-04
- 登入子系統
- Browse 子系統
- 基本功能實現

---

**最後更新：** 2025-12-04  
**專案狀態：** 🔄 開發中  
**版本：** 2.0（重構版）

