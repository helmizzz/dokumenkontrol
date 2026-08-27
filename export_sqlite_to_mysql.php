<?php
$sqliteDbPath = __DIR__ . '/storage/database.sqlite';
$exportPath = __DIR__ . '/storage/database_export.sql';

if (!file_exists($sqliteDbPath)) {
    die("Error: File $sqliteDbPath tidak ditemukan.\n");
}

try {
    $pdo = new PDO('sqlite:' . $sqliteDbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $sqlOutput = "";
    $sqlOutput .= "-- Hasil Export dari SQLite ke MySQL\n";
    $sqlOutput .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            $sqlOutput .= "-- Dumping data for table `$table`\n";
            $sqlOutput .= "TRUNCATE TABLE `$table`;\n";
            
            foreach ($rows as $row) {
                $columns = array_keys($row);
                $values = array_values($row);
                
                $columnsStr = implode("`, `", $columns);
                
                $valuesEscaped = array_map(function($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote($val);
                }, $values);
                
                $valuesStr = implode(", ", $valuesEscaped);
                
                $sqlOutput .= "INSERT INTO `$table` (`$columnsStr`) VALUES ($valuesStr);\n";
            }
            $sqlOutput .= "\n";
        }
    }
    
    $sqlOutput .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    file_put_contents($exportPath, $sqlOutput);
    echo "Export berhasil! File disimpan di: $exportPath\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
