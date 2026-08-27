<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak. Halaman ini khusus Superadmin.");
}

// Menangani Filter Pencarian
$where_clauses = ["1=1"];
$params = [];

if (!empty($_GET['start_date'])) {
    $where_clauses[] = "DATE(a.created_at) >= :start_date";
    $params[':start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $where_clauses[] = "DATE(a.created_at) <= :end_date";
    $params[':end_date'] = $_GET['end_date'];
}
if (!empty($_GET['module'])) {
    $where_clauses[] = "a.module = :module";
    $params[':module'] = $_GET['module'];
}
if (!empty($_GET['action_type'])) {
    $where_clauses[] = "a.action_type = :action_type";
    $params[':action_type'] = $_GET['action_type'];
}
if (!empty($_GET['search'])) {
    $where_clauses[] = "(a.description LIKE :search OR u.username LIKE :search OR u.full_name LIKE :search)";
    $params[':search'] = '%' . trim($_GET['search']) . '%';
}

$sql = "SELECT a.*, u.username, u.full_name 
        FROM activity_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE " . implode(' AND ', $where_clauses) . "
        ORDER BY a.created_at DESC LIMIT 500"; // Dibatasi 500 baris terbaru untuk mencegah lag browser

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Ambil daftar modul yang unik dari database untuk dropdown
$modules = $pdo->query("SELECT DISTINCT module FROM activity_logs ORDER BY module ASC")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<h1 class="page-title">Audit Trail (Log Viewer)</h1>

<?php if (isset($_GET['success'])): ?>
    <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
        <i class='bx bx-check-circle'></i> <?php echo htmlspecialchars(str_replace('_', ' ', $_GET['success'])); ?>
    </div>
<?php endif; ?>

<!-- Panel Filter -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="logs.php" class="filter-panel" style="margin-bottom: 0;">
        <div class="form-group">
            <label>Tanggal Mulai</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Tanggal Akhir</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Modul</label>
            <select name="module" class="form-control">
                <option value="">Semua Modul</option>
                <?php foreach($modules as $m): ?>
                    <option value="<?php echo $m['module']; ?>" <?php echo (($_GET['module'] ?? '') == $m['module']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['module']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Aksi</label>
            <select name="action_type" class="form-control">
                <option value="">Semua Aksi</option>
                <option value="CREATE" <?php echo (($_GET['action_type'] ?? '') == 'CREATE') ? 'selected' : ''; ?>>CREATE</option>
                <option value="UPDATE" <?php echo (($_GET['action_type'] ?? '') == 'UPDATE') ? 'selected' : ''; ?>>UPDATE</option>
                <option value="DELETE" <?php echo (($_GET['action_type'] ?? '') == 'DELETE') ? 'selected' : ''; ?>>DELETE</option>
                <option value="VIEW" <?php echo (($_GET['action_type'] ?? '') == 'VIEW') ? 'selected' : ''; ?>>VIEW</option>
            </select>
        </div>
        <div class="form-group" style="grid-column: span 2;">
            <label>Pencarian</label>
            <input type="text" name="search" class="form-control" placeholder="Cari nama pelaku atau deskripsi aktivitas..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn-primary"><i class='bx bx-filter-alt'></i> Terapkan Filter</button>
        </div>
    </form>
</div>

<!-- Tabel Log -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Waktu Kejadian</th>
                <th>Pelaku</th>
                <th>Aksi</th>
                <th>Modul</th>
                <th>Deskripsi Detail</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($logs) > 0): ?>
                <?php $no=1; foreach($logs as $log): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo date('d-M-Y H:i:s', strtotime($log['created_at'])); ?></td>
                    <td>
                        <?php if ($log['username']): ?>
                            <strong><?php echo htmlspecialchars($log['username']); ?></strong>
                        <?php else: ?>
                            <span style="color: #9ca3af; font-style: italic;">Sistem / Dihapus</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if ($log['action_type'] == 'CREATE') echo '<span class="badge badge-create">🟢 CREATE</span>';
                        elseif ($log['action_type'] == 'UPDATE') echo '<span class="badge badge-update">🟠 UPDATE</span>';
                        elseif ($log['action_type'] == 'DELETE') echo '<span class="badge badge-delete">🔴 DELETE</span>';
                        elseif ($log['action_type'] == 'VIEW') echo '<span class="badge" style="background-color: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">🔵 VIEW</span>';
                        else echo '<span class="badge">'.$log['action_type'].'</span>';
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($log['module']); ?></td>
                    <td style="white-space: normal; min-width: 300px;"><?php echo htmlspecialchars($log['description']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Tidak ada jejak audit yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div style="padding: 15px; text-align: center; font-size: 0.8rem; color: #6b7280; border-top: 1px solid var(--border-color);">
        Menampilkan maksimal 500 baris log terbaru. Gunakan filter untuk mencari data yang lebih spesifik.
    </div>
</div>

<?php include 'includes/footer.php'; ?>
