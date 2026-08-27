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
    $dept_code = trim($_POST['dept_code'] ?? '');
    $dept_name = trim($_POST['dept_name'] ?? '');

    if (empty($id) || empty($dept_code) || empty($dept_name)) {
        die("Data tidak lengkap.");
    }

    try {
        // Cek kode kembar
        $stmtCheckCode = $pdo->prepare("SELECT id FROM departments WHERE dept_code = ? AND id != ?");
        $stmtCheckCode->execute([$dept_code, $id]);
        if ($stmtCheckCode->fetch()) {
            die("Kode departemen sudah digunakan oleh departemen lain.");
        }

        // Cek nama kembar
        $stmtCheckName = $pdo->prepare("SELECT id FROM departments WHERE dept_name = ? AND id != ?");
        $stmtCheckName->execute([$dept_name, $id]);
        if ($stmtCheckName->fetch()) {
            die("Nama departemen sudah digunakan oleh departemen lain.");
        }

        // Dapatkan data lama
        $stmtOld = $pdo->prepare("SELECT dept_code, dept_name FROM departments WHERE id = ?");
        $stmtOld->execute([$id]);
        $oldDept = $stmtOld->fetch();
        if (!$oldDept) {
            die("Departemen tidak ditemukan.");
        }

        // Lakukan pembaruan
        $stmtUpdate = $pdo->prepare("UPDATE departments SET dept_code = ?, dept_name = ? WHERE id = ?");
        if ($stmtUpdate->execute([$dept_code, $dept_name, $id])) {
            // Log aktivitas
            $changes = [];
            $oldCode = $oldDept['dept_code'] ?? '-';
            if ($oldCode !== $dept_code) $changes[] = "Kode dari '{$oldCode}' menjadi '{$dept_code}'";
            if ($oldDept['dept_name'] !== $dept_name) $changes[] = "Nama dari '{$oldDept['dept_name']}' menjadi '{$dept_name}'";
            
            if (!empty($changes)) {
                $logMsg = "Mengubah data departemen: " . implode(', ', $changes);
                write_audit_log($pdo, $_SESSION['user_id'], 'UPDATE', 'Master Departemen', $logMsg);
            }
            
            echo "success";
        } else {
            echo "Gagal memperbarui departemen.";
        }
    } catch (PDOException $e) {
        die("Error DB: " . $e->getMessage());
    }
}
?>
