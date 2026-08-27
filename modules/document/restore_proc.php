<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

// Validasi role (hanya Superadmin & Admin)
if (!isset($_SESSION['role_name']) || ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin')) {
    echo "Error: Akses ditolak.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (!isset($_POST['rev_id'])) {
        echo "Error: Parameter tidak valid.";
        exit;
    }

    $rev_id = (int)$_POST['rev_id'];
    $user_id = $_SESSION['user_id'];

    $pdo->beginTransaction();
    try {
        // 1. Ambil data revisi lama
        $stmt_rev = $pdo->prepare("SELECT * FROM document_revisions WHERE id = ?");
        $stmt_rev->execute([$rev_id]);
        $revision = $stmt_rev->fetch();

        if (!$revision) {
            throw new Exception("Data revisi tidak ditemukan.");
        }

        $doc_id = $revision['document_id'];
        $old_file = $revision['file_path']; // Nama file di folder document_revisions

        if (!file_exists('../../storage/document_revisions/' . $old_file)) {
            throw new Exception("File fisik revisi lama tidak ditemukan di server.");
        }

        // 2. Ambil data dokumen aktif saat ini
        $stmt_curr = $pdo->prepare("SELECT doc_number, revision_number, file_path FROM documents WHERE id = ?");
        $stmt_curr->execute([$doc_id]);
        $current_doc = $stmt_curr->fetch();

        if (!$current_doc) {
            throw new Exception("Dokumen tidak ditemukan.");
        }

        $current_file = $current_doc['file_path'];
        $current_rev_number = isset($current_doc['revision_number']) ? (int)$current_doc['revision_number'] : 0;
        
        // 3. Masukkan file aktif saat ini ke history
        if (!empty($current_file) && file_exists('../../storage/secure_docs/' . $current_file)) {
            $stmt_ins = $pdo->prepare("INSERT INTO document_revisions (document_id, file_path, revision_number, created_by) VALUES (?, ?, ?, ?)");
            $stmt_ins->execute([$doc_id, $current_file, $current_rev_number, $user_id]);
            
            // Pindahkan file aktif ke history
            rename('../../storage/secure_docs/' . $current_file, '../../storage/document_revisions/' . $current_file);
        }

        // 4. Copy file dari history ke tempat utama (secure_docs) dengan nama baru
        $new_filename = uniqid('REV_', true) . '.pdf';
        copy('../../storage/document_revisions/' . $old_file, '../../storage/secure_docs/' . $new_filename);

        // 5. Update tabel documents (naikkan revisi)
        $new_revision = $current_rev_number + 1;
        $sql = "UPDATE documents SET file_path = ?, revision_number = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt_upd = $pdo->prepare($sql);
        $stmt_upd->execute([$new_filename, $new_revision, $doc_id]);

        // 6. Tulis Audit Log
        $display_code = $current_doc['doc_number'] . "-" . $new_revision;
        $desc = "Me-restore dokumen ke Revisi " . $revision['revision_number'] . ". Nomor kontrol naik menjadi: " . $display_code;
        write_audit_log($pdo, $user_id, 'UPDATE', 'Dokumen', $desc);

        $pdo->commit();
        echo "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: Akses ditolak.";
}
?>
