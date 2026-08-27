<?php
require_once __DIR__ . '/../../core/auth_middleware.php';
require_once __DIR__ . '/../../config/database.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = $_POST['file_id'] ?? 0;
    
    // Validasi eksistensi & izin (status = 0 di recycle bin)
    $stmt = $pdo->prepare("SELECT id, title, uploaded_by FROM documents WHERE id = ? AND status = 0");
    $stmt->execute([$file_id]);
    $doc = $stmt->fetch();
    
    if (!$doc) {
        die('Dokumen tidak ditemukan di Recycle Bin.');
    }
    
    // Hanya Uploader, Admin, dan Superadmin yang boleh restore
    if ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin' && $_SESSION['user_id'] != $doc['uploaded_by']) {
        die('Akses ditolak. Anda tidak berhak memulihkan dokumen ini.');
    }
    
    // Lakukan restore
    $stmtUpdate = $pdo->prepare("UPDATE documents SET status = 1 WHERE id = ?");
    if ($stmtUpdate->execute([$file_id])) {
        // Catat ke log
        write_audit_log($pdo, $_SESSION['user_id'], 'RESTORE', 'Recycle Bin', "Memulihkan dokumen '{$doc['title']}' dari Recycle Bin");
        
        echo "success";
    } else {
        echo "Gagal memulihkan dokumen.";
    }
}
?>
