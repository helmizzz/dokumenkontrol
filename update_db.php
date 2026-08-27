<?php
require_once __DIR__ . '/config/database.php';

try {
    // Check if column exists first (optional, but good practice)
    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_password_changed'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_password_changed` TINYINT(1) DEFAULT 0 AFTER `password`");
        echo "Column added.\n";
    }

    // Set Superadmin (role_id = 1) to is_password_changed = 1
    $pdo->exec("UPDATE `users` SET `is_password_changed` = 1 WHERE `role_id` = 1");
    echo "Superadmin updated.\n";
    
    echo "Database successfully updated.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
