# 威宇影城會員登入子系統 使用說明

## 📁 檔案結構

已建立的檔案：

```
WeiYuCinema/
├── db_connect.php              # 資料庫連線設定檔
├── index.php                   # 首頁（含登入狀態顯示）
├── login.php                   # 登入頁面
├── login_process.php           # 登入處理後端
├── register.php                # 註冊頁面
├── register_process.php        # 註冊處理後端
├── forgot_password.php         # 忘記密碼頁面
├── reset_password.php          # 密碼重設處理後端
├── logout.php                  # 登出功能
├── session.php                 # Session 狀態 API（回傳 JSON）
├── WeiYuCinema.sql            # 更新後的資料庫結構
└── update_member_table.sql    # 資料表更新腳本
```

## 🚀 安裝步驟

### 1. 更新資料庫結構

如果您已經有舊的資料庫，請執行更新腳本：

```sql
-- 在 phpMyAdmin 或 MySQL 命令列執行
source update_member_table.sql;
```

或者重新匯入完整的資料庫：

```sql
-- 刪除舊資料庫（小心！會刪除所有資料）
DROP DATABASE IF EXISTS WeiYuCinema;

-- 建立新資料庫
CREATE DATABASE WeiYuCinema CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 匯入 SQL 檔案
USE WeiYuCinema;
source WeiYuCinema.sql;
```

### 2. 設定資料庫連線

檢查 `db_connect.php` 的連線參數：

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // 您的資料庫使用者名稱
define('DB_PASS', '');            // 您的資料庫密碼
define('DB_NAME', 'WeiYuCinema');
```

### 3. 啟動 XAMPP

- 啟動 Apache
- 啟動 MySQL

### 4. 訪問系統

在瀏覽器開啟：`http://localhost/WeiYuCinema/index.php`

## 📝 資料庫變更說明

### memberProfile 資料表變更

**移除欄位：**
- `memberPwdHintId` - 密碼提示問題 ID（已移除）
- `memberPwdHintAns` - 密碼提示答案（已移除）

**欄位說明：**
- `member` - 改為存放「電子信箱」，用於登入和密碼重設

### 測試帳號

| 電子信箱 | 密碼 | 角色 | 說明 |
|---------|------|------|------|
| admin@weiyucinema.com | admin | 管理者 | 管理員帳號 |
| ming123@gmail.com | pw1234 | 一般會員 | 測試帳號1 |
| meili88@gmail.com | abc5678 | 一般會員 | 測試帳號2 |
| wei5566@gmail.com | xyz7788 | 一般會員 | 測試帳號3 |
| yucheng77@gmail.com | pass2020 | 一般會員 | 測試帳號4 |

## 🔧 功能說明

### 1. 會員註冊 (register.php)

**功能：**
- 輸入姓名、電子信箱、密碼、手機、生日
- 自動產生會員編號（M0001, M0002...）
- 自動建立會員儲值卡（初始餘額 0）
- 驗證電子信箱格式
- 驗證手機格式（09xxxxxxxx）
- 檢查電子信箱是否重複

**驗證規則：**
- 密碼長度至少 6 個字元
- 兩次密碼輸入必須一致
- 電子信箱格式正確
- 手機號碼 10 位數字（09開頭）

### 2. 會員登入 (login.php)

**功能：**
- 使用電子信箱 + 密碼登入
- 驗證帳號密碼
- 檢查帳號是否已驗證
- 登入成功後設定 Session

**Session 變數：**
```php
$_SESSION['memberId']    // 會員編號
$_SESSION['memberName']  // 會員姓名
$_SESSION['role_id']     // 角色 ID（0=一般會員, 1=管理者）
$_SESSION['login_time']  // 登入時間
```

### 3. 忘記密碼 (forgot_password.php)

**功能：**
- 輸入註冊時的電子信箱
- 設定新密碼（需輸入兩次）
- 直接更新密碼（無需郵件驗證）

**注意：** 實際應用應該加入郵件驗證功能

### 4. 登出 (logout.php)

**功能：**
- 清除所有 Session 變數
- 刪除 Session Cookie
- 導向登入頁面

### 5. Session API (session.php)

**用途：** 供前端 JavaScript 查詢登入狀態

**回傳格式（JSON）：**
```json
{
  "isLoggedIn": true,
  "memberId": "M0001",
  "memberName": "王小明",
  "roleId": 0,
  "roleName": "一般會員",
  "loginTime": 1234567890
}
```

## 🔒 安全性注意事項

### ⚠️ 目前的安全問題（待改進）

1. **密碼未加密存儲**
   - 目前使用明文密碼
   - 建議使用 `password_hash()` 和 `password_verify()`

2. **SQL Injection 防護**
   - 已使用 `mysqli_prepare()` 和參數綁定
   - ✅ 基本防護已做

3. **XSS 防護**
   - 建議在輸出時使用 `htmlspecialchars()`
   - 前端頁面已部分使用

4. **Session 安全性**
   - 建議加入 Session timeout 機制
   - 建議加入 CSRF Token

### 建議改進（生產環境必做）

```php
// 註冊時密碼加密
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 登入時驗證密碼
if (password_verify($password, $member['memberPwd'])) {
    // 密碼正確
}

// Session Timeout（30分鐘）
if (time() - $_SESSION['login_time'] > 1800) {
    session_destroy();
    header("Location: login.php?error=timeout");
    exit();
}
```

## 🧪 測試流程

### 1. 測試註冊功能
1. 訪問 `register.php`
2. 填寫完整資料
3. 確認自動產生會員編號
4. 確認自動建立儲值卡

### 2. 測試登入功能
1. 訪問 `login.php`
2. 使用測試帳號登入
3. 確認導向首頁
4. 確認顯示會員姓名

### 3. 測試忘記密碼
1. 訪問 `forgot_password.php`
2. 輸入電子信箱
3. 設定新密碼
4. 使用新密碼登入

### 4. 測試登出功能
1. 登入後點擊「登出」
2. 確認導向登入頁面
3. 確認無法直接訪問需登入的頁面

### 5. 測試 Session API
在瀏覽器開啟：`http://localhost/WeiYuCinema/session.php`
查看 JSON 回傳結果

## 📌 下一步開發建議

1. **密碼加密** - 使用 `password_hash()`
2. **郵件驗證** - 註冊時發送驗證信
3. **忘記密碼郵件** - 發送重設密碼連結
4. **Session 管理** - 加入 timeout 和記住我功能
5. **權限控制** - 建立中介層檢查登入狀態
6. **錯誤日誌** - 記錄登入失敗等事件
7. **驗證碼** - 防止暴力破解

## 🐛 已知問題

無

## 📞 聯絡資訊

如有問題請聯繫開發團隊。

---

**版本：** 1.0  
**更新日期：** 2025-12-04  
**開發者：** 威宇影城開發團隊

