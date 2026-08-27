<?php
require_once __DIR__ . '/../../core/auth_middleware.php';
require_once __DIR__ . '/../../config/database.php';
check_auth();

// Hanya Superadmin yang berhak menambah/mengedit Master Data Departemen
if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak. Hanya Superadmin yang dapat mengedit departemen.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $dept_name = trim($_POST['dept_name'] ?? '');

    if (empty($id) || empty($dept_name)) {
        die("Data tidak lengkap.");
    }

    try {
        // Cek nama kembar
        $stmtCheck = $pdo->prepare("SELECT id FROM departments WHERE dept_name = ? AND id != ?");
        $stmtCheck->execute([$dept_name, $id]);
        if ($stmtCheck->fetch()) {
            die("Nama departemen sudah digunakan oleh departemen lain.");
        }

        // Dapatkan nama lama
        $stmtOld = $pdo->prepare("SELECT dept_name FROM departments WHERE id = ?");
        $stmtOld->execute([$id]);
        $oldDept = $stmtOld->fetch();
        if (!$oldDept) {
            die("Departemen tidak ditemukan.");
        }

        // Lakukan pembaruan
        $stmtUpdate = $pdo->prepare("UPDATE departments SET dept_name = ? WHERE id = ?");
        if ($stmtUpdate->execute([$dept_name, $id])) {
            // Log aktivitas
            write_audit_log($pdo, $_SESSION['user_id'], 'UPDATE', 'Master Departemen', "Mengubah nama departemen dari '{$oldDept['dept_name']}' menjadi '{$dept_name}'");
            
            echo "success";
        } else {
            echo "Gagal memperbarui departemen.";
        }
    } catch (PDOException $e) {
        die("Error DB: " . $e->getMessage());
    }
}
?>
