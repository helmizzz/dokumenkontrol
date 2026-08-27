<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

if ($_SESSION['role_name'] !== 'Superadmin') {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    try {
        if ($action === 'add_dept') {
            $dept_code = strtoupper(trim($_POST['dept_code']));
            $dept_name = trim($_POST['dept_name']);
            
            $stmt = $pdo->prepare("INSERT INTO departments (dept_code, dept_name, status) VALUES (?, ?, 1)");
            $stmt->execute([$dept_code, $dept_name]);
            write_audit_log($pdo, $user_id, 'CREATE', 'Departemen', "Menambahkan departemen baru: $dept_code - $dept_name");
            
            header("Location: ../../public/master_data.php?success=1");
            exit;
        } 
        elseif ($action === 'add_year') {
            $year_value = trim($_POST['year_value']);
            
            // Cek duplikasi
            $check = $pdo->prepare("SELECT id FROM years WHERE year_value = ?");
            $check->execute([$year_value]);
            if ($check->fetch()) {
                header("Location: ../../public/master_data.php?error=" . urlencode("Tahun sudah terdaftar di sistem!"));
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO years (year_value, status) VALUES (?, 1)");
            $stmt->execute([$year_value]);
            write_audit_log($pdo, $user_id, 'CREATE', 'Tahun', "Menambahkan master tahun baru: $year_value");
            
            header("Location: ../../public/master_data.php?success=1");
            exit;
        }
        elseif ($action === 'toggle_dept') {
            $id = $_POST['id'];
            $current = $_POST['current_status'];
            $new_status = ($current == 1) ? 0 : 1;
            $status_text = ($new_status == 1) ? "Aktif" : "Nonaktif";

            $stmt = $pdo->prepare("UPDATE departments SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            write_audit_log($pdo, $user_id, 'UPDATE', 'Departemen', "Mengubah status departemen ID $id menjadi $status_text");
            
            header("Location: ../../public/master_data.php?success=1");
            exit;
        }
        elseif ($action === 'toggle_year') {
            $id = $_POST['id'];
            $current = $_POST['current_status'];
            $new_status = ($current == 1) ? 0 : 1;
            $status_text = ($new_status == 1) ? "Buka (Open)" : "Tutup (Closed)";

            $stmt = $pdo->prepare("UPDATE years SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            write_audit_log($pdo, $user_id, 'UPDATE', 'Tahun', "Mengubah status periode Tahun ID $id menjadi $status_text");
            
            header("Location: ../../public/master_data.php?success=1");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: ../../public/master_data.php?error=" . urlencode("Gagal memproses data: " . $e->getMessage()));
        exit;
    }
}
header("Location: ../../public/master_data.php");
exit;
?>
