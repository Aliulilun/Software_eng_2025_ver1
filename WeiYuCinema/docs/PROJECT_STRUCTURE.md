# 威宇影城售票系統 - 專案架構說明

## 📁 專案架構總覽

```
WeiYuCinema/                         # 專案根目錄
│
├── index.php                        # 系統入口頁面（自動導向）
├── WeiYuCinema.sql                  # 資料庫結構與測試資料
│
├── config/                          # 設定檔目錄
│   └── db_connect.php              # 資料庫連線設定
│
├── includes/                        # 共用 PHP 檔案
│   ├── check_login.php             # 會員登入狀態檢查
│   └── session.php                 # Session 狀態 API
│
├── auth/                            # 登入子系統
│   ├── login.php                   # 登入頁面（PHP 控制層）
│   ├── login_process.php           # 登入處理後端
│   ├── register.php                # 註冊頁面
│   ├── register_process.php        # 註冊處理後端
│   ├── forgot_password.php         # 忘記密碼頁面
│   ├── reset_password.php          # 密碼重設處理
│   ├── logout.php                  # 登出功能
│   └── templates/                  # HTML 模板目錄
│       ├── login.html              # 登入頁面 HTML
│       ├── register.html           # 註冊頁面 HTML
│       └── forgot_password.html    # 忘記密碼頁面 HTML
│
├── member/                          # 會員功能目錄
│   ├── index.php                   # 會員首頁
│   │
│   ├── browse/                     # Browse 相關資訊查詢子系統
│   │   ├── movies.php              # 電影資訊查詢（PHP 控制層）
│   │   ├── cinemas.php             # 影城資訊查詢
│   │   ├── showings.php            # 場次查詢
│   │   ├── movie_detail.php        # 電影詳細資訊
│   │   ├── cinema_detail.php       # 影城詳細資訊
│   │   └── templates/              # HTML 模板目錄
│   │       ├── movies.html         # 電影列表 HTML
│   │       ├── cinemas.html        # 影城列表 HTML
│   │       ├── showings.html       # 場次列表 HTML
│   │       ├── movie_detail.html   # 電影詳情 HTML
│   │       └── cinema_detail.html  # 影城詳情 HTML
│   │
│   ├── booking/                    # 購票子系統
│   │   ├── index.php               # 購票首頁
│   │   ├── select_seat.php         # 選座頁面
│   │   ├── select_meal.php         # 選餐頁面
│   │   ├── confirm.php             # 確認訂單頁面
│   │   ├── process.php             # 訂票處理後端
│   │   └── templates/              # HTML 模板目錄
│   │
│   ├── inquiry/                    # 訂票紀錄查詢子系統
│   │   ├── index.php               # 訂票紀錄列表
│   │   ├── detail.php              # 訂單詳細資訊
│   │   ├── refund.php              # 退票功能
│   │   ├── change.php              # 修改訂票
│   │   └── templates/              # HTML 模板目錄
│   │
│   ├── topup/                      # 儲值卡子系統
│   │   ├── index.php               # 儲值卡管理首頁
│   │   ├── topup_process.php       # 儲值處理後端
│   │   └── templates/              # HTML 模板目錄
│   │
│   └── profile/                    # 會員資料管理子系統
│       ├── index.php               # 個人資料頁面
│       ├── edit.php                # 修改資料頁面
│       ├── change_password.php     # 變更密碼頁面
│       ├── update_process.php      # 更新處理後端
│       └── templates/              # HTML 模板目錄
│
├── admin/                           # 管理者功能目錄（待開發）
│   ├── index.php                   # 管理者首頁
│   ├── movie_manage/               # 電影管理
│   ├── showing_manage/             # 場次管理
│   ├── order_manage/               # 訂單管理
│   └── member_manage/              # 會員管理
│
├── images/                          # 圖片資源目錄
│   ├── movies/                     # 電影海報
│   └── cinemas/                    # 影城照片
│
├── templates/                       # 共用模板（未來使用）
│   ├── header.php                  # 共用頁首
│   └── footer.php                  # 共用頁尾
│
└── docs/                            # 文件目錄
    ├── PROJECT_STRUCTURE.md        # 本檔案 - 專案架構說明
    ├── LOGIN_SYSTEM_README.md      # 登入子系統說明
    ├── BROWSE_SYSTEM_README.md     # Browse 子系統說明
    ├── BROWSE_QUICK_TEST.md        # Browse 測試指南
    └── QUICK_START.md              # 快速開始指南
```

---

## 🎯 架構設計原則

### 1. 模組化設計
- **按功能分類**：將相同功能的檔案放在同一資料夾
- **子系統獨立**：每個子系統有自己的資料夾（auth/, browse/, booking/ 等）
- **易於維護**：修改某個功能時只需關注對應資料夾

