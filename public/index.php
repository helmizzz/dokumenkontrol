<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Document Control System</title>
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --bg-color: #f3f4f6;
            --text-color: #1f2937;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --error-color: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: var(--text-color);
        }

        .login-box {
            background-color: var(--card-bg);
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .login-header {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .login-header p {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 8px;
        }

        .login-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .error-message {
            background-color: #fee2e2;
            color: var(--error-color);
            padding: 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            margin-bottom: 16px;
            text-align: center;
            display: none;
        }

        <?php if(isset($_GET['error'])): ?>
        .error-message {
            display: block;
        }
        <?php endif; ?>

        /* --- Tambahan: Dekorasi Landing Page --- */
        
        .login-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .brand-logo {
            margin-top: 30px;
            width: 40vw;
            max-width: 250px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .grass-container {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 8vw;
            min-height: 40px;
            max-height: 100px;
            background-image: url('assets/img/grass.png'); /* Pastikan file ini ada */
            background-size: contain;
            background-repeat: repeat-x;
            background-position: bottom;
            z-index: 1;
        }


    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-header">
                <h1>DCS Portal</h1>
                <p>Document Control System</p>
            </div>
            <div class="login-body">
                <div class="error-message">
                    Username atau Password salah!
                </div>
                <form action="login_proc.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn-primary">Masuk</button>
                </form>
            </div>
        </div>



        <!-- Logo Perusahaan -->
        <img src="assets/img/logo.png" alt="Berdikari Meubel Nusantara" class="brand-logo" onerror="this.style.display='none'">
    </div>

    <!-- Rumput di bagian bawah layar -->
    <div class="grass-container"></div>

</body>
</html>
