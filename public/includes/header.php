<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCS - Document Control System</title>
    <!-- Boxicons CDN for lightweight icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Sidebar (Disertakan sebelum main content agar flex berfungsi benar) -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <!-- Header / Topbar -->
        <header class="header">
            <button class="toggle-btn" id="sidebarToggle">
                <i class='bx bx-menu'></i>
            </button>

            <div class="header-right">
                <!-- Info Icon -->
                <button class="btn-icon" onclick="openModalTutorial()">
                    <i class='bx bx-info-circle'></i>
                </button>

                <div class="user-profile">
                    <i class='bx bx-user-circle' style="font-size: 1.5rem; color: var(--text-gray-500);"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?> (<?php echo htmlspecialchars($_SESSION['dept_name'] ?? ''); ?>)</span>
                </div>
                <a href="logout.php" class="btn-logout"><i class='bx bx-log-out'></i> Keluar</a>
            </div>
        </header>

        <!-- Content Area start -->
        <div class="content-wrapper">
            
            <!-- Warning Banner for Default Password -->
            <?php if(isset($_SESSION['is_password_changed']) && $_SESSION['is_password_changed'] == 0): ?>
            <div style="background-color: #fef3c7; color: #92400e; padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #f59e0b; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong><i class='bx bx-error' style="font-size: 1.2em; vertical-align: middle;"></i> Keamanan Akun:</strong> Anda masih menggunakan password bawaan/awal. Harap segera ubah password Anda demi keamanan.
                </div>
                <button onclick="openModalChangePassword()" style="background-color: #f59e0b; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold;">Ubah Password</button>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['pwd_success'])): ?>
                <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    <i class='bx bx-check-circle'></i> Password berhasil diubah!
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['pwd_error'])): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                    <i class='bx bx-error'></i> <?php echo htmlspecialchars($_GET['pwd_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Modal Ganti Password -->
            <div class="modal-overlay" id="modalChangePassword">
                <div class="modal-content" style="max-width: 400px; height: auto;">
                    <div class="modal-header">
                        <div class="modal-title">Ganti Password</div>
                        <button type="button" class="btn-close" onclick="closeModalChangePassword()"><i class='bx bx-x'></i></button>
                    </div>
                    <div class="modal-body" style="padding: 20px; background-color: var(--card-bg);">
                        <form action="../modules/user/ganti_password_proc.php" method="POST">
                            <div class="form-group">
                                <label>Password Lama <span class="text-danger">*</span></label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="form-group" style="margin-top: 15px;">
                                <label>Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="form-group" style="margin-top: 15px;">
                                <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn-primary w-full" style="margin-top: 20px;"><i class='bx bx-save'></i> Simpan Password Baru</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Tutorial -->
            <div class="modal-overlay" id="modalTutorial">
                <div class="modal-content" style="max-width: 800px; height: 80vh; display: flex; flex-direction: column;">
                    <div class="modal-header">
                        <div class="modal-title"><i class='bx bx-book-open'></i> Informasi & Tutorial Penggunaan</div>
                        <button type="button" class="btn-close" onclick="closeModalTutorial()"><i class='bx bx-x'></i></button>
                    </div>
                    <div class="modal-body" style="padding: 20px; overflow-y: auto; background-color: var(--card-bg); flex-grow: 1;">
                        <?php
                            $tutorial_path = dirname(__DIR__, 2) . '/docs/Tutorial Penggunaan.md';
                            $tutorial_content = file_exists($tutorial_path) ? file_get_contents($tutorial_path) : 'File tutorial tidak ditemukan.';
                        ?>
                        <div id="tutorialContent" style="line-height: 1.6; font-size: 14px;"></div>
                        <!-- Inject content safely for JS to read -->
                        <textarea id="rawTutorialContent" style="display:none;"><?php echo htmlspecialchars($tutorial_content); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Library Marked.js untuk parsing Markdown ke HTML -->
            <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
            <script>
                function openModalChangePassword() {
                    document.getElementById('modalChangePassword').classList.add('active');
                }
                function closeModalChangePassword() {
                    document.getElementById('modalChangePassword').classList.remove('active');
                }
                function openModalTutorial() {
                    const rawContent = document.getElementById('rawTutorialContent').value;
                    document.getElementById('tutorialContent').innerHTML = marked.parse(rawContent);
                    document.getElementById('modalTutorial').classList.add('active');
                }
                function closeModalTutorial() {
                    document.getElementById('modalTutorial').classList.remove('active');
                }
            </script>
