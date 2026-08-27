<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak. Halaman ini khusus Superadmin.");
}

// Fetch Master Data for dropdowns
$roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments WHERE status = 1 ORDER BY dept_name ASC")->fetchAll();

// Fetch Users
$sql_users = "SELECT u.*, r.role_name, d.dept_name 
              FROM users u 
              LEFT JOIN roles r ON u.role_id = r.id 
              LEFT JOIN departments d ON u.dept_id = d.id 
              WHERE 1=1";

$params = [];
if (!empty($_GET['filter_dept'])) {
    $sql_users .= " AND u.dept_id = :dept_id";
    $params[':dept_id'] = $_GET['filter_dept'];
}

$sql_users .= " ORDER BY u.id DESC";
$stmt = $pdo->prepare($sql_users);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<h1 class="page-title">Manajemen User</h1>

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

<div class="card" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; padding: 15px 24px;">
    <h3 style="margin: 0;">Daftar Pengguna Sistem</h3>
    <button class="btn-primary" onclick="openModalUser('add')"><i class='bx bx-plus'></i> Tambah User</button>
</div>

<!-- Filter Panel -->
<form method="GET" action="user_management.php" class="filter-panel" style="margin-bottom: 20px; display: flex; gap: 15px; align-items: end;">
    <div class="form-group" style="flex: 1; margin-bottom: 0;">
        <label for="filter_dept" style="display: block; margin-bottom: 5px;">Filter Departemen</label>
        <select name="filter_dept" id="filter_dept" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Departemen</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?php echo $d['id']; ?>" <?php echo (($_GET['filter_dept'] ?? '') == $d['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($d['dept_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Departemen</th>
                <th>Level Akses</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach($users as $u): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><strong><?php echo htmlspecialchars($u['full_name'] ?? $u['username']); ?></strong></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['dept_name'] ?? 'Global (Semua Dept)'); ?></td>
                <td><?php echo htmlspecialchars($u['role_name']); ?></td>
                <td>
                    <?php if(!isset($u['status']) || $u['status'] == 1): ?>
                        <span class="badge badge-active">🟢 Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">🔴 Nonaktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-btns">
                        <!-- Edit Button -->
                        <button class="btn-icon" title="Edit Profil" onclick="openModalUser('edit', <?php echo htmlspecialchars(json_encode([
                            'id' => $u['id'],
                            'full_name' => $u['full_name'] ?? $u['username'],
                            'username' => $u['username'],
                            'role_id' => $u['role_id'],
                            'dept_id' => $u['dept_id'],
                            'status' => $u['status'] ?? 1
                        ])); ?>)">
                            <i class='bx bx-edit' style="color: var(--border-blue-500);"></i>
                        </button>
                        
                        <!-- Reset Password -->
                        <form method="POST" action="../modules/user/user_proc.php" style="display:inline;">
                            <input type="hidden" name="action" value="reset_pass">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn-icon" title="Reset Password" onclick="return confirm('Yakin mereset password ke \'Perusahaan123!\'?');">
                                <i class='bx bx-key' style="color: #f59e0b;"></i>
                            </button>
                        </form>

                        <!-- Toggle Status -->
                        <?php if ($u['id'] != $_SESSION['user_id']): // Jangan biarkan superadmin menonaktifkan dirinya sendiri ?>
                        <form method="POST" action="../modules/user/user_proc.php" style="display:inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $u['status'] ?? 1; ?>">
                            <?php if(!isset($u['status']) || $u['status'] == 1): ?>
                                <button type="submit" class="btn-icon" title="Nonaktifkan Karyawan"><i class='bx bx-user-x' style="color: #ef4444;"></i></button>
                            <?php else: ?>
                                <button type="submit" class="btn-icon" title="Aktifkan Karyawan"><i class='bx bx-user-check' style="color: #10b981;"></i></button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form User -->
