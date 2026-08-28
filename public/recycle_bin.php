<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

// Fetch filter options
$years = $pdo->query("SELECT * FROM years ORDER BY year_value DESC")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Handle search and filter
$where_clauses = ["d.status = 0"];
$params = [];

if (!empty($_GET['search'])) {
    $where_clauses[] = "(d.doc_number LIKE :search_doc OR d.title LIKE :search_title)";
    $searchTerm = '%' . trim($_GET['search']) . '%';
    $params[':search_doc'] = $searchTerm;
    $params[':search_title'] = $searchTerm;
}
if (!empty($_GET['year'])) {
    $where_clauses[] = "d.year_id = :year";
    $params[':year'] = $_GET['year'];
}
if (!empty($_GET['month'])) {
    $where_clauses[] = "d.month_value = :month";
    $params[':month'] = $_GET['month'];
}

// Department filter logic
if ($_SESSION['role_name'] === 'Superadmin') {
    if (!empty($_GET['dept'])) {
        $where_clauses[] = "d.dept_id = :dept";
        $params[':dept'] = $_GET['dept'];
    }
} else {
    // Admin & User only see their department's docs, PLUS public docs from other departments
    // Wait, requirement: "Filter Dropdown Departemen: ... otomatis terkunci pada departemen mereka sendiri atau disembunyikan"
    // To keep it simple, we hide the filter and apply strict WHERE clause.
    // Also need to consider Document Access for private files!
    // But for the repository view, let's just use a base query. 
    // If not superadmin, they can only view documents from their department OR public documents OR documents they have explicit access to.
}

if (!empty($_GET['access_type'])) {
    if ($_GET['access_type'] === 'public') {
        $where_clauses[] = "is_public = 1";
    } elseif ($_GET['access_type'] === 'private') {
        $where_clauses[] = "is_public = 0";
    }
}

// Construct Query
$sql = "SELECT d.*, y.year_value, dept.dept_name 
        FROM documents d 
        LEFT JOIN years y ON d.year_id = y.id 
        LEFT JOIN departments dept ON d.dept_id = dept.id";

// Role-based visibility
if ($_SESSION['role_name'] !== 'Superadmin') {
    $user_id = $_SESSION['user_id'];
    $dept_id = $_SESSION['dept_id'];
    $sql .= " LEFT JOIN document_access da ON (da.document_id = d.id AND da.user_id = $user_id)
              LEFT JOIN document_dept_access dda ON (dda.document_id = d.id AND dda.dept_id = $dept_id)
              WHERE (d.is_public = 1 OR d.dept_id = $dept_id OR da.id IS NOT NULL OR dda.id IS NOT NULL) 
              AND " . implode(' AND ', $where_clauses);
} else {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<h1 class="page-title">Recycle Bin (Sampah)</h1>

<!-- Filter Panel -->
<form method="GET" action="recycle_bin.php" class="filter-panel">
    <div class="form-group">
        <label for="search">Cari Dokumen</label>
        <input type="text" id="search" name="search" class="form-control" placeholder="Nomor / Judul" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="year">Tahun</label>
        <select id="year" name="year" class="form-control">
            <option value="">Semua Tahun</option>
            <?php foreach ($years as $yr): ?>
                <option value="<?php echo $yr['id']; ?>" <?php echo (($_GET['year'] ?? '') == $yr['id']) ? 'selected' : ''; ?>><?php echo $yr['year_value']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="month">Bulan</label>
        <select id="month" name="month" class="form-control">
            <option value="">Semua Bulan</option>
            <?php foreach ($months as $num => $name): ?>
                <option value="<?php echo $num; ?>" <?php echo (($_GET['month'] ?? '') == $num) ? 'selected' : ''; ?>><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($_SESSION['role_name'] === 'Superadmin'): ?>
    <div class="form-group">
        <label for="dept">Departemen</label>
        <select id="dept" name="dept" class="form-control">
            <option value="">Semua Departemen</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept['id']; ?>" <?php echo (($_GET['dept'] ?? '') == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['dept_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="access_type">Sifat Dokumen</label>
        <select id="access_type" name="access_type" class="form-control">
            <option value="">Semua Sifat</option>
            <option value="public" <?php echo (($_GET['access_type'] ?? '') == 'public') ? 'selected' : ''; ?>>Public</option>
            <option value="private" <?php echo (($_GET['access_type'] ?? '') == 'private') ? 'selected' : ''; ?>>Private</option>
        </select>
    </div>

    <div class="form-group">
        <button type="submit" class="btn-primary"><i class='bx bx-search'></i> Filter</button>
    </div>
</form>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nomor Dokumen</th>
                <th>Judul Dokumen</th>
                <th>Departemen Asal</th>
                <th>Terbit</th>
                <th>Sifat Akses</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($documents) > 0): ?>
                <?php $no = 1; foreach ($documents as $doc): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong>
                        <?php 
                        $disp_doc = $doc['doc_number'];
                        if (isset($doc['revision_number']) && $doc['revision_number'] > 0) {
                            $disp_doc .= '-' . $doc['revision_number'];
                        }
                        echo htmlspecialchars($disp_doc); 
                        ?>
                    </strong></td>
                    <td><?php echo htmlspecialchars($doc['title']); ?></td>
                    <td><?php echo htmlspecialchars($doc['dept_name'] ?? '-'); ?></td>
                    <td><?php echo isset($doc['month_value']) ? $months[$doc['month_value']] . ' ' . $doc['year_value'] : '-'; ?></td>
                    <td>
                        <?php if ($doc['is_public'] == 1): ?>
                            <span class="badge badge-public">🟢 Public</span>
                        <?php else: ?>
                            <span class="badge badge-private">🔴 Private</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <?php 
                                $can_delete = ($_SESSION['role_name'] === 'Superadmin' || $_SESSION['role_name'] === 'Admin' || $_SESSION['user_id'] == $doc['uploaded_by']);
                            ?>
                            <?php if ($can_delete): ?>
                                <button type="button" class="btn-icon" style="color: #10b981;" onclick="restoreDocument(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" title="Restore Dokumen"><i class='bx bx-log-in-circle'></i></button>
                                <button type="button" class="btn-icon" style="color: #ef4444;" onclick="hardDeleteDocument(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" title="Hapus Permanen"><i class='bx bx-x-circle'></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada dokumen yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>



<script>
function restoreDocument(fileId, title) {
    if (confirm('Anda yakin ingin memulihkan dokumen "' + title + '" ke Repositori Utama?')) {
        const formData = new FormData();
        formData.append('file_id', fileId);
        
        fetch('../modules/document/restore_trash_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.trim() === 'success') {
                alert('Dokumen berhasil dipulihkan!');
                window.location.reload();
            } else {
                alert(data);
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
        });
    }
}

function hardDeleteDocument(fileId, title) {
    if (confirm('PERINGATAN KRITIS!\n\nAnda yakin ingin MENGHAPUS PERMANEN dokumen "' + title + '"?\nSeluruh file PDF dan riwayat revisinya akan dimusnahkan dari server dan tidak dapat dikembalikan.\n\nLanjutkan?')) {
        const formData = new FormData();
        formData.append('file_id', fileId);
        
        fetch('../modules/document/hard_delete_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.trim() === 'success') {
                alert('Dokumen beserta file revisinya berhasil dihapus permanen!');
                window.location.reload();
            } else {
                alert(data);
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
