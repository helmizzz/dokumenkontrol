<?php
function write_audit_log($pdo, $user_id, $action_type, $module, $description) {
    // Memastikan hanya mencatat modifikasi data (CREATE, UPDATE, DELETE) serta aktivitas VIEW
    $allowed_actions = ['CREATE', 'UPDATE', 'DELETE', 'VIEW'];
    if (!in_array(strtoupper($action_type), $allowed_actions)) {
        return false;
    }

    $sql = "INSERT INTO activity_logs (user_id, action_type, module, description) 
            VALUES (:user_id, :action_type, :module, :description)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':user_id' => $user_id,
        ':action_type' => strtoupper($action_type),
        ':module' => $module,
        ':description' => $description
    ]);
}
?>
