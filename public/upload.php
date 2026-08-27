<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

// Hanya Superadmin dan Admin yang boleh mengakses halaman ini
if ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin') {
    die("Akses ditolak. Anda tidak memiliki izin untuk mengunggah dokumen.");
}

$dept_id = $_SESSION['dept_id'];

// Ambil tahun yang berstatus aktif (jika ada flag aktif, karena di master data nanti ada status tutup periode, tapi saat ini master_data blm dibuat, jadi kita ambil semua dulu)
// Asumsi: Kita ambil semua tahun dari master
$years = $pdo->query("SELECT * FROM years WHERE status = 1 OR status IS NULL ORDER BY year_value DESC")->fetchAll();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Ambil daftar departemen (kecuali departemen uploader)
$stmt_depts = $pdo->prepare("SELECT * FROM departments WHERE id != ? ORDER BY dept_name ASC");
$stmt_depts->execute([$dept_id]);
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

?>
<?php include 'includes/header.php'; ?>

<!-- Tom Select CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-control { border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; }
    .ts-dropdown { border-radius: 0 0 6px 6px; }
</style>

<h1 class="page-title">Unggah Dokumen</h1>

<form id="uploadForm" action="../modules/document/upload_proc.php" method="POST" enctype="multipart/form-data">
    <div class="upload-grid">
        <!-- Kolom Kiri: Metadata & Berkas -->
        <div class="upload-col">
            <div class="card">
                <h3 class="card-title">Informasi Utama</h3>
                
                <div class="form-group">
                    <label for="doc_number">Nomor Dokumen <span class="text-danger">*</span></label>
                    <input type="text" id="doc_number" name="doc_number" class="form-control" required placeholder="Contoh: SOP-HRD-001">
                </div>

                <div class="form-group">
                    <label for="title">Judul Dokumen <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="Masukkan judul dokumen">
                </div>

                <div class="form-group-row">
                    <div class="form-group">
                        <label for="year_id">Tahun <span class="text-danger">*</span></label>
                        <select id="year_id" name="year_id" class="form-control" required>
                            <option value="">Pilih Tahun</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?php echo $y['id']; ?>"><?php echo $y['year_value']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="month_value">Bulan <span class="text-danger">*</span></label>
                        <select id="month_value" name="month_value" class="form-control" required>
                            <option value="">Pilih Bulan</option>
                            <?php foreach ($months as $k => $v): ?>
                                <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Berkas PDF <span class="text-danger">*</span></label>
                    <div class="dropzone" id="dropzone">
                        <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="file-input" required>
                        <div class="dropzone-text" id="dropzone-text">
                            <i class='bx bxs-file-pdf' style="font-size: 3rem; color: var(--text-gray-500); margin-bottom: 10px; display:block;"></i>
                            Klik atau Seret file PDF ke area ini<br>
                            <small class="text-gray-500">Maksimal ukuran: 10 MB</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Hak Akses -->
        <div class="upload-col">
            <div class="card">
                <h3 class="card-title">Pengaturan Hak Akses</h3>
                
                <div class="form-group">
                    <label>Sifat Dokumen <span class="text-danger">*</span></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="access_type" value="public" checked onchange="toggleAccessPanel()">
                            <span>🟢 Public (Dapat diakses semua divisi)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="access_type" value="private" onchange="toggleAccessPanel()">
                            <span>🔴 Private (Akses Terbatas)</span>
                        </label>
                    </div>
                </div>

                <div id="privatePanel" class="private-panel" style="display: none; margin-top: 15px;">
                    <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 15px;">Pilih pihak yang diizinkan mengakses dokumen ini (selain rekan 1 departemen Anda).</p>
                    
                    <!-- Tab untuk Departemen -->
                    <div style="margin-bottom: 15px;">
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Akses per Departemen</label>
                        <select id="dept_select" name="allowed_depts[]" multiple placeholder="Ketik nama departemen..." autocomplete="off">
                            <?php foreach ($other_depts as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?></option>
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
                                        <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']) . ' (' . htmlspecialchars($u['role_name']) . ')'; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 30px;">
                    <button type="submit" id="btnSubmit" class="btn-primary w-full" style="padding: 12px; font-size: 1rem;">
                        <i class='bx bx-cloud-upload'></i> Unggah Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Toggle Private Panel
    function toggleAccessPanel() {
        const isPrivate = document.querySelector('input[name="access_type"]:checked').value === 'private';
        const panel = document.getElementById('privatePanel');
        if(isPrivate) {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    }

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

    // File Input Logic (Dropzone)
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('pdf_file');
    const dropzoneText = document.getElementById('dropzone-text');

    dropzone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function() {
        handleFileSelection(this.files);
    });

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelection(e.dataTransfer.files);
        }
    });

    function handleFileSelection(files) {
        if (files.length > 0) {
            const file = files[0];
            // Validasi frontend
            if (file.type !== 'application/pdf') {
                alert('Format tidak didukung. Harap unggah file PDF.');
                fileInput.value = '';
                resetDropzone();
                return;
            }
            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('Ukuran berkas terlalu besar. Maksimal 10MB.');
                fileInput.value = '';
                resetDropzone();
                return;
            }
            
            const sizeMB = (file.size / (1024*1024)).toFixed(2);
            dropzoneText.innerHTML = `<i class='bx bxs-file-pdf' style="font-size: 3rem; color: var(--border-blue-500); margin-bottom: 10px; display:block;"></i>
                                      <strong style="color: var(--text-gray-900);">${file.name}</strong><br>
                                      <span style="color: var(--border-emerald-500); font-weight: 500;">${sizeMB} MB - Siap Diunggah</span>`;
            dropzone.style.borderColor = 'var(--border-blue-500)';
            dropzone.style.backgroundColor = '#eff6ff';
        }
    }

    function resetDropzone() {
        dropzoneText.innerHTML = `<i class='bx bxs-file-pdf' style="font-size: 3rem; color: var(--text-gray-500); margin-bottom: 10px; display:block;"></i>
                                Klik atau Seret file PDF ke area ini<br>
                                <small class="text-gray-500">Maksimal ukuran: 10 MB</small>`;
        dropzone.style.borderColor = '#d1d5db';
        dropzone.style.backgroundColor = 'transparent';
    }

    // Form Submit Handler
    const form = document.getElementById('uploadForm');
    const btnSubmit = document.getElementById('btnSubmit');

    form.addEventListener('submit', function(e) {
        // Prevent double submit
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<i class='bx bx-loader bx-spin'></i> Mengunggah...`;
    });
</script>

<?php include 'includes/footer.php'; ?>
