<?php
require_once __DIR__ . '/../config/database.php';

echo "Memulai migrasi struktur tabel (menambahkan status pada documents)...<br>";

try {
    // Tambahkan kolom status jika belum ada
    $pdo->exec("ALTER TABLE documents ADD COLUMN status TINYINT(1) DEFAULT 1;");
    echo "Kolom 'status' berhasil ditambahkan ke tabel documents.<br>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Kolom 'status' sudah ada di tabel documents (Aman).<br>";
    } else {
        echo "Gagal menambahkan kolom: " . $e->getMessage() . "<br>";
    }
}

echo "<br><a href='index.php'>Kembali ke Beranda</a>";
?>
