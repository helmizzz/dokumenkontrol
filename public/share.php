<?php
require_once __DIR__ . '/../config/database.php';
session_start();

if (!isset($_GET['token'])) {
    die("Token tidak ditemukan.");
}

$token = $_GET['token'];

// Cari data share
$stmt = $pdo->prepare("SELECT s.*, d.title, d.doc_number 
                       FROM document_shares s
                       JOIN documents d ON s.document_id = d.id
                       WHERE s.token = ?");
$stmt->execute([$token]);
$share = $stmt->fetch();

if (!$share) {
    die("Link tidak valid atau tidak ditemukan.");
}

// Cek kadaluarsa
if (!empty($share['expires_at'])) {
    $expires = strtotime($share['expires_at']);
    if (time() > $expires) {
        die("Maaf, link ini telah kadaluarsa pada " . date('d M Y H:i', $expires));
    }
}

// Cek apakah butuh password
$requires_password = !empty($share['password_hash']);
$is_unlocked = false;

if (isset($_SESSION['share_unlocked'][$token]) && $_SESSION['share_unlocked'][$token] === true) {
    $is_unlocked = true;
} elseif (!$requires_password) {
    $is_unlocked = true;
}

// Proses submit password
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], $share['password_hash'])) {
        $_SESSION['share_unlocked'][$token] = true;
        $is_unlocked = true;
        // Redirect agar form resubmission tidak muncul saat direfresh
        header("Location: share.php?token=" . urlencode($token));
        exit;
    } else {
        $error = "Password yang Anda masukkan salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Document - <?php echo htmlspecialchars($share['doc_number']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .share-container {
            width: 100%;
            max-width: 500px;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .viewer-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .viewer-header {
            background: #1f2937;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<?php if (!$is_unlocked): ?>
    <!-- Form Password -->
    <div class="share-container">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #111827; margin-bottom: 5px;">Dokumen Terproteksi</h2>
            <p style="color: #6b7280; font-size: 0.9rem;">
                <?php echo htmlspecialchars($share['doc_number'] . ' - ' . $share['title']); ?>
            </p>
        </div>
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="password">Masukkan Password Dokumen</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; padding: 10px;">Buka Dokumen</button>
        </form>
    </div>
<?php else: ?>
    <!-- PDF Viewer -->
    <div class="viewer-container">
        <div class="viewer-header">
            <h3 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($share['doc_number'] . ' - ' . $share['title']); ?></h3>
            <span style="font-size: 0.85rem; color: #9ca3af;">Secured Share Link</span>
        </div>
        <div style="flex: 1; background: #e5e7eb; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; z-index: 9999; opacity: 0.15;">
                <div style="transform: rotate(-45deg); font-size: 5rem; font-weight: bold; color: #000; text-align: center; white-space: nowrap;">
                    DOKUMEN KONTROL<br><span style="font-size: 2rem;">CONFIDENTIAL</span>
                </div>
            </div>
            <iframe src="share_stream.php?token=<?php echo urlencode($token); ?>#toolbar=0&navpanes=0&scrollbar=0" style="width: 100%; height: 100%; border: none;"></iframe>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
