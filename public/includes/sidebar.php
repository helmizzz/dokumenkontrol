<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role_name'] ?? 'User';
?>
<aside class="sidebar" id="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <i class='bx bxs-file-pdf'></i>
        <span class="brand-text">DCS Portal</span>
    </a>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class='bx bxs-dashboard'></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li>
            <a href="documents.php" class="<?php echo $current_page == 'documents.php' ? 'active' : ''; ?>">
                <i class='bx bx-folder-open'></i>
                <span>Repositori Dokumen</span>
            </a>
        </li>
        <li>
            <a href="recycle_bin.php" class="<?php echo $current_page == 'recycle_bin.php' ? 'active' : ''; ?>">
                <i class='bx bx-trash'></i>
                <span>Recycle Bin</span>
            </a>
        </li>

        <?php if ($role === 'Superadmin' || $role === 'Admin'): ?>
        <li>
            <a href="upload.php" class="<?php echo $current_page == 'upload.php' ? 'active' : ''; ?>">
                <i class='bx bx-cloud-upload'></i>
                <span>Unggah Dokumen</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role === 'Superadmin'): ?>
        <li>
            <a href="master_data.php" class="<?php echo strpos($current_page, 'master_') !== false ? 'active' : ''; ?>">
                <i class='bx bx-data'></i>
                <span>Master Data</span>
            </a>
        </li>
        <li>
            <a href="user_management.php" class="<?php echo $current_page == 'user_management.php' ? 'active' : ''; ?>">
                <i class='bx bx-user-pin'></i>
                <span>Manajemen User</span>
            </a>
        </li>
        <li>
            <a href="logs.php" class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
                <i class='bx bx-history'></i>
                <span>Audit Trail</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>
