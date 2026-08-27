<?php
require_once __DIR__ . '/../config/database.php';
session_start();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak.");
}

// Ignore errors if columns already exist
try { $pdo->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(100)"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE users ADD COLUMN status INTEGER DEFAULT 1"); } catch (Exception $e) {}

// Update existing null values
$pdo->exec("UPDATE users SET status = 1 WHERE status IS NULL");
$pdo->exec("UPDATE users SET full_name = username WHERE full_name IS NULL OR full_name = ''");

header("Location: user_management.php?success=Migrasi_User_Berhasil");
exit;
?>
