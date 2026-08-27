<?php
require_once __DIR__ . '/../core/auth_middleware.php';
check_auth(); // Pastikan user sudah login

require_once __DIR__ . '/../config/database.php';

$role = $_SESSION['role_name'] ?? '';
$dept_id = $_SESSION['dept_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

$total_docs = 0;
$public_docs = 0;
$private_docs = 0;
$dept_docs = 0;

try {
    // Dokumen Departemen Anda (Sama untuk semua role: Total dokumen di departemen user saat ini)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE dept_id = ?");
    $stmt->execute([$dept_id]);
    $dept_docs = $stmt->fetchColumn();

    if ($role === 'Superadmin') {
        // Superadmin: Akses ke seluruh dokumen di sistem
        $total_docs = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
        $public_docs = $pdo->query("SELECT COUNT(*) FROM documents WHERE is_public = 1")->fetchColumn();
        $private_docs = $pdo->query("SELECT COUNT(*) FROM documents WHERE is_public = 0")->fetchColumn();
    } elseif ($role === 'Admin') {
        // Admin: Akses ke semua dokumen publik & privat di departemennya saja
        $total_docs = $dept_docs;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE dept_id = ? AND is_public = 1");
        $stmt->execute([$dept_id]);
        $public_docs = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE dept_id = ? AND is_public = 0");
        $stmt->execute([$dept_id]);
        $private_docs = $stmt->fetchColumn();
    } else {
        // User: Akses ke dokumen publik di departemennya, dan HANYA dokumen privat yang dia upload sendiri
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE dept_id = ? AND is_public = 1");
        $stmt->execute([$dept_id]);
        $public_docs = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE uploaded_by = ? AND is_public = 0");
        $stmt->execute([$user_id]);
        $private_docs = $stmt->fetchColumn();

        $total_docs = $public_docs + $private_docs;
    }
} catch (PDOException $e) {
    // Fallback jika terjadi error database
    $total_docs = $public_docs = $private_docs = $dept_docs = 0;
}
?>

<?php include 'includes/header.php'; ?>

<h1 class="page-title">Dashboard Utama</h1>

<div class="grid-cards">
    <div class="stat-card">
        <div class="stat-icon"><i class='bx bxs-file-pdf'></i></div>
        <div class="stat-details">
            <h3><?php echo $total_docs; ?></h3>
            <p>Total Dokumen</p>
        </div>
    </div>

    <div class="stat-card blue">
        <div class="stat-icon" style="color: var(--border-blue-500);"><i class='bx bx-world'></i></div>
        <div class="stat-details">
            <h3><?php echo $public_docs; ?></h3>
            <p>Dokumen Publik</p>
        </div>
    </div>

    <div class="stat-card" style="border-top-color: #f59e0b;">
        <div class="stat-icon" style="color: #f59e0b;"><i class='bx bxs-lock-alt'></i></div>
        <div class="stat-details">
            <h3><?php echo $private_docs; ?></h3>
            <p>Dokumen Privat</p>
        </div>
    </div>

    <div class="stat-card" style="border-top-color: #8b5cf6;">
        <div class="stat-icon" style="color: #8b5cf6;"><i class='bx bxs-buildings'></i></div>
        <div class="stat-details">
            <h3><?php echo $dept_docs; ?></h3>
            <p>Dokumen Departemen</p>
        </div>
    </div>
</div>

<div style="background: var(--card-bg); padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h2 style="font-size: 1.25rem; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
        <i class='bx bx-info-circle' style="color: var(--border-blue-500); margin-right: 8px;"></i> Informasi Sesi
    </h2>
    <p style="margin-bottom: 10px;"><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
    <p style="margin-bottom: 10px;"><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['role_name'] ?? ''); ?></p>
    <p><strong>Departemen:</strong> <?php echo htmlspecialchars($_SESSION['dept_name'] ?? ''); ?></p>
</div>

<?php include 'includes/footer.php'; ?>
