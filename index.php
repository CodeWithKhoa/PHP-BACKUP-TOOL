<?php
/**
 * Script sao lưu toàn bộ website (Mã nguồn & Cơ sở dữ liệu)
 *
 * @author: Tran Dang Khoa
 * @version: 2.8 (Includes the script itself in the backup)
 * @date: 2025-08-15
 */

// --- PHẦN 1: KHỞI TẠO MÔI TRƯỜNG ---

// Thiết lập múi giờ mặc định là của Việt Nam để các hàm thời gian hoạt động chính xác.
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Báo cáo tất cả các loại lỗi PHP để dễ dàng gỡ lỗi.
error_reporting(E_ALL);

// Hiển thị lỗi ra màn hình (nên tắt ở môi trường production để bảo mật).
ini_set('display_errors', 1);

// Tăng thời gian tối đa cho phép script chạy, tránh bị ngắt giữa chừng.
set_time_limit(600); // 10 phút

// Tăng giới hạn bộ nhớ RAM mà script được phép sử dụng.
ini_set('memory_limit', '512M'); // 512 Megabytes

/* =================== PHẦN 2: CẤU HÌNH (USER CONFIGURATION) =================== */
// Đây là khu vực duy nhất bạn cần chỉnh sửa.

// Chế độ Gỡ lỗi (Debug Mode): 'true' để in ra tiến trình, 'false' để chạy im lặng.
define("DEBUG_MODE", true);

// --- Cấu hình kết nối Cơ sở dữ liệu (CSDL) ---
define("DB_USER", 'root');          // Tên người dùng CSDL
define("DB_PASSWORD", '');           // Mật khẩu CSDL
define("DB_NAME", 'vtechon');     // Tên CSDL cần sao lưu
define("DB_HOST", 'localhost');       // Host của CSDL
define("DB_CHARSET", 'utf8mb4');    // Bảng mã ký tự

// --- Cấu hình Sao lưu ---
define("OUTPUT_DIR", 'backups'); // Tên thư mục để lưu các file sao lưu.
define("WEBSITE_ROOT_DIR", $_SERVER['DOCUMENT_ROOT']); // Đường dẫn đến thư mục gốc của website.

/* =================== KẾT THÚC CẤU HÌNH =================== */


// --- PHẦN 3: HÀM HỖ TRỢ & KHỐI THỰC THI CHÍNH ---

/**
 * Hàm ghi log có điều kiện, chỉ in ra nếu DEBUG_MODE là true.
 * @param string $message Thông điệp cần hiển thị.
 */
function log_message($message) {
    if (DEBUG_MODE) {
        echo $message . (PHP_SAPI === 'cli' ? "\n" : "<br>");
    }
}

// Kiểm tra yêu cầu hệ thống: Cần phải có extension 'zip' của PHP.
if (!class_exists('ZipArchive')) {
    die("Lỗi: Extension 'ZipArchive' là bắt buộc. Vui lòng cài đặt hoặc kích hoạt nó trong php.ini.");
}

// Khối thực thi chính: Sử dụng try...catch để bắt lỗi một cách an toàn.
try {
    $backupManager = new FullBackupManager(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_CHARSET);
    $backupManager->performFullBackup(WEBSITE_ROOT_DIR, OUTPUT_DIR);
} catch (Exception $e) {
    die("Đã xảy ra lỗi nghiêm trọng: " . $e->getMessage());
}


/**
 * --- PHẦN 4: ĐỊNH NGHĨA CLASS `FullBackupManager` ---
 * Class này đóng gói tất cả logic liên quan đến việc sao lưu.
 */
class FullBackupManager {
    private $conn;       // Lưu đối tượng kết nối MySQLi.
    private $dbName;     // Lưu tên cơ sở dữ liệu.
    private $scriptPath; // Lưu đường dẫn đến chính file script này.

    /**
     * Hàm khởi tạo (Constructor).
     */
    public function __construct($host, $username, $passwd, $dbName, $charset = 'utf8') {
        $this->dbName = $dbName;
        $this->scriptPath = __FILE__;
        $this->connectDatabase($host, $username, $passwd, $charset);
    }

    /**
     * Hàm hủy (Destructor).
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Phương thức kết nối đến cơ sở dữ liệu.
     */
    protected function connectDatabase($host, $username, $passwd, $charset) {
        $this->conn = new mysqli($host, $username, $passwd, $this->dbName);
        if ($this->conn->connect_error) {
            throw new Exception("Lỗi kết nối MySQL: " . $this->conn->connect_error);
        }
        if (!$this->conn->set_charset($charset)) {
            throw new Exception("Lỗi khi cài đặt bộ ký tự " . $charset . ": " . $this->conn->error);
        }
    }

