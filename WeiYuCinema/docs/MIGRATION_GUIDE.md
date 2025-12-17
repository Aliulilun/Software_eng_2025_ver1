# 🔄 專案架構重組遷移指南

## 概述

本專案已經重新組織架構，將檔案按功能模組化，並實現 PHP 和 HTML 的分離。

---

## ✅ 已完成的重構

### 1. 資料夾結構建立
- ✅ `config/` - 設定檔
- ✅ `includes/` - 共用 PHP 檔案
- ✅ `auth/` - 登入子系統
- ✅ `auth/templates/` - 登入子系統 HTML 模板
- ✅ `member/browse/` - Browse 子系統
- ✅ `member/browse/templates/` - Browse 子系統 HTML 模板
- ✅ `member/booking/`, `inquiry/`, `topup/`, `profile/` - 其他子系統資料夾
- ✅ `docs/` - 文件目錄

### 2. 已重構的檔案

#### 設定與共用檔案
- ✅ `config/db_connect.php` - 資料庫設定
- ✅ `includes/check_login.php` - 登入檢查
- ✅ `includes/session.php` - Session API

#### 登入子系統
- ✅ `auth/login.php` + `auth/templates/login.html` - 登入頁面（已分離）
- ✅ `auth/login_process.php` - 登入處理
- ✅ `auth/logout.php` - 登出功能

#### 會員功能
- ✅ `member/index.php` - 會員首頁（已更新路徑）
- ✅ `member/browse/movies.php` + `templates/movies.html` - 電影查詢（已分離）

#### 系統入口
- ✅ `index.php` - 系統首頁（已更新）

---

## 🔄 新舊路徑對照

### 訪問路徑變更

| 功能 | 舊路徑 | 新路徑 |
|-----|-------|-------|
| 系統首頁 | `/WeiYuCinema/index.php` | `/WeiYuCinema/index.php`（已更新） |
| 登入頁面 | `/WeiYuCinema/login.php` | `/WeiYuCinema/auth/login.php` |
| 登出功能 | `/WeiYuCinema/logout.php` | `/WeiYuCinema/auth/logout.php` |
| 會員首頁 | `/WeiYuCinema/member/index.php` | `/WeiYuCinema/member/index.php`（路徑不變，內容已更新） |
| 電影查詢 | `/WeiYuCinema/member/browse_movies.php` | `/WeiYuCinema/member/browse/movies.php` |
| 影城查詢 | `/WeiYuCinema/member/browse_cinemas.php` | `/WeiYuCinema/member/browse/cinemas.php` |
| 場次查詢 | `/WeiYuCinema/member/browse_showings.php` | `/WeiYuCinema/member/browse/showings.php` |

### 檔案引用路徑變更

#### 從 auth/login.php 引用
```php
// 舊
require_once 'db_connect.php';

// 新
require_once '../config/db_connect.php';
```

#### 從 member/browse/movies.php 引用
```php
// 舊
require_once '../db_connect.php';
require_once 'check_login.php';

// 新
require_once '../../config/db_connect.php';
require_once '../../includes/check_login.php';
```

---

## 🚀 如何使用新架構

### Step 1: 測試新架構

1. 訪問系統首頁：
```
http://localhost/WeiYuCinema/index.php
```

2. 點擊「會員登入」，會導向：
```
http://localhost/WeiYuCinema/auth/login.php
```

3. 使用測試帳號登入：
   - Email: `ming123@gmail.com`
   - Password: `pw1234`

4. 登入後會自動導向會員首頁：
```
http://localhost/WeiYuCinema/member/index.php
```

5. 測試電影查詢：
```
http://localhost/WeiYuCinema/member/browse/movies.php
```

### Step 2: 確認功能運作

✅ **應該正常運作的功能：**
- 系統首頁顯示
- 登入功能（auth/login.php）
- 登入後跳轉（根據角色）
- 會員首頁顯示
- 電影查詢功能（member/browse/movies.php）
- 登出功能

⏳ **待更新的功能：**
- 註冊頁面（仍在舊位置）
- 忘記密碼（仍在舊位置）
- 影城查詢（仍在舊位置）
- 場次查詢（仍在舊位置）
- 其他詳細頁面（仍在舊位置）

---

## 📝 完成剩餘重構的步驟

### 階段 1：完成 auth/ 重構

需要更新的檔案：
1. `auth/register.php` - 創建 PHP 控制層
2. `auth/templates/register.html` - 創建 HTML 模板
3. `auth/register_process.php` - 更新路徑引用
4. `auth/forgot_password.php` - 創建 PHP 控制層
5. `auth/templates/forgot_password.html` - 創建 HTML 模板
6. `auth/reset_password.php` - 更新路徑引用

### 階段 2：完成 member/browse/ 重構

需要更新的檔案：
1. `member/browse/cinemas.php` + `templates/cinemas.html`
2. `member/browse/showings.php` + `templates/showings.html`
3. `member/browse/movie_detail.php` + `templates/movie_detail.html`
4. `member/browse/cinema_detail.php` + `templates/cinema_detail.html`

