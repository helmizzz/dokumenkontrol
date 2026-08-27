<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

// Validasi role (hanya Superadmin & Admin)
if (!isset($_SESSION['role_name']) || ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin')) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $doc_number = $_POST['doc_number'];
    $title = $_POST['title'];
    $is_public = ($_POST['access_type'] === 'public') ? 1 : 0;
    $year_id = $_POST['year_id'];
    $month_value = $_POST['month_value'];
    $dept_id = $_SESSION['dept_id']; // Otomatis sesuai departemen admin pengunggah
    $uploaded_by = $_SESSION['user_id'];

    // Cek duplikasi nomor dokumen
    $stmt_check = $pdo->prepare("SELECT id FROM documents WHERE doc_number = :doc_number");
    $stmt_check->execute([':doc_number' => $doc_number]);
    if ($stmt_check->fetch()) {
        die("Eksekusi Ditolak: Nomor Dokumen sudah terdaftar di sistem.");
    }

    // Pengecekan Berkas Berbasis File Mime Type
    $file = $_FILES['pdf_file'];
    
    // Periksa ukuran maksimal 10MB
    if ($file['size'] > 10 * 1024 * 1024) {
        die("Eksekusi Ditolak: Ukuran berkas melebihi 10MB.");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        die("Eksekusi Ditolak: Sistem hanya menerima format berkas PDF standar.");
    }

    // Pembuatan nama file unik terenkripsi
    $filename = uniqid('DOC_', true) . '.pdf';
    // Karena posisi skrip ada di modules/document/ , kita naik 2 tingkat untuk ke root, lalu masuk storage
    $target_path = '../../storage/secure_docs/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        
        // Gunakan transaksi untuk menjaga integritas (jika insert access gagal, dokumen di-rollback)
        $pdo->beginTransaction();
        try {
            // Dokumen langsung disimpan dengan status aktif tanpa alur approval
            $sql = "INSERT INTO documents (doc_number, title, file_path, is_public, year_id, month_value, dept_id, uploaded_by) 
                    VALUES (:doc_number, :title, :file_path, :is_public, :year_id, :month_value, :dept_id, :uploaded_by)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':doc_number' => $doc_number,
                ':title' => $title,
                ':file_path' => $filename,
                ':is_public' => $is_public,
                ':year_id' => $year_id,
                ':month_value' => $month_value,
                ':dept_id' => $dept_id,
                ':uploaded_by' => $uploaded_by
            ]);

            $new_doc_id = $pdo->lastInsertId();

            // Jika bersifat private, tambahkan daftar akses
            if ($is_public === 0) {
                if (isset($_POST['allowed_users']) && is_array($_POST['allowed_users'])) {
                    $sql_access = "INSERT INTO document_access (document_id, user_id) VALUES (:doc_id, :u_id)";
                    $stmt_access = $pdo->prepare($sql_access);
                    foreach ($_POST['allowed_users'] as $u_id) {
                        $stmt_access->execute([':doc_id' => $new_doc_id, ':u_id' => $u_id]);
                    }
                }
                if (isset($_POST['allowed_depts']) && is_array($_POST['allowed_depts'])) {
                    $sql_dept_access = "INSERT INTO document_dept_access (document_id, dept_id) VALUES (:doc_id, :d_id)";
                    $stmt_dept_access = $pdo->prepare($sql_dept_access);
                    foreach ($_POST['allowed_depts'] as $d_id) {
                        $stmt_dept_access->execute([':doc_id' => $new_doc_id, ':d_id' => $d_id]);
                    }
                }
            }

            // Tulis ke Log Audit Trail
            write_audit_log($pdo, $uploaded_by, 'CREATE', 'Dokumen', "Mengunggah dokumen baru bernomor: $doc_number");

            $pdo->commit();

            // Arahkan kembali ke halaman repositori dengan status sukses
            header("Location: ../../public/documents.php?success=1&doc_num=" . urlencode($doc_number));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            // Hapus file yang terlanjur terupload jika DB gagal
            if(file_exists($target_path)) unlink($target_path);
            die("Terjadi kesalahan sistem: " . $e->getMessage());
        }
    } else {
        die("Gagal memindahkan berkas yang diunggah.");
    }
} else {
    header("Location: ../../public/dashboard.php");
    exit;
}
?>
