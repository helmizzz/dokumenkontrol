<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT u.*, r.role_name, d.dept_name 
                                   FROM users u 
                                   LEFT JOIN roles r ON u.role_id = r.id 
                                   LEFT JOIN departments d ON u.dept_id = d.id 
                                   WHERE u.username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name'];
                $_SESSION['dept_id'] = $user['dept_id'];
                $_SESSION['dept_name'] = $user['dept_name'];
                $_SESSION['is_password_changed'] = $user['is_password_changed'] ?? 1;

                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
    
    // Jika gagal, kembali ke form login dengan pesan error
    header("Location: index.php?error=1");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
