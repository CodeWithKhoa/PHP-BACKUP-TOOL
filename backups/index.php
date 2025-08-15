<?php
// --- BẮT ĐẦU CẤU HÌNH ---
define('LOGIN_USER', 'admin');
define('LOGIN_PASSWORD', 'dangkhoa2006'); // <-- THAY MẬT KHẨU MẠNH VÀO ĐÂY!
// --- KẾT THÚC CẤU HÌNH ---

session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $userMatch = hash_equals(LOGIN_USER, $_POST['username']);
        $passMatch = hash_equals(LOGIN_PASSWORD, $_POST['password']);
        if ($userMatch && $passMatch) {
            $_SESSION['is_logged_in'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error_message = 'Tài khoản hoặc mật khẩu không đúng!';
        }
    }
}
$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khu Vực Backup</title>
    <style>
        :root {
            --primary-color: #0d6efd; --secondary-color: #6c757d; --background-color: #f8f9fa;
            --surface-color: #ffffff; --text-color: #212529; --border-color: #dee2e6;
            --danger-color: #dc3545; --success-color: #198754;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--background-color); color: var(--text-color); margin: 0;
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            padding: 20px; box-sizing: border-box;
        }
        .container {
            width: 100%; max-width: 900px; background-color: var(--surface-color);
            padding: 40px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 30px;
        }
        .header h1 { color: var(--primary-color); font-size: 28px; margin: 0; }
        .logout-btn {
            display: inline-flex; align-items: center; gap: 8px; background-color: var(--danger-color);
            color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none;
            font-weight: 500; transition: background-color 0.2s;
        }
        .logout-btn:hover { background-color: #bb2d3b; }
        .file-table { width: 100%; border-collapse: collapse; }
        .file-table th, .file-table td { text-align: left; padding: 15px; border-bottom: 1px solid var(--border-color); }
        .file-table th { color: var(--secondary-color); font-weight: 600; text-transform: uppercase; font-size: 12px; }
        .file-table tr:hover { background-color: #f1f3f5; }
        .file-name { display: flex; align-items: center; gap: 10px; }
        .file-name span { word-break: break-all; }
        .download-btn {
            display: inline-flex; align-items: center; gap: 8px; background-color: var(--success-color);
            color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none;
            font-size: 14px; transition: background-color 0.2s;
        }
        .download-btn:hover { background-color: #157347; }
        .icon { width: 20px; height: 20px; } .icon-lg { width: 24px; height: 24px; }
        .no-backups { text-align: center; padding: 40px; color: var(--secondary-color); }
        .text-center { text-align: center; }
        .login-container { text-align: center; } .login-container h1 { margin-bottom: 10px; }
        .login-container p { color: var(--secondary-color); margin-bottom: 30px; }
        .login-form input {
            width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid var(--border-color);
            border-radius: 6px; box-sizing: border-box; font-size: 16px;
        }
        .login-form button {
            width: 100%; padding: 12px; background-color: var(--primary-color); color: white;
            border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold;
            transition: background-color 0.2s;
        }
        .login-form button:hover { background-color: #0b5ed7; }
        .error { color: var(--danger-color); font-weight: 500; margin-bottom: 15px; }
        @media (max-width: 768px) {
            .container { padding: 20px; }
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .file-table thead { display: none; }
            .file-table, .file-table tbody, .file-table tr, .file-table td { display: block; width: 100%; }
            .file-table tr { margin-bottom: 15px; border: 1px solid var(--border-color); border-radius: 8px; }
            .file-table td { display: flex; justify-content: space-between; padding: 10px 15px; border: none; }
            .file-table td::before { content: attr(data-label); font-weight: bold; margin-right: 10px; color: var(--secondary-color); }
            .file-table td:last-child { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($isLoggedIn): ?>
            <header class="header">
                <h1>Bảng điều khiển Backup</h1>
                <a href="?logout=true" class="logout-btn"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 192 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l210.7 0-73.4 73.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l128-128zM160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C43 32 0 75 0 128L0 384c0 53 43 96 96 96l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l64 0z"/></svg><span>Đăng xuất</span></a>
            </header>
            <table class="file-table">
                <thead><tr><th>Tên file</th><th>Kích thước</th><th>Ngày cập nhật</th><th class="text-center">Hành động</th></tr></thead>
                <tbody>
                    <?php
                    $files = scandir(__DIR__); $backupFiles = [];
                    foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'zip' && preg_match('/^full-backup-/', $file)) { $backupFiles[] = $file; }
                    }
                    usort($backupFiles, function($a, $b) { return filemtime($b) - filemtime($a); });
                    if (empty($backupFiles)) {
                        echo '<tr><td colspan="4" class="no-backups">Không tìm thấy file backup nào.</td></tr>';
                    } else {
                        foreach ($backupFiles as $file) {
                            $fileSize = filesize($file);
                            $formattedSize = round($fileSize / 1024 / 1024, 2) . ' MB';
                            $fileDate = date("d/m/Y H:i:s", filemtime($file));
                            echo '<tr>';
                            echo '<td data-label="Tên file"><div class="file-name"><svg class="icon-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="#6c757d"><path d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM112 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg><span>' . htmlspecialchars($file) . '</span></div></td>';
                            echo '<td data-label="Kích thước">' . $formattedSize . '</td>';
                            echo '<td data-label="Ngày Cập Nhật">' . $fileDate . '</td>';
                            // ===== DÒNG QUAN TRỌNG ĐÃ THAY ĐỔI =====
                            echo '<td data-label="Hành động" class="text-center"><a href="download.php?file=' . urlencode($file) . '" class="download-btn"><svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56c13.3 0 24 10.7 24 24s-10.7 24-24 24H64c-13.3 0-24-10.7-24-24s10.7-24 24-24H432z"/></svg><span>Tải về</span></a></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="login-container">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 50px; height: 50px; fill: var(--primary-color); margin-bottom: 20px;"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
                <h1>Truy Cập An Toàn</h1><p>Vui lòng nhập thông tin đăng nhập để tiếp tục.</p>
                <div class="login-form">
                    <?php if (isset($error_message)): ?><p class="error"><?php echo $error_message; ?></p><?php endif; ?>
                    <form method="POST" action="index.php">
                        <input type="text" name="username" placeholder="Tên đăng nhập" required>
                        <input type="password" name="password" placeholder="Mật khẩu" required>
                        <button type="submit">Đăng nhập</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>