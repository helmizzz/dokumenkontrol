<?php
// config/database.php
// Supports MySQL in production and SQLite for local development.
// Set these variables in your web server environment or shell before running the app:
// DB_DRIVER, DB_HOST, DB_PORT, DB_NAME, DB_USERNAME, DB_PASSWORD

$db_file = __DIR__ . '/../storage/database.sqlite';

try {
    $driver = getenv('DB_DRIVER') ?: 'mysql';
    $hosts = array_filter(explode(',', getenv('DB_HOST') ?: 'localhost'));
    $ports = array_filter(explode(',', getenv('DB_PORT') ?: '3306'));
    $dbname = getenv('DB_NAME') ?: 'documentcontrol';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';

    $availableDrivers = PDO::getAvailableDrivers();

    if ($driver === 'sqlite') {
        if (!in_array('sqlite', $availableDrivers, true)) {
            throw new PDOException('PDO SQLite driver is not installed.');
        }

        $dsn = 'sqlite:' . $db_file;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        if (!in_array('mysql', $availableDrivers, true)) {
            throw new PDOException('PDO MySQL driver is not installed. Enable extension=pdo_mysql in php.ini.');
        }

        $lastError = null;
        foreach ($hosts as $hostCandidate) {
            foreach ($ports as $portCandidate) {
                try {
                    $dsn = "mysql:host=$hostCandidate;port=$portCandidate;dbname=$dbname;charset=utf8mb4";
                    $pdo = new PDO($dsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    $pdo->query('SELECT 1');
                    break 2;
                } catch (PDOException $e) {
                    // Cek jika error adalah "Unknown database" (kode 1049)
                    if ($e->getCode() == 1049) {
                        try {
                            // Koneksi tanpa nama database
                            $dsn_setup = "mysql:host=$hostCandidate;port=$portCandidate;charset=utf8mb4";
                            $pdo_setup = new PDO($dsn_setup, $username, $password, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                            ]);
                            
                            // Buat database secara otomatis
                            $pdo_setup->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            
                            // Koneksi ulang dengan database yang baru dibuat
                            $pdo = new PDO($dsn, $username, $password, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false,
                            ]);
                            
                            break 2; // Berhasil, keluar dari loop
                        } catch (PDOException $ex) {
                            $lastError = $ex;
                        }
                    } else {
                        $lastError = $e;
                    }
                }
            }
        }

        if (!isset($pdo)) {
            throw $lastError ?: new PDOException('Tidak dapat terhubung ke server MySQL.');
        }
    }
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>
