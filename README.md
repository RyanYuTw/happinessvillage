# Happiness Village 幸福村

幸福村教學手冊管理系統 — 基於 Laravel + Inertia.js + Vue 3 打造的注音教學手冊編輯與管理平台。

## 功能特色

- 📖 教學手冊 CRUD 管理
- ✏️ 富文字編輯器（含表格、繪圖、按鈕等自訂元件）
- 🎨 線上繪圖編輯器
- 📷 照片管理
- 🔤 注音符號工具
- 👤 使用者驗證與權限管理

## 技術棧

- **後端**: Laravel 11, PHP 8.2+
- **前端**: Vue 3, Inertia.js, Vite
- **樣式**: Tailwind CSS
- **資料庫**: MySQL / SQLite

## 環境需求

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL 8.0+（或 SQLite）

## 安裝步驟

### 1. Clone 專案

```bash
git clone git@github.com:RyanYuTw/happinessvillage.git
cd happinessvillage
```

### 2. 安裝依賴

```bash
composer install
npm install
```

### 3. 環境設定

```bash
cp .env.example .env
php artisan key:generate
```

編輯 `.env` 填入資料庫等設定（參見下方環境變數說明）。

### 4. 資料庫遷移

```bash
php artisan migrate
```

### 5. 啟動開發伺服器

```bash
# 終端 1 — 後端
php artisan serve

# 終端 2 — 前端（Vite）
npm run dev
```

## 環境變數說明

複製 `.env.example` 為 `.env`，主要需設定：

| 變數 | 說明 | 範例 |
|------|------|------|
| `APP_NAME` | 應用名稱 | `HappinessVillage` |
| `APP_URL` | 應用 URL | `http://localhost` |
| `DB_CONNECTION` | 資料庫類型 | `mysql` / `sqlite` |
| `DB_HOST` | 資料庫主機 | `127.0.0.1` |
| `DB_DATABASE` | 資料庫名稱 | `happinessvillage` |
| `DB_USERNAME` | 資料庫帳號 | `root` |
| `DB_PASSWORD` | 資料庫密碼 | *(你的密碼)* |
| `SEED_USER_EMAIL` | 初始用戶信箱 | *(選填，供 seed 腳本用)* |
| `SEED_USER_NAME` | 初始用戶名稱 | *(選填)* |
| `SEED_USER_PASSWORD` | 初始用戶密碼 | *(選填)* |

> ⚠️ **注意**：`.env` 檔案包含敏感資訊，**絕對不要**提交至版本控制。

## 專案結構

```
├── app/
│   └── Http/Controllers/
│       ├── HandbookController.php   # 教學手冊
│       ├── PhotoController.php      # 照片管理
│       └── ZhuyinController.php     # 注音工具
├── resources/js/
│   ├── Components/
│   │   └── Editor/                  # 富文字編輯器元件
│   └── Pages/
│       ├── Admin/                   # 後台管理頁面
│       ├── DrawingEditor.vue        # 繪圖編輯器
│       └── Welcome.vue              # 首頁
├── routes/
│   └── web.php                      # 路由定義
└── database/
    └── migrations/                  # 資料庫遷移
```

## License

本專案僅供內部使用。
