<?php
require_once __DIR__ . '/../config/database.php';
session_start();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak.");
}

// Ignore errors if column already exists
try {
    $pdo->exec("ALTER TABLE documents ADD COLUMN revision_number INTEGER DEFAULT 0");
    echo "Kolom revision_number berhasil ditambahkan ke tabel documents!<br>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        echo "Kolom revision_number sudah ada.<br>";
    } else {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}

echo "Migrasi Selesai! Anda akan dialihkan dalam 3 detik...";
header("refresh:3;url=documents.php");
?>
