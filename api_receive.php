<?php
header('Content-Type: application/json');

// Koneksi ke MySQL LAMPP (User: root, Password: "")
$db = new mysqli('localhost', 'root', '', 'line_monitor');

if ($db->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$machine_id = isset($input['machine_id']) ? $db->real_escape_string($input['machine_id']) : null;
$count = isset($input['count']) ? (int)$input['count'] : 0;

if ($machine_id) {
    $stmt = $db->prepare("INSERT INTO machine_status (machine_id, last_signal, count_val) 
                          VALUES (?, NOW(), ?) 
                          ON DUPLICATE KEY UPDATE last_signal = NOW(), count_val = ?");
    $stmt->bind_param("sii", $machine_id, $count, $count);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Machine ID']);
}
?>
