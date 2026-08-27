<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $admin_id = $_SESSION['user_id'];

    try {
        if ($action === 'add_user') {
            $full_name = trim($_POST['full_name']);
            $username = trim($_POST['username']);
            $role_id = $_POST['role_id'];
            $dept_id = empty($_POST['dept_id']) ? NULL : $_POST['dept_id'];
            
            // Cek username kembar
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$username]);
            if ($check->fetch()) {
                header("Location: ../../public/user_management.php?error=" . urlencode("Username sudah terdaftar!"));
                exit;
            }

            // Gunakan password yang diinput, default ke Perusahaan123! jika kosong
            $raw_password = !empty($_POST['password']) ? $_POST['password'] : 'Perusahaan123!';
            $password = password_hash($raw_password, PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role_id, dept_id, status, is_password_changed) VALUES (?, ?, ?, ?, ?, 1, 0)");
            $stmt->execute([$full_name, $username, $password, $role_id, $dept_id]);
            write_audit_log($pdo, $admin_id, 'CREATE', 'User Management', "Mendaftarkan user baru: $username ($full_name)");
            
            header("Location: ../../public/user_management.php?success=1");
            exit;
        } 
        elseif ($action === 'edit_user') {
            $id = $_POST['user_id'];
            $full_name = trim($_POST['full_name']);
            $username = trim($_POST['username']);
            $role_id = $_POST['role_id'];
            $dept_id = empty($_POST['dept_id']) ? NULL : $_POST['dept_id'];
            $status = $_POST['status'];

            // Cek username kembar tapi abaikan user ini sendiri
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->execute([$username, $id]);
            if ($check->fetch()) {
                header("Location: ../../public/user_management.php?error=" . urlencode("Username sudah dipakai orang lain!"));
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role_id = ?, dept_id = ?, status = ? WHERE id = ?");
            $stmt->execute([$full_name, $username, $role_id, $dept_id, $status, $id]);
            write_audit_log($pdo, $admin_id, 'UPDATE', 'User Management', "Memperbarui profil user ID: $id ($username)");
            
            header("Location: ../../public/user_management.php?success=1");
            exit;
        }
        elseif ($action === 'toggle_status') {
            $id = $_POST['id'];
            $current = $_POST['current_status'];
            $new_status = ($current == 1) ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            
            $action_desc = ($new_status == 1) ? "Mengaktifkan kembali" : "Menonaktifkan";
            write_audit_log($pdo, $admin_id, 'UPDATE', 'User Management', "$action_desc akun user ID: $id");
            
            header("Location: ../../public/user_management.php?success=1");
            exit;
        }
        elseif ($action === 'reset_pass') {
            $id = $_POST['id'];
            $password = password_hash('Perusahaan123!', PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ?, is_password_changed = 0 WHERE id = ?");
            $stmt->execute([$password, $id]);
            
            write_audit_log($pdo, $admin_id, 'UPDATE', 'User Management', "Mereset password akun user ID: $id");
            
            header("Location: ../../public/user_management.php?success=1");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: ../../public/user_management.php?error=" . urlencode("Gagal memproses: " . $e->getMessage()));
        exit;
    }
}
header("Location: ../../public/user_management.php");
exit;
?>
