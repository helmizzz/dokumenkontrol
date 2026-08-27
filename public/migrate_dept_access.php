<?php
require_once __DIR__ . '/../config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS document_dept_access (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT NOT NULL,
        dept_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
        FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    // SQLite syntax compatibility fallback (if using SQLite in local)
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $sql = "CREATE TABLE IF NOT EXISTS document_dept_access (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            document_id INTEGER NOT NULL,
            dept_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
            FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE CASCADE
        );";
    }

    $pdo->exec($sql);
    echo "Tabel document_dept_access berhasil dibuat atau sudah ada.\n";

} catch (PDOException $e) {
    echo "Terjadi kesalahan database: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Terjadi kesalahan: " . $e->getMessage() . "\n";
}
?>
