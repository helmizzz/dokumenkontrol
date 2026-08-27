<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
session_start();

if (!isset($_SESSION['role_name']) || ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin')) {
    die("Akses ditolak.");
}

if (!isset($_GET['doc_id'])) {
    die("Dokumen tidak valid.");
}

$doc_id = (int)$_GET['doc_id'];

// Get document title to show in modal header (optional)
$stmt_doc = $pdo->prepare("SELECT title, doc_number FROM documents WHERE id = ?");
$stmt_doc->execute([$doc_id]);
$document = $stmt_doc->fetch();

if (!$document) {
    die("Dokumen tidak ditemukan.");
}

// Get history
$stmt_rev = $pdo->prepare("
    SELECT r.*, u.username, u.full_name 
    FROM document_revisions r 
    LEFT JOIN users u ON r.created_by = u.id 
    WHERE r.document_id = ? 
    ORDER BY r.revision_number DESC, r.created_at DESC
");
$stmt_rev->execute([$doc_id]);
$revisions = $stmt_rev->fetchAll();

if (count($revisions) === 0) {
    echo '<div style="text-align: center; padding: 20px;">Tidak ada riwayat revisi untuk dokumen ini.</div>';
    exit;
}
?>

<table class="data-table" style="font-size: 0.9rem;">
    <thead>
        <tr>
            <th>Versi / Revisi</th>
            <th>Waktu Pembaruan</th>
            <th>Diperbarui Oleh</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($revisions as $rev): ?>
        <tr>
            <td><strong>Revisi <?php echo $rev['revision_number']; ?></strong></td>
            <td><?php echo date('d M Y, H:i', strtotime($rev['created_at'])); ?></td>
            <td><?php echo htmlspecialchars($rev['full_name'] ?? $rev['username'] ?? 'Sistem'); ?></td>
            <td>
                <a href="../modules/document/download_history.php?id=<?php echo $rev['id']; ?>" class="btn-icon" title="Download File Backup"><i class='bx bx-download'></i></a>
                <button type="button" class="btn-icon" onclick="restoreHistory(<?php echo $rev['id']; ?>, <?php echo $rev['revision_number']; ?>)" title="Restore Versi Ini"><i class='bx bx-reset'></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
