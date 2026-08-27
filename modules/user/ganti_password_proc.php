<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi input dasar
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../../public/dashboard.php?pwd_error=" . urlencode("Semua kolom harus diisi!"));
        exit;
    }

    if ($new_password !== $confirm_password) {
        header("Location: ../../public/dashboard.php?pwd_error=" . urlencode("Password baru dan konfirmasi tidak cocok!"));
        exit;
    }

    try {
        // Cek password lama
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_password, $user['password'])) {
            // Hash password baru
            $new_hashed = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update database
            $update = $pdo->prepare("UPDATE users SET password = ?, is_password_changed = 1 WHERE id = ?");
            $update->execute([$new_hashed, $user_id]);
            
            // Update session
            $_SESSION['is_password_changed'] = 1;

            write_audit_log($pdo, $user_id, 'UPDATE', 'User Profile', "User mengubah password mereka secara mandiri.");

            header("Location: ../../public/dashboard.php?pwd_success=1");
            exit;
        } else {
            header("Location: ../../public/dashboard.php?pwd_error=" . urlencode("Password lama yang Anda masukkan salah!"));
            exit;
        }
    } catch (PDOException $e) {
        header("Location: ../../public/dashboard.php?pwd_error=" . urlencode("Terjadi kesalahan sistem."));
        exit;
    }
}
header("Location: ../../public/dashboard.php");
exit;
?>
