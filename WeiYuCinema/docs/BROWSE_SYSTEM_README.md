# 威宇影城 Browse (Br) 相關資訊查詢子系統 使用說明

## 📁 檔案結構

已建立的檔案：

```
WeiYuCinema/
├── member/                          # 會員頁面子系統
│   ├── check_login.php             # 登入狀態檢查中介層
│   ├── index.php                   # 會員首頁（含導覽列）
│   │
│   ├── browse_movies.php           # Br1 - 電影資訊查詢
│   ├── browse_cinemas.php          # Br2 - 影城資訊查詢
│   ├── browse_showings.php         # Br3 - 場次查詢
│   ├── movie_detail.php            # 電影詳細資訊頁
│   ├── cinema_detail.php           # 影城詳細資訊頁
│   │
│   ├── booking.php                 # B - 購票服務（佔位）
│   ├── inquiry.php                 # In - 訂票紀錄查詢（佔位）
│   ├── topup.php                   # T - 儲值卡管理（佔位）
│   └── profile.php                 # M - 會員資料管理（佔位）
│
├── images/                          # 圖片資料夾
│   ├── movies/                     # 電影海報
│   └── cinemas/                    # 影城照片
│
└── login_process.php               # 已修改：根據角色跳轉
```

## 🎯 功能說明

### 1. 登入跳轉邏輯 (已修改)

**檔案：** `login_process.php`

登入成功後會根據會員角色跳轉：
- **管理者 (role_id = 1)** → `admin/index.php`
- **一般會員 (role_id = 0)** → `member/index.php`

```php
if ($member['role_id'] == 1) {
    header("Location: admin/index.php");  // 管理者
} else {
    header("Location: member/index.php"); // 一般會員
}
```

---

### 2. 登入狀態檢查 (check_login.php)

所有會員頁面都會引入此檔案，確保：
- ✅ 使用者已登入
- ✅ 使用者是一般會員（非管理者）
- ✅ 提供會員資訊變數（$memberId, $memberName, $roleId）

---

### 3. 會員首頁 (member/index.php)

**功能：**
- 顯示會員資訊
- 統一的導覽列（所有頁面共用）
- 快速功能連結

**導覽列選項：**
1. 相關資訊查詢 (Browse)
2. 購票服務 (Booking)
3. 訂票紀錄查詢 (Inquiry)
4. 儲值卡 (Top Up)
5. 更改會員資料 (Member)
6. 登出

---

### 4. Browse (Br) 相關資訊查詢

#### Br1 - 電影資訊查詢 (browse_movies.php)

**功能：**
- ✅ 列出所有電影
- ✅ 關鍵字搜尋（電影名稱、導演、演員）
- ✅ 分級篩選（普遍級、保護級、輔12、輔15、限制級）
- ✅ 類型篩選（動作、愛情、動畫、劇情、驚悚）
- ✅ 顯示電影海報（如有）
- ✅ 連結到電影詳細資訊
- ✅ 連結到場次查詢
- ✅ 連結到購票功能

**查詢語句特點：**
- 使用 LEFT JOIN 關聯 grade 和 movieType
- 使用 prepared statement 防止 SQL Injection
- 支援多條件組合查詢

---

#### Br2 - 影城資訊查詢 (browse_cinemas.php)

**功能：**
- ✅ 列出所有影城
- ✅ 關鍵字搜尋（影城名稱、地址）
- ✅ 顯示影城照片（如有）
- ✅ 顯示地址、電話、交通資訊
- ✅ 連結到影城詳細資訊
- ✅ 連結到場次查詢
- ✅ 連結到購票功能

**顯示資訊：**
- 影城名稱
- 地址
- 電話
- 交通資訊

---

#### Br3 - 場次查詢 (browse_showings.php)

**功能：**
- ✅ 列出所有場次
- ✅ 電影篩選（下拉選單）
- ✅ 影城篩選（下拉選單）
- ✅ 日期篩選（日期選擇器）
- ✅ 版本篩選（2D/3D/IMAX/Dolby Atmos）
- ✅ 連結到電影詳細資訊
- ✅ 連結到購票功能

**查詢特點：**
- 多表 JOIN（showing, movie, theater, cinema, playVersion）
- 支援多條件組合篩選
- 按日期和時間排序

**顯示資訊：**
- 場次編號
- 電影名稱
- 影城
- 影廳
- 版本
- 放映日期
- 開始時間
- 片長

