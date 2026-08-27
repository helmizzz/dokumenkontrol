<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/logger.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['file_id'])) {
    die("Akses Ditolak.");
}

$file_id = $_GET['file_id'];
$user_id = $_SESSION['user_id'];
$dept_id = $_SESSION['dept_id'];
$role_name = $_SESSION['role_name'];

// Kueri pengecekan hak baca dari database
$sql = "SELECT title, file_path, is_public, uploaded_by, dept_id FROM documents WHERE id = :file_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':file_id' => $file_id]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Berkas tidak ditemukan.");
}

$has_access = false;
if ($doc['is_public'] == 1 || $doc['uploaded_by'] == $user_id || $role_name === 'Superadmin' || $doc['dept_id'] == $dept_id) {
    $has_access = true;
} else {
    // Cek izin eksplisit pada tabel pivot private access (User)
    $sql_check = "SELECT id FROM document_access WHERE document_id = :file_id AND user_id = :user_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':file_id' => $file_id, ':user_id' => $user_id]);
    if ($stmt_check->fetch()) {
        $has_access = true;
    } else {
        // Cek izin eksplisit pada tabel pivot private access (Departemen)
        $sql_dept = "SELECT id FROM document_dept_access WHERE document_id = :file_id AND dept_id = :dept_id";
        $stmt_dept = $pdo->prepare($sql_dept);
        $stmt_dept->execute([':file_id' => $file_id, ':dept_id' => $dept_id]);
        if ($stmt_dept->fetch()) {
            $has_access = true;
        }
    }
}

if (!$has_access) {
    die("Anda tidak memiliki izin akses untuk melihat dokumen private ini.");
}

$full_path = '../storage/secure_docs/' . $doc['file_path'];
if (file_exists($full_path)) {
    $doc_title = $doc['title'] ?? ('ID: ' . $file_id);
    write_audit_log($pdo, $user_id, 'VIEW', 'Document Viewer', "Membuka dokumen: " . $doc_title);

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="document.pdf"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    @readfile($full_path);
} else {
    die("File fisik PDF tidak ditemukan di server.");
}
?>
