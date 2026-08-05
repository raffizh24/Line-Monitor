<?php
header('Content-Type: application/json');

// Catat semua error PHP ke log
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Koneksi ke Database
$db = new mysqli('localhost', 'root', '', 'seid_ac_line-monitor');

if ($db->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $db->connect_error]);
    exit;
}

// Ambil input JSON dari Raspberry Pi
$input = json_decode(file_get_contents('php://input'), true);

$machine_id = isset($input['machine_id']) ? $db->real_escape_string($input['machine_id']) : null;
$pulse_count = isset($input['count']) ? (int)$input['count'] : 0;
$periode = date('Y-m');

if ($machine_id && $pulse_count > 0) {
    $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, total_qty, total_work_seconds) 
            VALUES ('$machine_id', '$periode', NOW(), 1, $pulse_count) 
            ON DUPLICATE KEY UPDATE 
                last_signal = NOW(), 
                total_qty = total_qty + 1, 
                total_work_seconds = total_work_seconds + $pulse_count";

    if ($db->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Data updated']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Query Failed: ' . $db->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Data Received', 'received' => $input]);
}
