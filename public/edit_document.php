<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

// Validasi role (hanya Superadmin & Admin)
if ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin') {
    die("Akses ditolak. Anda tidak memiliki izin untuk halaman ini.");
}

if (!isset($_GET['doc_id'])) {
    die("ID Dokumen tidak ditemukan.");
}

$doc_id = (int)$_GET['doc_id'];

// Ambil informasi dokumen
$stmt = $pdo->prepare("SELECT d.*, dept.dept_name 
                       FROM documents d 
                       LEFT JOIN departments dept ON d.dept_id = dept.id
                       WHERE d.id = :id");
$stmt->execute([':id' => $doc_id]);
$document = $stmt->fetch();

if (!$document) {
    die("Dokumen tidak ditemukan.");
}

// Keamanan: Admin hanya boleh mengedit dokumen dari departemennya sendiri
if ($_SESSION['role_name'] === 'Admin') {
    if ($document['dept_id'] !== $_SESSION['dept_id']) {
        die("Akses ditolak. Anda hanya dapat mengedit dokumen departemen Anda sendiri.");
    }
}

// Data Master untuk Dropdown
$years = $pdo->query("SELECT * FROM years WHERE status = 1 OR status IS NULL ORDER BY year_value DESC")->fetchAll();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Ambil daftar departemen (kecuali departemen asal dokumen)
$stmt_depts = $pdo->prepare("SELECT * FROM departments WHERE id != ? ORDER BY dept_name ASC");
$stmt_depts->execute([$document['dept_id']]);
$other_depts = $stmt_depts->fetchAll();

// Ambil semua user (kecuali diri sendiri), join departemen
$stmt_all_users = $pdo->prepare("
    SELECT u.id, u.username, u.full_name, r.role_name, d.dept_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    LEFT JOIN departments d ON u.dept_id = d.id 
    WHERE u.id != ? 
    ORDER BY d.dept_name ASC, u.username ASC
");
$stmt_all_users->execute([$_SESSION['user_id']]);
$all_users = $stmt_all_users->fetchAll();

$users_by_dept = [];
foreach ($all_users as $u) {
    $deptName = $u['dept_name'] ?? 'Lainnya';
    $users_by_dept[$deptName][] = $u;
}

// Ambil daftar akses user saat ini
$stmt_access = $pdo->prepare("SELECT user_id FROM document_access WHERE document_id = :doc_id");
$stmt_access->execute([':doc_id' => $doc_id]);
$current_access = $stmt_access->fetchAll(PDO::FETCH_COLUMN, 0);

// Ambil daftar akses departemen saat ini
$stmt_dept_access = $pdo->prepare("SELECT dept_id FROM document_dept_access WHERE document_id = :doc_id");
$stmt_dept_access->execute([':doc_id' => $doc_id]);
$current_dept_access = $stmt_dept_access->fetchAll(PDO::FETCH_COLUMN, 0);

?>
<?php include 'includes/header.php'; ?>

<!-- Tom Select CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-control { border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; }
    .ts-dropdown { border-radius: 0 0 6px 6px; }
</style>

<div class="header-action">
    <h1 class="page-title">Edit Dokumen & Revisi</h1>
    <a href="documents.php" class="btn-secondary"><i class='bx bx-arrow-back'></i> Kembali ke Repositori</a>
</div>

<form id="editForm" action="../modules/document/edit_proc.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="doc_id" value="<?php echo $doc_id; ?>">
    <input type="hidden" name="doc_number" value="<?php echo htmlspecialchars($document['doc_number']); ?>">
    
    <div class="upload-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Kolom Kiri: Metadata & Berkas -->
        <div class="upload-col" style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card" style="height: auto;">
                <h3 class="card-title">Informasi Dasar Dokumen</h3>
                
                <div class="form-group">
                    <label>Nomor Dokumen Dasar (Tidak dapat diubah)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($document['doc_number']); ?>" disabled>
                    <small style="color: #6b7280;">Versi saat ini: Revisi ke-<?php echo isset($document['revision_number']) ? $document['revision_number'] : 0; ?></small>
                </div>

                <div class="form-group">
                    <label for="title">Judul Dokumen <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($document['title']); ?>">
                </div>

                <div class="form-group-row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="year_id">Tahun <span class="text-danger">*</span></label>
                        <select id="year_id" name="year_id" class="form-control" required>
                            <option value="">-- Pilih Tahun --</option>
                            <?php foreach ($years as $yr): ?>
                                <option value="<?php echo $yr['id']; ?>" <?php echo ($document['year_id'] == $yr['id']) ? 'selected' : ''; ?>>
                                    <?php echo $yr['year_value']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="month_value">Bulan <span class="text-danger">*</span></label>
                        <select id="month_value" name="month_value" class="form-control" required>
                            <option value="">-- Pilih Bulan --</option>
                            <?php foreach ($months as $num => $name): ?>
                                <option value="<?php echo $num; ?>" <?php echo ($document['month_value'] == $num) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bagian Pembaruan Berkas -->
            <div class="card" style="height: auto;">
                <h3 class="card-title">Pembaruan Berkas PDF (Opsional)</h3>
                <p style="color: #6b7280; font-size: 0.85rem; margin-bottom: 10px;">
                    Biarkan kosong jika Anda hanya ingin memperbarui judul/hak akses. Mengunggah berkas PDF baru akan secara otomatis menaikkan versi dokumen (*Revision Number*).
                </p>
                <div class="upload-area" id="uploadArea" style="border: 2px dashed var(--border-color); padding: 30px; text-align: center; border-radius: 8px; cursor: pointer;">
                    <i class='bx bxs-file-pdf' style="font-size: 3rem; color: #ef4444; margin-bottom: 10px;"></i>
                    <p style="margin-bottom: 5px;"><strong>Klik untuk memilih file baru</strong> atau Seret & Lepaskan ke sini</p>
                    <p style="font-size: 0.85rem; color: #6b7280;">Hanya menerima file PDF (Maks. 10MB)</p>
                    <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" style="display: none;">
                    <div id="fileInfo" style="margin-top: 15px; font-weight: 500; color: var(--primary); display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pengaturan Akses -->
        <div class="upload-col">
            <div class="card">
                <h3 class="card-title">Pengaturan Hak Akses</h3>
                
                <div class="form-group">
                    <label>Sifat Dokumen</label>
                    <div class="radio-group" style="display: flex; gap: 20px; margin-top: 10px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="access_type" id="access_public" value="public" <?php echo ($document['is_public'] == 1) ? 'checked' : ''; ?> style="margin-right: 5px;">
                            <span>Public (Terbuka)</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="access_type" id="access_private" value="private" <?php echo ($document['is_public'] == 0) ? 'checked' : ''; ?> style="margin-right: 5px;">
                            <span>Private (Terbatas)</span>
                        </label>
                    </div>
                </div>

                <!-- Bagian Hak Akses Multiple Choice -->
                <!-- Bagian Hak Akses Multiple Choice -->
                <div id="userSelectionArea" style="<?php echo ($document['is_public'] == 1) ? 'display: none;' : ''; ?> margin-top: 15px;">
                    <hr style="border: none; border-top: 1px solid var(--border-color); margin: 20px 0;">
                    <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 15px;">Pilih pihak yang diizinkan mengakses dokumen ini (selain rekan 1 departemen uploader).</p>
                    
                    <!-- Tab untuk Departemen -->
                    <div style="margin-bottom: 15px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Akses per Departemen</label>
                        <select id="dept_select" name="allowed_depts[]" multiple placeholder="Ketik nama departemen..." autocomplete="off">
                            <?php foreach ($other_depts as $d): ?>
                                <?php $isSelected = in_array($d['id'], $current_dept_access) ? 'selected' : ''; ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo $isSelected; ?>><?php echo htmlspecialchars($d['dept_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tab untuk User -->
                    <div style="margin-bottom: 15px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Akses per Pengguna Spesifik</label>
                        <select id="user_select" name="allowed_users[]" multiple placeholder="Ketik nama pengguna..." autocomplete="off">
                            <?php foreach ($users_by_dept as $deptName => $users): ?>
                                <optgroup label="<?php echo htmlspecialchars($deptName); ?>">
                                    <?php foreach ($users as $u): ?>
                                        <?php $isSelected = in_array($u['id'], $current_access) ? 'selected' : ''; ?>
                                        <option value="<?php echo $u['id']; ?>" <?php echo $isSelected; ?>><?php echo htmlspecialchars($u['username']) . ' (' . htmlspecialchars($u['role_name']) . ')'; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 1.05rem;">
                        <i class='bx bx-save'></i> Simpan Pembaruan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Initialize Tom Select
    let deptSelect, userSelect;
    document.addEventListener("DOMContentLoaded", function() {
        deptSelect = new TomSelect("#dept_select",{
            plugins: ['remove_button'],
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
        userSelect = new TomSelect("#user_select",{
            plugins: ['remove_button'],
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
    });

// Interaksi Pemilihan File
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('pdf_file');
const fileInfo = document.getElementById('fileInfo');

uploadArea.addEventListener('click', () => {
    fileInput.click();
});

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = 'var(--primary)';
    uploadArea.style.backgroundColor = '#f0fdf4';
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.style.borderColor = 'var(--border-color)';
    uploadArea.style.backgroundColor = 'transparent';
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = 'var(--border-color)';
    uploadArea.style.backgroundColor = 'transparent';
    
    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        handleFileSelection();
    }
});

fileInput.addEventListener('change', handleFileSelection);

function handleFileSelection() {
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.type !== 'application/pdf') {
            alert('Error: Hanya file PDF yang diizinkan!');
            fileInput.value = '';
            fileInfo.style.display = 'none';
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('Error: Ukuran file melebihi 10MB!');
            fileInput.value = '';
            fileInfo.style.display = 'none';
            return;
        }
        fileInfo.textContent = 'File Terpilih: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        fileInfo.style.display = 'block';
    }
}

// Logika Tampil/Sembunyi Daftar Akses
const radioPublic = document.getElementById('access_public');
const radioPrivate = document.getElementById('access_private');
const userSelectionArea = document.getElementById('userSelectionArea');

function toggleAccessArea() {
    if (radioPrivate.checked) {
        userSelectionArea.style.display = 'block';
    } else {
        userSelectionArea.style.display = 'none';
        // Clear selection bila pindah ke public agar data bersih
        if (deptSelect) deptSelect.clear();
        if (userSelect) userSelect.clear();
    }
}

radioPublic.addEventListener('change', toggleAccessArea);
radioPrivate.addEventListener('change', toggleAccessArea);
</script>

<?php include 'includes/footer.php'; ?>