---

#### 電影詳細資訊頁 (movie_detail.php)

**功能：**
- ✅ 顯示電影海報
- ✅ 顯示完整電影資訊（名稱、片長、分級、類型、導演、主演、上映日期）
- ✅ 顯示電影介紹
- ✅ 列出此電影的所有場次
- ✅ 連結到購票功能

**URL 參數：** `?id={movieId}`

---

#### 影城詳細資訊頁 (cinema_detail.php)

**功能：**
- ✅ 顯示影城照片
- ✅ 顯示完整影城資訊（名稱、地址、電話、交通資訊）
- ✅ 顯示影城介紹
- ✅ 列出此影城的所有影廳
- ✅ 列出近期場次（最多 20 筆）
- ✅ Google 地圖連結
- ✅ 連結到購票功能

**URL 參數：** `?id={cinemaId}`

---

## 🔍 資料庫查詢說明

### 電影查詢（含搜尋和篩選）

```sql
SELECT m.*, g.gradeName, mt.movieTypeName 
FROM movie m
LEFT JOIN grade g ON m.gradeId = g.gradeId
LEFT JOIN movieType mt ON m.movieTypeId = mt.movieTypeId
WHERE (m.movieName LIKE ? OR m.director LIKE ? OR m.actors LIKE ?)
  AND m.gradeId = ?
  AND m.movieTypeId = ?
ORDER BY m.movieStart DESC
```

### 影城查詢

```sql
SELECT * FROM cinema 
WHERE (cinemaName LIKE ? OR cinemaAddress LIKE ?)
ORDER BY cinemaId
```

### 場次查詢（多條件篩選）

```sql
SELECT s.*, m.movieName, m.movieTime, c.cinemaName, t.theaterName, v.versionName
FROM showing s
LEFT JOIN movie m ON s.movieId = m.movieId
LEFT JOIN theater t ON s.theaterId = t.theaterId
LEFT JOIN cinema c ON t.cinemaId = c.cinemaId
LEFT JOIN playVersion v ON s.versionId = v.versionId
WHERE s.movieId = ?
  AND c.cinemaId = ?
  AND s.showingDate = ?
  AND s.versionId = ?
ORDER BY s.showingDate, s.startTime
```

---

## 🎨 使用者介面特色

### 統一的導覽列

所有會員頁面都包含相同的導覽列：

```
歡迎，{會員姓名}！| 會員首頁 | 瀏覽電影 | 瀏覽影城 | 查詢場次 | 
購票服務 | 訂票紀錄 | 儲值卡 | 會員資料 | 登出
```

### 搜尋與篩選表單

- 關鍵字輸入框
- 下拉選單（分級、類型、影城、版本等）
- 日期選擇器
- 搜尋按鈕 + 清除條件按鈕

### 資料表格顯示

- 使用 HTML table 元素
- border="1" 顯示表格框線
- cellpadding 和 cellspacing 控制間距
- 操作欄位提供多個連結

---

## 🧪 測試流程

### 1. 測試登入跳轉

1. 使用一般會員帳號登入：`ming123@gmail.com` / `pw1234`
2. 確認跳轉到 `member/index.php`
3. 登出後使用管理者帳號登入：`admin@weiyucinema.com` / `admin`
4. 確認跳轉到 `admin/index.php`（目前會顯示 404，因為未建立）

### 2. 測試電影查詢

1. 訪問 `member/browse_movies.php`
2. 測試關鍵字搜尋（例如：「沙丘」）
3. 測試分級篩選（例如：限制級）
4. 測試類型篩選（例如：動畫）
5. 測試組合條件查詢
6. 點擊「詳細資訊」查看電影詳情
7. 點擊「查看場次」查看此電影的場次

### 3. 測試影城查詢

1. 訪問 `member/browse_cinemas.php`
2. 測試關鍵字搜尋（例如：「台北」）
3. 點擊「詳細資訊」查看影城詳情
4. 點擊「查看場次」查看此影城的場次

### 4. 測試場次查詢

1. 訪問 `member/browse_showings.php`
2. 測試電影篩選
3. 測試影城篩選
4. 測試日期篩選（例如：2025-12-03）
5. 測試版本篩選（例如：IMAX）
6. 測試組合條件查詢

### 5. 測試詳細資訊頁

1. 從電影列表進入電影詳細資訊頁
2. 確認顯示完整資訊
3. 確認顯示場次列表
4. 從影城列表進入影城詳細資訊頁
5. 確認顯示影廳列表
6. 確認顯示近期場次

