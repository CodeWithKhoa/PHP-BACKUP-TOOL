<?php
// Bắt đầu session để có thể kiểm tra trạng thái đăng nhập
session_start();

// 1. KIỂM TRA ĐĂNG NHẬP
// Nếu session 'is_logged_in' không tồn tại hoặc không phải là true, cấm truy cập ngay lập tức.
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(403); // Gửi mã lỗi 403 Forbidden
    die('<h1>Truy cap bi tu choi!</h1><p>Ban can phai dang nhap de tai file.</p>');
}

// 2. KIỂM TRA TÊN FILE YÊU CẦU
// Lấy tên file từ URL (?file=...)
$fileName = isset($_GET['file']) ? $_GET['file'] : '';

// 3. CÁC BƯỚC KIỂM TRA BẢO MẬT (RẤT QUAN TRỌNG)
// a. Ngăn chặn tấn công Path Traversal (vd: ?file=../../secret.txt) bằng cách chỉ lấy tên file.
$baseName = basename($fileName);
// b. Đảm bảo tên file sau khi lọc vẫn giống tên file gốc và không rỗng.
if (empty($fileName) || $baseName !== $fileName) {
    http_response_code(400); // Gửi mã lỗi 400 Bad Request
    die('Ten file khong hop le.');
}
// c. Đảm bảo file tồn tại trong thư mục này
if (!file_exists($baseName)) {
    http_response_code(404); // Gửi mã lỗi 404 Not Found
    die('File khong ton tai.');
}
// d. Chỉ cho phép tải file zip
if (pathinfo($baseName, PATHINFO_EXTENSION) !== 'zip') {
    http_response_code(403);
    die('Loai file khong duoc phep tai.');
}


// 4. THỰC HIỆN CHO TẢI FILE NẾU MỌI THỨ ĐỀU HỢP LỆ
$filePath = __DIR__ . DIRECTORY_SEPARATOR . $baseName;

header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $baseName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Xóa bộ đệm đầu ra để tránh làm hỏng file
ob_clean();
flush();

// Đọc và xuất nội dung file
readfile($filePath);
exit; // Dừng script sau khi tải xong