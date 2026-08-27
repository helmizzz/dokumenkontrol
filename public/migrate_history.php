<?php
require_once __DIR__ . '/../config/database.php';

try {
    // 1. Buat tabel document_revisions
    $sql = "CREATE TABLE IF NOT EXISTS document_revisions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        revision_number INT NOT NULL DEFAULT 0,
        created_by INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    // SQLite syntax compatibility fallback (if using SQLite in local)
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $sql = "CREATE TABLE IF NOT EXISTS document_revisions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            document_id INTEGER NOT NULL,
            file_path TEXT NOT NULL,
            revision_number INTEGER NOT NULL DEFAULT 0,
            created_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        );";
    }

    $pdo->exec($sql);
    echo "Tabel document_revisions berhasil dibuat atau sudah ada.\n";

    // 2. Buat folder storage/document_revisions
    $dir = __DIR__ . '/../storage/document_revisions';
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "Folder storage/document_revisions berhasil dibuat.\n";
        } else {
            echo "Gagal membuat folder storage/document_revisions. Cek permission.\n";
        }
    } else {
        echo "Folder storage/document_revisions sudah ada.\n";
    }

} catch (PDOException $e) {
    echo "Terjadi kesalahan database: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Terjadi kesalahan: " . $e->getMessage() . "\n";
}
?>