---

## 📌 圖片資料夾說明

### 電影海報

**路徑：** `images/movies/`

**檔案命名：** 與資料庫 `movie.movieImg` 欄位對應

**範例：**
- `dune2.jpg` - 沙丘：第二部
- `coco.jpg` - 可可夜總會
- `onepiece.jpg` - 航海王：紅髮歌姬

### 影城照片

**路徑：** `images/cinemas/`

**檔案命名：** 與資料庫 `cinema.cinemaImg` 欄位對應

**範例：**
- `xinyi.jpg` - 台北信義威秀
- `banqiao.jpg` - 板橋大遠百威秀
- `linkou.jpg` - 林口威秀

**注意：** 目前這些圖片檔案尚未實際存在，頁面會顯示「無圖片」的佔位區塊。

---

## 🔒 安全性機制

### 1. 登入狀態檢查

所有會員頁面都會檢查：
- 是否已登入
- 是否為一般會員身份

### 2. SQL Injection 防護

- ✅ 所有查詢使用 `mysqli_prepare()` 和 `mysqli_stmt_bind_param()`
- ✅ 不直接拼接 SQL 語句

### 3. XSS 防護

- ✅ 所有輸出使用 `htmlspecialchars()`
- ✅ 防止惡意腳本注入

### 4. 輸入驗證

- ✅ ID 參數使用 `intval()` 轉換為整數
- ✅ 字串參數使用 `trim()` 去除空白
- ✅ 使用 `mysqli_real_escape_string()` 額外防護

---

## 📊 資料庫相關資料表

Browse 功能使用的資料表：

| 資料表 | 用途 |
|--------|------|
| `movie` | 電影基本資訊 |
| `grade` | 電影分級 |
| `movieType` | 電影類型 |
| `cinema` | 影城資訊 |
| `theater` | 影廳資訊 |
| `showing` | 場次資訊 |
| `playVersion` | 放映版本 |

---

## ⚠️ 已知問題與待改進

### 目前狀態

✅ **已完成：**
- 電影資訊查詢（含搜尋和篩選）
- 影城資訊查詢（含搜尋）
- 場次查詢（含多條件篩選）
- 電影詳細資訊頁
- 影城詳細資訊頁
- 登入狀態檢查
- 統一導覽列

⏳ **待完成：**
- 購票服務 (Booking)
- 訂票紀錄查詢 (Inquiry)
- 儲值卡管理 (Top Up)
- 會員資料管理 (Member)
- 管理者頁面
- 圖片上傳功能
- 分頁功能（當資料很多時）

### 待改進項目

1. **圖片顯示**
   - 目前圖片路徑存在但檔案不存在
   - 需要準備實際的電影海報和影城照片

2. **分頁功能**
   - 當電影或場次數量很多時，建議加入分頁
   - 每頁顯示 10-20 筆資料

3. **排序功能**
   - 加入按名稱、日期等排序選項

4. **搜尋結果數量顯示**
   - 顯示「共找到 X 筆資料」

5. **無資料處理**
   - 當搜尋無結果時，顯示更友善的訊息

---

## 🚀 下一步開發

1. **購票服務 (Booking)**
   - 選擇場次
   - 選擇座位
   - 選擇餐點
   - 確認訂單

2. **訂票紀錄查詢 (Inquiry)**
   - 查看歷史訂單
   - 訂單詳細資訊
   - 退票功能 (Refund)
   - 修改訂票 (Change)

3. **儲值卡管理 (Top Up)**
   - 查看餘額
   - 儲值功能
   - 交易紀錄

4. **會員資料管理 (Member)**
   - 修改基本資料
   - 變更密碼
   - 付款帳號管理

5. **管理者頁面**
   - 電影管理（新增、修改、刪除）
   - 場次管理
   - 訂單管理
   - 會員管理

---

## 📞 注意事項

1. **目前排版簡單** - 僅使用基本 HTML，無 CSS 美化
2. **圖片需自行準備** - 放入對應資料夾
3. **資料庫需先更新** - 執行 `update_member_table.sql`
4. **測試帳號可用** - 使用現有測試帳號登入

---

**版本：** 1.0  
**更新日期：** 2025-12-04  
**功能狀態：** ✅ Browse (Br) 功能已完成  
**開發者：** 威宇影城開發團隊

