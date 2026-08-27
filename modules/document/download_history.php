<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

// Validasi role (hanya Superadmin & Admin yang bisa download history)
if (!isset($_SESSION['role_name']) || ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin')) {
    die("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    die("Parameter ID tidak valid.");
}

$rev_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data dari document_revisions
$stmt = $pdo->prepare("SELECT r.*, d.title, d.doc_number 
                       FROM document_revisions r 
                       JOIN documents d ON r.document_id = d.id 
                       WHERE r.id = ?");
$stmt->execute([$rev_id]);
$rev = $stmt->fetch();

if (!$rev) {
    die("Data revisi tidak ditemukan.");
}

$file_path = '../../storage/document_revisions/' . $rev['file_path'];

if (!file_exists($file_path)) {
    die("File fisik PDF (versi lama) tidak ditemukan di server.");
}

// Catat log download
$desc = "Mendownload file history (Revisi " . $rev['revision_number'] . ") dari dokumen: " . $rev['doc_number'];
write_audit_log($pdo, $user_id, 'VIEW', 'History Dokumen', $desc);

// Stream untuk download (Force download)
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $rev['doc_number'] . '_REV' . $rev['revision_number'] . '.pdf"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

ob_clean();
flush();
readfile($file_path);
exit;
?>
