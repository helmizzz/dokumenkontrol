<?php
// core/auth_middleware.php
session_start();

function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /dokumenkontrol/public/index.php");
        exit;
    }
}

function check_role($allowed_roles) {
    if (!isset($_SESSION['role_name']) || !in_array($_SESSION['role_name'], $allowed_roles)) {
        die("Akses Ditolak: Anda tidak memiliki izin untuk halaman ini.");
    }
}
?>