    /**
     * Phương thức chính, điều phối toàn bộ quy trình làm việc.
     */
    public function performFullBackup($websiteRootDir, $backupDir) {
        log_message("<h1>Bắt đầu quá trình sao lưu toàn bộ hệ thống...</h1>");
        
        // Bước 1: Tạo thư mục sao lưu nếu chưa tồn tại.
        if (!is_dir($backupDir)) {
            log_message("Tạo thư mục sao lưu: '$backupDir'...");
            if (!mkdir($backupDir, 0755, true)) {
                throw new Exception("Không thể tạo thư mục sao lưu '$backupDir'. Vui lòng kiểm tra quyền ghi.");
            }
        }
        
        // Bước 2: Xuất CSDL ra file .sql.
        log_message("Đang xuất cơ sở dữ liệu '$this->dbName'...");
        $sqlContent = $this->backupDatabaseTables();
        $sqlFilename = 'database-backup-' . $this->dbName . '-' . date("Ymd-His") . '.sql';
        $sqlFilePath = $backupDir . DIRECTORY_SEPARATOR . $sqlFilename;
        if (file_put_contents($sqlFilePath, $sqlContent) === false) {
            throw new Exception("Không thể ghi file SQL vào '$sqlFilePath'.");
        }
        log_message("=> Xuất cơ sở dữ liệu thành công: '$sqlFilename'");

        // Bước 3: Nén mã nguồn và file SQL vào file .zip.
        log_message("Đang nén mã nguồn và CSDL vào file ZIP...");
        $zipFilename = 'full-backup-' . date("Ymd-His") . '.zip';
        $zipFilePath = $backupDir . DIRECTORY_SEPARATOR . $zipFilename;
        $this->createZipArchive($websiteRootDir, $sqlFilePath, $zipFilePath, $backupDir);
        log_message("=> Nén file thành công: '$zipFilename'");

        // Bước 4: Dọn dẹp file .sql tạm.
        log_message("Đang dọn dẹp file SQL tạm thời...");
        unlink($sqlFilePath);

        log_message("<h2>Hoàn tất sao lưu!</h2>");
        log_message("Bản sao lưu mới đã được lưu tại: <strong>" . realpath($zipFilePath) . "</strong>");

        // Bước 5: Dọn dẹp các bản sao lưu cũ.
        log_message("<hr><h2>Bắt đầu dọn dẹp các bản sao lưu cũ...</h2>");
        $this->cleanupOldBackups($backupDir);
        log_message("<strong>Dọn dẹp hoàn tất!</strong>");
    }

    /**
     * Phương thức dọn dẹp các bản sao lưu cũ (Backup Rotation).
     */
    private function cleanupOldBackups($backupDir) {
        log_message("Đang quét thư mục: '$backupDir'");
        $files = scandir($backupDir);
        $backupsByDay = [];

        // Quét và nhóm các file backup hợp lệ theo từng ngày.
        foreach ($files as $file) {
            if (preg_match('/^full-backup-(\d{8})-\d{6}\.zip$/', $file, $matches)) {
                $date = $matches[1];
                $backupsByDay[$date][] = $file;
            }
        }

        if (empty($backupsByDay)) {
            log_message("Không tìm thấy file sao lưu nào để dọn dẹp.");
            return;
        }

        // Sắp xếp file trong mỗi ngày theo thứ tự thời gian.
        foreach ($backupsByDay as &$fileList) {
            sort($fileList);
        }

        // Chuyển đổi ngày sang dạng timestamp để so sánh chính xác.
        $todayTimestamp = strtotime('today midnight');
        $yesterdayTimestamp = strtotime('yesterday midnight');

        foreach ($backupsByDay as $dateString => $fileList) {
            $fileDateTimestamp = strtotime($dateString);
            
            $fullPathFiles = array_map(function($f) use ($backupDir) {
                return $backupDir . DIRECTORY_SEPARATOR . $f;
            }, $fileList);

            log_message("<strong>Kiểm tra ngày: " . date('d-m-Y', $fileDateTimestamp) . "</strong> (" . count($fullPathFiles) . " files)");

            // Quy tắc 1: Nếu là ngày hôm nay, giữ lại tất cả.
            if ($fileDateTimestamp === $todayTimestamp) {
                log_message("-> Hôm nay: Giữ lại tất cả.");
                continue;
            }
            
            // Quy tắc 2: Nếu là ngày hôm qua.
            if ($fileDateTimestamp === $yesterdayTimestamp) {
                if (count($fullPathFiles) > 2) {
                    $firstFile = reset($fullPathFiles);
                    $lastFile = end($fullPathFiles);
                    log_message("-> Hôm qua: Giữ lại file đầu tiên (" . basename($firstFile) . ") và cuối cùng (" . basename($lastFile) . ").");
                    $filesToDelete = array_slice($fullPathFiles, 1, -1);
                    foreach ($filesToDelete as $fileToDelete) {
                        log_message("   - Xóa file: " . basename($fileToDelete));
                        unlink($fileToDelete);
                    }
                } else {
                    log_message("-> Hôm qua: Có 2 file hoặc ít hơn, giữ lại tất cả.");
                }
                continue;
            }

            // Quy tắc 3: Nếu là các ngày cũ hơn hôm qua.
            if ($fileDateTimestamp < $yesterdayTimestamp) {
                 if (count($fullPathFiles) > 1) {
                    $lastFile = end($fullPathFiles);
                    log_message("-> Ngày cũ: Chỉ giữ lại file cuối cùng (" . basename($lastFile) . ").");
                    $filesToDelete = array_slice($fullPathFiles, 0, -1);
                    foreach ($filesToDelete as $fileToDelete) {
                        log_message("   - Xóa file: " . basename($fileToDelete));
                        unlink($fileToDelete);
                    }
                } else {
                     log_message("-> Ngày cũ: Có 1 file, giữ lại.");
                }
            }
        }
    }

