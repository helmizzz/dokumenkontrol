<?php
require_once __DIR__ . '/../../core/auth_middleware.php';
require_once __DIR__ . '/../../config/database.php';
check_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = $_POST['file_id'] ?? 0;
    
    // Ambil data dokumen di recycle bin
    $stmt = $pdo->prepare("SELECT id, title, file_path, uploaded_by FROM documents WHERE id = ? AND status = 0");
    $stmt->execute([$file_id]);
    $doc = $stmt->fetch();
    
    if (!$doc) {
        die('Dokumen tidak ditemukan di Recycle Bin.');
    }
    
    // Hanya Superadmin/Admin atau Uploader yang boleh Hard Delete
    if ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin' && $_SESSION['user_id'] != $doc['uploaded_by']) {
        die('Akses ditolak. Anda tidak berhak menghapus permanen dokumen ini.');
    }
    
    // 1. Hapus semua file PDF revisi
    $revStmt = $pdo->prepare("SELECT file_path FROM document_revisions WHERE document_id = ?");
    $revStmt->execute([$file_id]);
    $revisions = $revStmt->fetchAll();
    
    foreach ($revisions as $rev) {
        $revPath = __DIR__ . '/../../storage/document_revisions/' . $rev['file_path'];
        if (file_exists($revPath)) {
            unlink($revPath);
        }
    }
    
    // 2. Hapus file PDF utama
    $mainPath = __DIR__ . '/../../storage/secure_docs/' . $doc['file_path'];
    if (file_exists($mainPath)) {
        unlink($mainPath);
    }
    
    // 3. Hapus record dari database
    $stmtDelete = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    if ($stmtDelete->execute([$file_id])) {
        write_audit_log($pdo, $_SESSION['user_id'], 'DELETE', 'Recycle Bin', "Menghapus permanen dokumen '{$doc['title']}' beserta revisinya.");
        echo "success";
    } else {
        echo "Gagal menghapus permanen data dokumen dari database.";
    }
}
?>
