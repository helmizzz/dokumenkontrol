<?php
// init_db.php
require_once __DIR__ . '/config/database.php';

echo "Memulai inisialisasi database MySQL...<br>";

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dept_name VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS years (
        id INT AUTO_INCREMENT PRIMARY KEY,
        year_value INT NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role_id INT,
        dept_id INT,
        FOREIGN KEY(role_id) REFERENCES roles(id) ON DELETE SET NULL,
        FOREIGN KEY(dept_id) REFERENCES departments(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doc_number VARCHAR(100) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        is_public TINYINT(1) DEFAULT 0,
        status TINYINT(1) DEFAULT 1,
        year_id INT,
        month_value INT NOT NULL,
        dept_id INT,
        uploaded_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY(year_id) REFERENCES years(id) ON DELETE SET NULL,
        FOREIGN KEY(dept_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY(uploaded_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS document_access (
        id INT AUTO_INCREMENT PRIMARY KEY,
        document_id INT,
        user_id INT,
        FOREIGN KEY(document_id) REFERENCES documents(id) ON DELETE CASCADE,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action_type VARCHAR(10) NOT NULL,
        module VARCHAR(50) NOT NULL,
        description TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Struktur tabel berhasil dibuat!<br>";

    $stmt = $pdo->query("SELECT COUNT(*) FROM roles");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO roles (role_name) VALUES ('Superadmin'), ('Admin'), ('User')");
        echo "Data roles berhasil disisipkan.<br>";
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM departments");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO departments (dept_name) VALUES ('IT'), ('HRD'), ('Finance')");
        echo "Data departments berhasil disisipkan.<br>";
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (username, password, role_id, dept_id) VALUES ('admin', '$password', 1, 1)");
        echo "Data user default berhasil disisipkan (Username: admin, Password: admin123).<br>";
    }

    echo "<br><b>Inisialisasi MySQL selesai!</b> Anda bisa menghapus script ini untuk keamanan.";
} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}
?>
