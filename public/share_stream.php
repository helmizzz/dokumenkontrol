<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/logger.php';
session_start();

if (!isset($_GET['token'])) {
    die("Token tidak ditemukan.");
}

$token = $_GET['token'];

// Cari data share
$stmt = $pdo->prepare("SELECT s.*, d.file_path, d.title 
                       FROM document_shares s
                       JOIN documents d ON s.document_id = d.id
                       WHERE s.token = ?");
$stmt->execute([$token]);
$share = $stmt->fetch();

if (!$share) {
    die("Link tidak valid.");
}

// Cek kadaluarsa
if (!empty($share['expires_at'])) {
    $expires = strtotime($share['expires_at']);
    if (time() > $expires) {
        die("Link kadaluarsa.");
    }
}

// Cek autentikasi password jika disetel
if (!empty($share['password_hash'])) {
    if (!isset($_SESSION['share_unlocked'][$token]) || $_SESSION['share_unlocked'][$token] !== true) {
        die("Akses ditolak. Anda belum memverifikasi password dokumen ini.");
    }
}

$file_path = '../storage/secure_docs/' . $share['file_path'];

if (!file_exists($file_path)) {
    die("File fisik tidak ditemukan di server.");
}

$doc_title = $share['title'] ?? ('Dokumen ID: ' . $share['document_id']);
write_audit_log($pdo, null, 'VIEW', 'Share Link', "Dokumen dibuka via Share Link: " . $doc_title);

// Stream PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Content-Length: ' . filesize($file_path));

// Hapus output buffer dan read file
ob_clean();
flush();
readfile($file_path);
exit;
?>
