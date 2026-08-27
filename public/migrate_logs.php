<?php
require_once __DIR__ . '/../config/database.php';
session_start();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak.");
}

try {
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_logs_created ON activity_logs (created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_logs_module ON activity_logs (module)");
    
    header("Location: logs.php?success=Optimasi_Database_Berhasil");
    exit;
} catch (Exception $e) {
    die("Gagal membuat indeks: " . $e->getMessage());
}
?>