### 2. MVC 概念（部分實現）
- **Model（資料層）**：資料庫查詢在 PHP 控制層中
- **View（視圖層）**：HTML 模板檔案（templates/ 目錄）
- **Controller（控制層）**：PHP 檔案處理業務邏輯

### 3. 路徑管理
- **絕對路徑**：使用 `/WeiYuCinema/` 開頭的絕對路徑
- **相對路徑**：模板內使用相對路徑引用資源
- **集中管理**：設定檔統一放在 config/ 目錄

---

## 📂 各目錄詳細說明

### config/ - 設定檔目錄
**用途**：存放系統設定檔

**檔案**：
- `db_connect.php` - 資料庫連線設定
  - 資料庫主機、帳號、密碼
  - 連線函數

**引用方式**：
```php
require_once '../config/db_connect.php';
require_once '../../config/db_connect.php';  // 深層目錄
```

---

### includes/ - 共用 PHP 檔案
**用途**：存放被多個頁面共用的 PHP 功能

**檔案**：
- `check_login.php` - 會員登入檢查
  - 檢查 Session 是否存在
  - 檢查會員角色
  - 提供會員資訊變數

- `session.php` - Session API
  - 回傳 JSON 格式登入狀態
  - 供前端 JavaScript 使用

**引用方式**：
```php
require_once '../includes/check_login.php';
require_once '../../includes/check_login.php';  // 深層目錄
```

---

### auth/ - 登入子系統
**用途**：處理所有登入、註冊、登出相關功能

**功能模組**：

#### 1. 登入功能
- `login.php` - 顯示登入表單，處理錯誤訊息
- `login_process.php` - 驗證帳號密碼，建立 Session
- `templates/login.html` - 登入頁面 HTML 模板

#### 2. 註冊功能
- `register.php` - 顯示註冊表單
- `register_process.php` - 新增會員資料
- `templates/register.html` - 註冊頁面 HTML 模板

#### 3. 忘記密碼功能
- `forgot_password.php` - 忘記密碼表單
- `reset_password.php` - 重設密碼處理
- `templates/forgot_password.html` - 忘記密碼 HTML 模板

#### 4. 登出功能
- `logout.php` - 清除 Session，導向登入頁

**訪問路徑**：
```
http://localhost/WeiYuCinema/auth/login.php
http://localhost/WeiYuCinema/auth/register.php
http://localhost/WeiYuCinema/auth/logout.php
```

---

### member/ - 會員功能目錄
**用途**：一般會員的所有功能

**結構**：
- `index.php` - 會員首頁（導覽列統一入口）
- 各子系統資料夾（browse/, booking/, inquiry/, topup/, profile/）

---

### member/browse/ - Browse 子系統
**用途**：電影、影城、場次查詢功能

**功能模組**：

#### Br1 - 電影資訊查詢
- `movies.php` - 查詢電影、處理搜尋和篩選
- `templates/movies.html` - 電影列表 HTML 模板

**功能**：
- 關鍵字搜尋（電影名稱、導演、演員）
- 分級篩選
- 類型篩選

#### Br2 - 影城資訊查詢
- `cinemas.php` - 查詢影城
- `templates/cinemas.html` - 影城列表 HTML 模板

**功能**：
- 關鍵字搜尋（影城名稱、地址）

#### Br3 - 場次查詢
- `showings.php` - 查詢場次
- `templates/showings.html` - 場次列表 HTML 模板

**功能**：
- 電影篩選
- 影城篩選
- 日期篩選
- 版本篩選

#### 詳細資訊頁
- `movie_detail.php` - 電影詳細資訊
- `cinema_detail.php` - 影城詳細資訊
- `templates/movie_detail.html` - 電影詳情 HTML
- `templates/cinema_detail.html` - 影城詳情 HTML

**訪問路徑**：
```
http://localhost/WeiYuCinema/member/browse/movies.php
http://localhost/WeiYuCinema/member/browse/cinemas.php
http://localhost/WeiYuCinema/member/browse/showings.php
```

---

### member/booking/ - 購票子系統（待開發）
**用途**：線上訂票功能

**規劃功能**：
1. 選擇場次
2. 選擇座位
3. 選擇餐點
4. 確認訂單
5. 付款處理

---

### member/inquiry/ - 訂票紀錄子系統（待開發）
**用途**：查詢、管理訂票紀錄

**規劃功能**：
1. 訂單列表
2. 訂單詳情
3. 退票功能 (Refund)
4. 修改訂票 (Change)

---

### member/topup/ - 儲值卡子系統（待開發）
**用途**：會員儲值卡管理

**規劃功能**：
1. 查看餘額
2. 儲值功能
3. 交易紀錄

---

### member/profile/ - 會員資料子系統（待開發）
**用途**：會員個人資料管理

**規劃功能**：
1. 查看個人資料
2. 修改基本資料
3. 變更密碼
4. 付款帳號管理