### 階段 3：開發其他子系統

1. member/booking/ - 購票功能
2. member/inquiry/ - 訂單查詢
3. member/topup/ - 儲值功能
4. member/profile/ - 會員資料

---

## ⚠️ 注意事項

### 1. 舊檔案保留
- 舊檔案仍保留在原位置
- 新檔案在新位置建立
- 待全部測試完成後再刪除舊檔案

### 2. 路徑問題
- 所有絕對路徑使用 `/WeiYuCinema/` 開頭
- 相對路徑需注意層級（`../` 或 `../../`）

### 3. 資料庫路徑
所有檔案引用資料庫設定時，注意路徑：
```php
require_once '../config/db_connect.php';      // 一層
require_once '../../config/db_connect.php';   // 兩層
```

### 4. Session 檢查路徑
所有會員頁面引用登入檢查時，注意路徑：
```php
require_once '../includes/check_login.php';      // member/ 下的檔案
require_once '../../includes/check_login.php';   // member/browse/ 下的檔案
```

---

## 🔍 檔案分離範例

### 範例 1：login.php

**PHP 控制層（auth/login.php）：**
```php
<?php
session_start();

if (isset($_SESSION['memberId'])) {
    header("Location: /WeiYuCinema/member/index.php");
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$errorMessage = '';

switch ($error) {
    case 'invalid':
        $errorMessage = '帳號或密碼錯誤';
        break;
    // ... 其他錯誤處理
}

// 載入 HTML 模板
include 'templates/login.html';
?>
```

**HTML 模板（auth/templates/login.html）：**
```html
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>會員登入</title>
</head>
<body>
    <h1>會員登入</h1>
    
    <?php if ($errorMessage): ?>
        <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
    
    <form action="login_process.php" method="POST">
        <!-- 表單內容 -->
    </form>
</body>
</html>
```

---

## 📊 專案狀態總覽

### 完成度

| 模組 | 舊架構 | 新架構 | 完成度 |
|-----|-------|-------|-------|
| 設定檔 | ✅ | ✅ | 100% |
| 共用檔案 | ✅ | ✅ | 100% |
| 登入 | ✅ | ✅ | 100% |
| 註冊 | ✅ | ⏳ | 0% |
| 忘記密碼 | ✅ | ⏳ | 0% |
| 登出 | ✅ | ✅ | 100% |
| 會員首頁 | ✅ | ✅ | 100% |
| 電影查詢 | ✅ | ✅ | 100% |
| 影城查詢 | ✅ | ⏳ | 0% |
| 場次查詢 | ✅ | ⏳ | 0% |
| 電影詳情 | ✅ | ⏳ | 0% |
| 影城詳情 | ✅ | ⏳ | 0% |

**整體完成度：** 約 40%

---

## 🎯 建議的開發順序

### 優先級 1：完成核心功能重構
1. auth/register.php（註冊功能）
2. auth/forgot_password.php（忘記密碼）
3. member/browse/ 剩餘檔案

### 優先級 2：測試核心功能
1. 完整測試登入、註冊、登出流程
2. 完整測試 Browse 功能
3. 修復發現的 Bug

### 優先級 3：開發新功能
1. member/booking/（購票）
2. member/inquiry/（訂單查詢）
3. member/topup/（儲值）
4. member/profile/（會員資料）

### 優先級 4：清理舊檔案
1. 確認新架構完全運作
2. 刪除舊位置的檔案
3. 更新所有文件

---

## 💡 開發提示

### 1. 建立新功能時
```bash
# 建立資料夾
mkdir -p member/new_module/templates

# 建立 PHP 檔案
touch member/new_module/index.php
touch member/new_module/process.php

# 建立 HTML 模板
touch member/new_module/templates/index.html
```

### 2. PHP 檔案模板
```php
<?php
require_once '../../includes/check_login.php';
require_once '../../config/db_connect.php';

// 處理業務邏輯
$data = mysqli_query($conn, "SELECT ...");

// 載入模板
include 'templates/index.html';

mysqli_close($conn);
?>
```

### 3. HTML 模板模板
```html
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>頁面標題</title>
</head>
<body>
    <h1>頁面標題</h1>
    
    <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <p><?php echo htmlspecialchars($row['field']); ?></p>
    <?php endwhile; ?>
</body>
</html>
```

---

## 📞 問題回報

如果在使用新架構時遇到問題：

1. **檢查檔案路徑**：確認 require_once 路徑正確
2. **檢查 Session**：確認 Session 正常啟動
3. **查看錯誤日誌**：檢查 PHP 錯誤訊息
4. **參考文件**：查看 `docs/PROJECT_STRUCTURE.md`

---

**版本：** 1.0  
**更新日期：** 2025-12-04  
**架構狀態：** 🔄 重組中（約 40% 完成）  
**開發者：** 威宇影城開發團隊

