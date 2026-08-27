<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

// Validasi role (hanya Superadmin & Admin yang boleh melihat metadata log)
if ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin') {
    die("Akses ditolak. Anda tidak memiliki izin untuk melihat metadata.");
}

if (!isset($_GET['id'])) {
    die("ID Dokumen tidak ditemukan.");
}

$doc_id = (int)$_GET['id'];

// Ambil informasi dokumen
$stmt = $pdo->prepare("SELECT d.*, u.username as uploader_name, u.full_name, dept.dept_name 
                       FROM documents d 
                       LEFT JOIN users u ON d.uploaded_by = u.id
                       LEFT JOIN departments dept ON d.dept_id = dept.id
                       WHERE d.id = :id");
$stmt->execute([':id' => $doc_id]);
$document = $stmt->fetch();

if (!$document) {
    die("Dokumen tidak ditemukan di database.");
}

// Keamanan Tambahan: Admin hanya boleh melihat dokumen dari departemennya sendiri atau yang public
if ($_SESSION['role_name'] === 'Admin') {
    if ($document['dept_id'] !== $_SESSION['dept_id'] && $document['is_public'] == 0) {
        die("Akses ditolak. Dokumen bersifat private milik departemen lain.");
    }
}

// Ambil log aktivitas yang terkait dengan dokumen ini
// Karena activity_logs menggunakan teks description, kita cari berdasarkan doc_number
$doc_number = $document['doc_number'];
$search_term = "%$doc_number%";

$log_stmt = $pdo->prepare("SELECT a.*, u.username 
                           FROM activity_logs a
                           LEFT JOIN users u ON a.user_id = u.id
                           WHERE a.description LIKE :search_term
                           ORDER BY a.created_at DESC");
$log_stmt->execute([':search_term' => $search_term]);
$logs = $log_stmt->fetchAll();

?>
<?php include 'includes/header.php'; ?>

<div class="header-action">
    <h1 class="page-title">Metadata & Histori Dokumen</h1>
    <a href="documents.php" class="btn-secondary"><i class='bx bx-arrow-back'></i> Kembali ke Repositori</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <h3 class="card-title">Informasi Dasar Dokumen</h3>
    <table class="data-table" style="width: 100%; border: none;">
        <tr>
            <td style="width: 200px; font-weight: bold; border: none; padding: 5px 0;">Nomor Dokumen</td>
            <td style="border: none; padding: 5px 0;">: <span class="badge badge-primary"><?php echo htmlspecialchars($document['doc_number']); ?></span></td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: none; padding: 5px 0;">Judul Dokumen</td>
            <td style="border: none; padding: 5px 0;">: <?php echo htmlspecialchars($document['title']); ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: none; padding: 5px 0;">Sifat Dokumen</td>
            <td style="border: none; padding: 5px 0;">: 
                <?php echo ($document['is_public'] == 1) ? '<span class="badge badge-public">Public</span>' : '<span class="badge badge-private">Private</span>'; ?>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: none; padding: 5px 0;">Departemen Pemilik</td>
            <td style="border: none; padding: 5px 0;">: <?php echo htmlspecialchars($document['dept_name']); ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: none; padding: 5px 0;">Diunggah Oleh</td>
            <td style="border: none; padding: 5px 0;">: <?php echo htmlspecialchars($document['uploader_name'] . ' (' . ($document['full_name'] ?? 'N/A') . ')'); ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold; border: none; padding: 5px 0;">Tanggal Terbit / Upload</td>
            <td style="border: none; padding: 5px 0;">: <?php echo date('d-M-Y H:i', strtotime($document['created_at'])); ?></td>
        </tr>
    </table>
</div>

<div class="card">
    <h3 class="card-title"><i class='bx bx-history'></i> Audit Trail (Riwayat Aktivitas)</h3>
    <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 15px;">Semua tindakan modifikasi terhadap dokumen ini terekam secara otomatis oleh sistem.</p>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu Kejadian</th>
                    <th>Pelaku (Username)</th>
                    <th>Tindakan (Aksi)</th>
                    <th>Modul</th>
                    <th>Deskripsi Lengkap</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo date('d-M-Y H:i:s', strtotime($log['created_at'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></strong></td>
                        <td>
                            <?php 
                            if ($log['action_type'] === 'CREATE') {
                                echo '<span style="color: green;">🟢 CREATE</span>';
                            } elseif ($log['action_type'] === 'UPDATE') {
                                echo '<span style="color: orange;">🟠 UPDATE</span>';
                            } elseif ($log['action_type'] === 'DELETE') {
                                echo '<span style="color: red;">🔴 DELETE</span>';
                            } else {
                                echo htmlspecialchars($log['action_type']);
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($log['module']); ?></td>
                        <td><?php echo htmlspecialchars($log['description']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">Tidak ada riwayat aktivitas ditemukan untuk dokumen ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