    /**
     * Phương thức tạo chuỗi dump SQL từ CSDL.
     */
    private function backupDatabaseTables() {
        $sql = "-- SQL Dump\n";
        $sql .= "-- Generated by Tran Dang Khoa's Backup Script\n";
        $sql .= "-- Generation Time: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";
        $tables = [];
        $result = $this->conn->query('SHOW TABLES');
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        $result->free_result();
        if (empty($tables)) {
            log_message("Cảnh báo: Không tìm thấy bảng nào trong cơ sở dữ liệu '$this->dbName'.");
            return $sql;
        }
        foreach ($tables as $table) {
            $result = $this->conn->query('SHOW CREATE TABLE `' . $table . '`');
            $row = $result->fetch_assoc();
            $sql .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\n";
            $sql .= $row['Create Table'] . ";\n\n";
            $result->free_result();
            $sql .= "-- Dumping data for table `$table`\n\n";
            $result = $this->conn->query('SELECT * FROM `' . $table . '`');
            $numFields = $result->field_count;
            while ($row = $result->fetch_row()) {
                $sql .= 'INSERT INTO `' . $table . '` VALUES(';
                for ($j = 0; $j < $numFields; $j++) {
                    if (isset($row[$j])) {
                        $sql .= '"' . $this->conn->real_escape_string($row[$j]) . '"';
                    } else {
                        $sql .= 'NULL';
                    }
                    if ($j < ($numFields - 1)) {
                        $sql .= ',';
                    }
                }
                $sql .= ");\n";
            }
            $result->free_result();
        }
        return $sql;
    }

    /**
     * Phương thức nén file vào một file ZIP.
     */
    private function createZipArchive($sourceDir, $sqlFilePath, $zipFilePath, $backupDirName) {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Không thể mở hoặc tạo file ZIP tại '$zipFilePath'.");
        }
        $zip->addFile($sqlFilePath, basename($sqlFilePath));
        $sourceDir = realpath($sourceDir);
        // Duyệt qua tất cả các FILE trong thư mục và thư mục con.
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            $filePath = $file->getRealPath();

            // --- LOGIC LOẠI TRỪ FILE ---

            // THEO YÊU CẦU: Dòng lệnh `if` kiểm tra và bỏ qua file script backup đã bị xóa,
            // do đó file `backup.php` này SẼ được đưa vào file zip.
            
            // Chỉ kiểm tra và bỏ qua các file .zip nằm TRONG thư mục backups
            // để tránh backup lồng nhau.
            $isInBackupDir = strpos($filePath, realpath($backupDirName)) === 0;
            $isZipFile = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'zip';

            if ($isInBackupDir && $isZipFile) {
                continue; // Nếu là file .zip trong thư mục backups thì bỏ qua.
            }
            
            // --- KẾT THÚC LOGIC LOẠI TRỪ ---

            // Lấy đường dẫn tương đối để giữ đúng cấu trúc thư mục.
            $relativePathSubstring = substr($filePath, strlen($sourceDir) + 1);
            // Sửa lỗi tương thích: Luôn dùng dấu '/' cho đường dẫn trong file zip.
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePathSubstring);
            
            // Thêm file vào zip.
            $zip->addFile($filePath, $relativePath);
        }
        log_message("Tổng số file đã được nén (không bao gồm CSDL): " . $zip->numFiles);
        $zip->close();
    }
}