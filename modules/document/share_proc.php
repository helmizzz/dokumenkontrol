<?php
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/core/logger.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['role_name']) || ($_SESSION['role_name'] !== 'Superadmin' && $_SESSION['role_name'] !== 'Admin')) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_id = (int)$_POST['doc_id'];
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $expires_at = isset($_POST['expires_at']) ? trim($_POST['expires_at']) : '';
    $user_id = (int)$_SESSION['user_id'];

    if (empty($doc_id)) {
        echo json_encode(['success' => false, 'message' => 'ID Dokumen tidak valid.']);
        exit;
    }

    try {
        // Generate secure random token
        $token = bin2hex(random_bytes(16));
        
        $password_hash = null;
        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
        }

        $expires_at_db = null;
        if ($expires_at !== '') {
            // Format HTML datetime-local is YYYY-MM-DDTHH:MM, convert to YYYY-MM-DD HH:MM:SS
            $expires_at_db = date('Y-m-d H:i:s', strtotime($expires_at));
        }

        $sql = "INSERT INTO document_shares (document_id, token, password_hash, expires_at, created_by) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$doc_id, $token, $password_hash, $expires_at_db, $user_id]);

        // Catat di Audit Trail
        write_audit_log($pdo, $user_id, 'SHARE', 'Dokumen', "Membuat share link untuk dokumen ID $doc_id. Token: $token");

        // Construct full URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname(dirname(dirname($_SERVER['REQUEST_URI']))); 
        // e.g. /dokumenkontrol
        
        $share_link = $protocol . $host . $path . '/public/share.php?token=' . $token;

        echo json_encode([
            'success' => true,
            'link' => $share_link
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