<div class="modal-overlay" id="modalUser">
    <div class="modal-content" style="max-width: 500px; height: auto;">
        <div class="modal-header">
            <div class="modal-title" id="modalUserTitle">Tambah User Baru</div>
            <button type="button" class="btn-close" onclick="closeModalUser()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" style="padding: 20px; background-color: var(--card-bg);">
            <form action="../modules/user/user_proc.php" method="POST" id="userForm">
                <input type="hidden" name="action" id="formAction" value="add_user">
                <input type="hidden" name="user_id" id="userId" value="">

                <div class="form-group">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" id="full_name" class="form-control" required placeholder="Cth: Budi Santoso">
                </div>
                
                <div class="form-group" style="margin-top: 15px;">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" class="form-control" required placeholder="Cth: budi.hrd">
                </div>

                <div class="form-group" id="passwordGroup" style="margin-top: 15px;">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password awal untuk user">
                    <small id="passwordHelp" style="color: #6b7280; display: block; margin-top: 5px;">Password akan dikirimkan ke user untuk login pertama kali.</small>
                </div>

                <div class="form-group-row" style="margin-top: 15px;">
                    <div class="form-group">
                        <label>Level Akses (Role) <span class="text-danger">*</span></label>
                        <select name="role_id" id="role_id" class="form-control" required onchange="handleRoleChange()">
                            <option value="">Pilih Role</option>
                            <?php foreach($roles as $r): ?>
                                <option value="<?php echo $r['id']; ?>" data-name="<?php echo $r['role_name']; ?>"><?php echo $r['role_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="deptGroup">
                        <label>Departemen <span class="text-danger">*</span></label>
                        <select name="dept_id" id="dept_id" class="form-control" required>
                            <option value="">Pilih Departemen</option>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo $d['dept_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="statusGroup" style="display: none; margin-top: 15px;">
                    <label>Status Akun</label>
                    <div class="radio-group" style="flex-direction: row;">
                        <label class="radio-label">
                            <input type="radio" name="status" id="status_active" value="1"> 🟢 Aktif
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="status" id="status_inactive" value="0"> 🔴 Nonaktif
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full" style="margin-top: 20px;" id="btnSubmitUser">Simpan User</button>
            </form>
        </div>
    </div>
</div>

<script>
    function handleRoleChange() {
        const roleSelect = document.getElementById('role_id');
        const selectedRole = roleSelect.options[roleSelect.selectedIndex].getAttribute('data-name');
        const deptGroup = document.getElementById('deptGroup');
        const deptSelect = document.getElementById('dept_id');

        // Jika Superadmin, sembunyikan kolom departemen dan hapus wajib isinya
        if (selectedRole === 'Superadmin') {
            deptGroup.style.display = 'none';
            deptSelect.removeAttribute('required');
            deptSelect.value = ''; // Kosongkan
        } else {
            deptGroup.style.display = 'flex'; // Form group default display is flex
            deptSelect.setAttribute('required', 'required');
        }
    }

    function openModalUser(mode, data = null) {
        const modal = document.getElementById('modalUser');
        const title = document.getElementById('modalUserTitle');
        const formAction = document.getElementById('formAction');
        const btnSubmit = document.getElementById('btnSubmitUser');
        const passHelp = document.getElementById('passwordHelp');
        const statusGroup = document.getElementById('statusGroup');
        
        // Reset form
        document.getElementById('userForm').reset();
        
        if (mode === 'add') {
            title.textContent = 'Tambah User Baru';
            formAction.value = 'add_user';
            btnSubmit.innerHTML = '<i class="bx bx-user-plus"></i> Simpan User Baru';
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('password').setAttribute('required', 'required');
            statusGroup.style.display = 'none';
            document.getElementById('username').removeAttribute('readonly');
        } else if (mode === 'edit' && data) {
            title.textContent = 'Edit Profil User';
            formAction.value = 'edit_user';
            btnSubmit.innerHTML = '<i class="bx bx-save"></i> Perbarui Profil';
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('password').removeAttribute('required');
            statusGroup.style.display = 'block';
            
            // Isi data ke form
            document.getElementById('userId').value = data.id;
            document.getElementById('full_name').value = data.full_name;
            document.getElementById('username').value = data.username;
            document.getElementById('role_id').value = data.role_id;
            
            if (data.status == 1) document.getElementById('status_active').checked = true;
            else document.getElementById('status_inactive').checked = true;

            // Trigger role change logic sebelum set departemen
            handleRoleChange();
            if (data.dept_id) {
                document.getElementById('dept_id').value = data.dept_id;
            }
        }
        
        modal.classList.add('active');
    }

    function closeModalUser() {
        document.getElementById('modalUser').classList.remove('active');
    }
</script>

<?php include 'includes/footer.php'; ?>
