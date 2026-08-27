<?php
require_once __DIR__ . '/../config/database.php';
session_start();

if (!isset($_SESSION['role_name']) || ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin')) {
    die("Akses ditolak.");
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS document_shares (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        token VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) DEFAULT NULL,
        expires_at DATETIME DEFAULT NULL,
        created_by INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(document_id) REFERENCES documents(id) ON DELETE CASCADE,
        FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Tabel document_shares berhasil dibuat/sudah ada!<br>";
    echo "Migrasi Selesai! Anda akan dialihkan dalam 3 detik...";
    header("refresh:3;url=documents.php");
} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}
?>
