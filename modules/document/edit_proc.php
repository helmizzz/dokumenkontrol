<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

// Validasi role (hanya Superadmin & Admin)
if (!isset($_SESSION['role_name']) || ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin')) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $doc_id = (int)$_POST['doc_id'];
    $title = $_POST['title'];
    $year_id = $_POST['year_id'];
    $month_value = $_POST['month_value'];
    $is_public = ($_POST['access_type'] === 'public') ? 1 : 0;
    $user_id = $_SESSION['user_id'];
    
    // 1. Ambil data dokumen saat ini untuk pengecekan versi awal
    $stmt_curr = $pdo->prepare("SELECT doc_number, revision_number, file_path FROM documents WHERE id = ?");
    $stmt_curr->execute([$doc_id]);
    $current_doc = $stmt_curr->fetch();
    
    if (!$current_doc) {
        die("Dokumen tidak ditemukan.");
    }
    
    $doc_number = $current_doc['doc_number'];
    // Jika kolom belum diisi/tidak ada, anggap 0
    $current_revision = isset($current_doc['revision_number']) ? (int)$current_doc['revision_number'] : 0;
    
    $pdo->beginTransaction();
    try {
        // 2. Deteksi apakah ada berkas PDF baru yang diunggah
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['size'] > 0) {
            // --- KONDISI B: LOGIKA REVISI BERKAS ---
            $file = $_FILES['pdf_file'];
            
            // Validasi file
            if ($file['size'] > 10 * 1024 * 1024) {
                die("Eksekusi Ditolak: Ukuran berkas melebihi 10MB.");
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($mime !== 'application/pdf') {
                die("Error: Format file wajib berupa PDF.");
            }
            
            // Kenaikan versi otomatis
            $new_revision = $current_revision + 1;
            $new_filename = uniqid('REV_', true) . '.pdf';
            $target_path = '../../storage/secure_docs/' . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Simpan history revisi terlebih dahulu sebelum menimpa database
                $old_file_path = $current_doc['file_path'];
                if (!empty($old_file_path) && file_exists('../../storage/secure_docs/' . $old_file_path)) {
                    $stmt_rev = $pdo->prepare("INSERT INTO document_revisions (document_id, file_path, revision_number, created_by) VALUES (?, ?, ?, ?)");
                    $stmt_rev->execute([$doc_id, $old_file_path, $current_revision, $user_id]);
                    
                    // Pindahkan file lama ke folder revisions
                    rename('../../storage/secure_docs/' . $old_file_path, '../../storage/document_revisions/' . $old_file_path);
                }

                // Update database dengan file baru dan menaikkan nomor revisi
                $sql = "UPDATE documents SET title = ?, year_id = ?, month_value = ?, is_public = ?, file_path = ?, revision_number = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $year_id, $month_value, $is_public, $new_filename, $new_revision, $doc_id]);
                
                // Catat ke Audit Trail dengan kode revisi baru (misal: XX-XXX-001-1)
                $display_code = $doc_number . "-" . $new_revision;
                write_audit_log($pdo, $user_id, 'UPDATE', 'Dokumen', "Melakukan revisi berkas dokumen. Nomor kontrol naik menjadi: $display_code");
            } else {
                throw new Exception("Gagal mengunggah file baru.");
            }
        } else {
            // --- KONDISI A: LOGIKA UPDATE METADATA BIASA ---
            $sql = "UPDATE documents SET title = ?, year_id = ?, month_value = ?, is_public = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $year_id, $month_value, $is_public, $doc_id]);
            
            // Catat ke Audit Trail sebagai update informasi standar
            $display_code = ($current_revision > 0) ? $doc_number . "-" . $current_revision : $doc_number;
            write_audit_log($pdo, $user_id, 'UPDATE', 'Dokumen', "Mengubah informasi metadata dokumen: $display_code");
        }
        
        // 3. Rekonfigurasi Hak Akses (Khusus jika bersifat Private)
        // Pertama, hapus akses lama
        $pdo->prepare("DELETE FROM document_access WHERE document_id = ?")->execute([$doc_id]);
        $pdo->prepare("DELETE FROM document_dept_access WHERE document_id = ?")->execute([$doc_id]);
        
        // Jika private, masukkan daftar baru yang dicentang
        if ($is_public === 0) {
            if (isset($_POST['allowed_users']) && is_array($_POST['allowed_users'])) {
                $stmt_acc = $pdo->prepare("INSERT INTO document_access (document_id, user_id) VALUES (?, ?)");
                foreach ($_POST['allowed_users'] as $acc_user_id) {
                    $stmt_acc->execute([$doc_id, (int)$acc_user_id]);
                }
            }
            if (isset($_POST['allowed_depts']) && is_array($_POST['allowed_depts'])) {
                $stmt_dept_acc = $pdo->prepare("INSERT INTO document_dept_access (document_id, dept_id) VALUES (?, ?)");
                foreach ($_POST['allowed_depts'] as $acc_dept_id) {
                    $stmt_dept_acc->execute([$doc_id, (int)$acc_dept_id]);
                }
            }
        }
        
        $pdo->commit();
        
        // Arahkan kembali ke repositori dengan pesan sukses
        header("Location: ../../public/documents.php?success=1");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Terjadi kesalahan sistem: " . $e->getMessage());
    }
} else {
    header("Location: ../../public/documents.php");
    exit;
}
?>
