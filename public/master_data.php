<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak. Halaman ini khusus Superadmin.");
}

// Ambil data departments
$departments = $pdo->query("SELECT * FROM departments ORDER BY id DESC")->fetchAll();
// Ambil data years
$years = $pdo->query("SELECT * FROM years ORDER BY year_value DESC")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<h1 class="page-title">Master Data</h1>

<?php if (isset($_GET['success'])): ?>
    <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
        <i class='bx bx-check-circle'></i> Operasi berhasil dilakukan!
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
        <i class='bx bx-error'></i> <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<!-- Tabs Navigation -->
<div class="tabs">
    <button class="tab-btn active" onclick="openTab('tab-dept')"><i class='bx bx-buildings'></i> Master Departemen</button>
    <button class="tab-btn" onclick="openTab('tab-year')"><i class='bx bx-calendar'></i> Master Tahun</button>
</div>

<!-- TAB 1: DEPARTEMEN -->
<div id="tab-dept" class="tab-content" style="display: block;">
    <div class="card" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; padding: 15px 24px;">
        <h3 style="margin: 0;">Daftar Departemen</h3>
        <button class="btn-primary" onclick="openModalDept()"><i class='bx bx-plus'></i> Tambah Departemen</button>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Dept</th>
                    <th>Nama Departemen</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; foreach($departments as $d): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong><?php echo htmlspecialchars($d['dept_code'] ?? '-'); ?></strong></td>
                    <td><?php echo htmlspecialchars($d['dept_name']); ?></td>
                    <td>
                        <?php if(!isset($d['status']) || $d['status'] == 1): ?>
                            <span class="badge badge-active">🟢 Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-inactive">🔴 Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-icon" title="Edit Departemen" onclick="editDepartment(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars(addslashes($d['dept_name'])); ?>')"><i class='bx bx-edit' style="color: #3b82f6;"></i></button>
                            <form method="POST" action="../modules/master/master_proc.php" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_dept">
                                <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $d['status'] ?? 1; ?>">
                                <?php if(!isset($d['status']) || $d['status'] == 1): ?>
                                    <button type="submit" class="btn-icon" title="Nonaktifkan" onclick="return confirm('Yakin ingin menonaktifkan departemen ini?');"><i class='bx bx-block' style="color: #ef4444;"></i></button>
                                <?php else: ?>
                                    <button type="submit" class="btn-icon" title="Aktifkan"><i class='bx bx-check-circle' style="color: #10b981;"></i></button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- TAB 2: TAHUN -->
<div id="tab-year" class="tab-content" style="display: none;">
    <div class="card" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; padding: 15px 24px;">
        <h3 style="margin: 0;">Daftar Master Tahun</h3>
        <button class="btn-primary" onclick="openModalYear()"><i class='bx bx-plus'></i> Tambah Tahun</button>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tahun Arsip</th>
                    <th>Status Periode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; foreach($years as $y): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong><?php echo htmlspecialchars($y['year_value']); ?></strong></td>
                    <td>
                        <?php if(!isset($y['status']) || $y['status'] == 1): ?>
                            <span class="badge badge-active">🟢 Buka (Open)</span>
                        <?php else: ?>
                            <span class="badge badge-private">🔴 Tutup (Closed)</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <form method="POST" action="../modules/master/master_proc.php" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_year">
                                <input type="hidden" name="id" value="<?php echo $y['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $y['status'] ?? 1; ?>">
                                <?php if(!isset($y['status']) || $y['status'] == 1): ?>
                                    <button type="submit" class="btn-icon" title="Tutup Periode" onclick="return confirm('Yakin menutup periode ini? Tahun ini tidak akan bisa dipilih lagi saat mengunggah dokumen baru.');"><i class='bx bx-lock-alt' style="color: #ef4444;"></i></button>
                                <?php else: ?>
                                    <button type="submit" class="btn-icon" title="Buka Periode"><i class='bx bx-lock-open-alt' style="color: #10b981;"></i></button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Departemen -->
<div class="modal-overlay" id="modalDept">
    <div class="modal-content" style="max-width: 500px; height: auto;">
        <div class="modal-header">
            <div class="modal-title">Tambah Departemen</div>
            <button type="button" class="btn-close" onclick="closeModal('modalDept')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" style="padding: 20px; background-color: var(--card-bg);">
            <form action="../modules/master/master_proc.php" method="POST">
                <input type="hidden" name="action" value="add_dept">
                <div class="form-group">
                    <label>Kode Departemen</label>
                    <input type="text" name="dept_code" class="form-control" required placeholder="Cth: HRD" maxlength="10">
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Nama Departemen</label>
                    <input type="text" name="dept_name" class="form-control" required placeholder="Cth: Human Resources">
                </div>
                <button type="submit" class="btn-primary w-full" style="margin-top: 20px;">Simpan Departemen</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Tahun -->
<div class="modal-overlay" id="modalYear">
    <div class="modal-content" style="max-width: 400px; height: auto;">
        <div class="modal-header">
            <div class="modal-title">Tambah Tahun Arsip</div>
            <button type="button" class="btn-close" onclick="closeModal('modalYear')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" style="padding: 20px; background-color: var(--card-bg);">
            <form action="../modules/master/master_proc.php" method="POST">
                <input type="hidden" name="action" value="add_year">
                <div class="form-group">
                    <label>Tahun (4 Digit)</label>
                    <input type="text" name="year_value" class="form-control" required pattern="[0-9]{4}" placeholder="Cth: 2026">
                </div>
                <button type="submit" class="btn-primary w-full" style="margin-top: 20px;">Simpan Tahun</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openTab(tabId) {
        // Hide all tabs
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => tab.style.display = 'none');
        
        // Remove active class from buttons
        const btns = document.querySelectorAll('.tab-btn');
        btns.forEach(btn => btn.classList.remove('active'));
        
        // Show selected tab
        document.getElementById(tabId).style.display = 'block';
        
        // Add active class to clicked button
        event.currentTarget.classList.add('active');
    }

    function openModalDept() {
        document.getElementById('modalDept').classList.add('active');
    }
    
    function openModalYear() {
        document.getElementById('modalYear').classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function editDepartment(id, currentName) {
        const newName = prompt("Masukkan nama departemen baru:", currentName);
        if (newName !== null && newName.trim() !== "" && newName !== currentName) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('dept_name', newName.trim());
            
            fetch('../modules/department/edit_proc.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if(data.trim() === 'success') {
                    window.location.href = "master_data.php?success=1";
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