---

## 🔄 檔案引用路徑規則

### 從 member/index.php 引用
```php
require_once '../includes/check_login.php';    // 往上一層
require_once '../config/db_connect.php';
```

### 從 member/browse/movies.php 引用
```php
require_once '../../includes/check_login.php';   // 往上兩層
require_once '../../config/db_connect.php';
```

### 從 auth/login.php 引用
```php
require_once '../config/db_connect.php';        // 往上一層
include 'templates/login.html';                  // 同層 templates/
```

### HTML 中的絕對路徑
```html
<a href="/WeiYuCinema/auth/login.php">登入</a>
<img src="/WeiYuCinema/images/movies/movie1.jpg">
```

---

## 📝 檔案命名規則

### PHP 控制層檔案
- 使用小寫 + 底線：`browse_movies.php` → 重構為 `movies.php`
- 放在功能資料夾中：`member/browse/movies.php`

### HTML 模板檔案
- 與 PHP 檔案同名：`movies.php` → `movies.html`
- 放在 `templates/` 子目錄：`member/browse/templates/movies.html`

### 處理後端檔案
- 加上 `_process` 後綴：`login_process.php`, `register_process.php`
- 或使用 `process.php`：`booking/process.php`

---

## 🚀 開發流程

### 新增功能模組步驟

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

// 業務邏輯處理
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
<head>
    <title>新功能</title>
</head>
<body>
    <?php while ($row = mysqli_fetch_assoc($data)): ?>
        <p><?php echo htmlspecialchars($row['field']); ?></p>
    <?php endwhile; ?>
</body>
</html>
```

4. **更新導覽列連結**
修改 `member/index.php` 加入新功能連結

---

## 🔍 檔案對應表

### 舊架構 → 新架構

| 舊檔案路徑 | 新檔案路徑 | 說明 |
|-----------|-----------|------|
| `db_connect.php` | `config/db_connect.php` | 設定檔 |
| `session.php` | `includes/session.php` | 共用檔案 |
| `login.php` | `auth/login.php` + `auth/templates/login.html` | 分離 PHP/HTML |
| `logout.php` | `auth/logout.php` | 純 PHP 無需分離 |
| `member/browse_movies.php` | `member/browse/movies.php` + `templates/movies.html` | 分離 PHP/HTML |
| `member/browse_cinemas.php` | `member/browse/cinemas.php` + `templates/cinemas.html` | 分離 PHP/HTML |
| `*.md` | `docs/*.md` | 文件統一管理 |

---

## ✅ 優點總結

### 1. 清晰的專案結構
- ✅ 按功能分類，一目了然
- ✅ 容易找到需要修改的檔案
- ✅ 新成員快速了解專案

### 2. 易於維護
- ✅ 修改某功能只需關注對應資料夾
- ✅ PHP 和 HTML 分離，職責明確
- ✅ 共用檔案集中管理

### 3. 易於擴展
- ✅ 新增功能模組很簡單
- ✅ 複製現有模組即可快速開發
- ✅ 不影響其他功能

### 4. 團隊協作友善
- ✅ 不同人負責不同資料夾
- ✅ 減少檔案衝突
- ✅ 程式碼審查更容易

---

## 📊 目前開發狀態

| 模組 | 狀態 | 說明 |
|-----|------|------|
| config/ | ✅ 完成 | 資料庫設定 |
| includes/ | ✅ 完成 | 共用功能 |
| auth/ | 🔄 部分完成 | login 已重構，其他待更新 |
| member/browse/ | 🔄 部分完成 | movies 已重構，其他待更新 |
| member/booking/ | ⏳ 待開發 | 佔位檔案已建立 |
| member/inquiry/ | ⏳ 待開發 | 佔位檔案已建立 |
| member/topup/ | ⏳ 待開發 | 佔位檔案已建立 |
| member/profile/ | ⏳ 待開發 | 佔位檔案已建立 |
| admin/ | ⏳ 待開發 | 未建立 |

---

## 🎯 下一步建議

1. **完成 auth/ 模組重構**
   - 更新 register.php 和對應模板
   - 更新 forgot_password.php 和對應模板

2. **完成 member/browse/ 模組重構**
   - 更新 cinemas.php 和對應模板
   - 更新 showings.php 和對應模板
   - 更新詳細頁面和對應模板

3. **開發其他功能模組**
   - booking/ - 購票功能
   - inquiry/ - 訂單查詢
   - topup/ - 儲值功能
   - profile/ - 會員資料

4. **建立共用模板**
   - templates/header.php - 統一頁首
   - templates/footer.php - 統一頁尾
   - templates/nav.php - 統一導覽列

---

**版本：** 2.0（重構版）  
**更新日期：** 2025-12-04  
**專案狀態：** 🔄 架構重組中  
**開發者：** 威宇影城開發團隊

