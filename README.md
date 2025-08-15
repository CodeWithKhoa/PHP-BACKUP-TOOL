# 🛠 PHP Backup Tool

## 📖 Introduction
**PHP Backup Tool** is a lightweight PHP script that allows you to back up your **MySQL database** and **entire project source code**.  
It supports exporting to **`.sql`** (database only) or **`.zip`** (database + source code), which can be **downloaded directly** or **stored automatically**.

---

## ✨ Features
- 📦 **Backup entire MySQL database**.
- 🗂 **Compress and store project folder** as `.zip`.
- ⏬ **Download backup files directly** from the browser.
- 🔒 **Protect backup folder** with `.htaccess`.

---

## 📂 Directory Structure
```
PHP-BACKUP-TOOL/
│   index.php          # Main backup interface
│   backupsql.php      # Script to back up the database
│   README.md          # Documentation
│
└── backups/           # Backup storage folder
    │   .htaccess      # Block direct access
    │   download.php   # Script to download backup files
    │   index.php      # List backup files
```

---

## 🖥 System Requirements
- **PHP** 7.0 or higher
- Web server with PHP support (Apache, Nginx, ...)
- Write permissions for `backups/` folder
- **MySQL** or **MariaDB**

---

## 🚀 Installation & Usage

### 1. Download Source Code
```bash
git clone https://github.com/CodeWithKhoa/PHP-BACKUP-TOOL.git
```

### 2. Configure Database
Open `index.php` and edit:
```php
// --- Cấu hình kết nối Cơ sở dữ liệu (CSDL) ---
define("DB_USER", '');          // Tên người dùng CSDL
define("DB_PASSWORD", '');           // Mật khẩu CSDL
define("DB_NAME", '');     // Tên CSDL cần sao lưu
define("DB_HOST", 'localhost');       // Host của CSDL
define("DB_CHARSET", 'utf8mb4');    // Bảng mã ký tự
```

### 3. Run Backup
- Open your browser and go to `index.php`.

### 4. Download Backup
- Open `backups/download.php` to download, or access the `backups/` folder.

---

## 🔐 Security
- The `backups/` folder has **`.htaccess`** to block direct access.

---

## ✍️ Author & Contact

This project was created and is maintained by **Tran Dang Khoa**.

-   **GitHub:** [@CodeWithKhoa](https://github.com/codewithkhoa)
-   **YouTube:** [@codewithkhoa](https://youtube.com/@codewithkhoa)
-   **Email:** [trandangkhoa31122006@gmail.com](mailto:trandangkhoa31122006@gmail.com)

---
## 📜 License
Released under the **MIT License** — free to use, modify, and distribute.
