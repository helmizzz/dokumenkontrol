<?php
require_once __DIR__ . '/../config/database.php';
session_start();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak.");
}

// Ignore errors if columns already exist
try { $pdo->exec("ALTER TABLE departments ADD COLUMN status INTEGER DEFAULT 1"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE departments ADD COLUMN dept_code VARCHAR(10)"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE years ADD COLUMN status INTEGER DEFAULT 1"); } catch (Exception $e) {}

// Update existing null statuses to 1
$pdo->exec("UPDATE departments SET status = 1 WHERE status IS NULL");
$pdo->exec("UPDATE years SET status = 1 WHERE status IS NULL");

header("Location: master_data.php?success=Migrasi_Berhasil");
exit;
?>
