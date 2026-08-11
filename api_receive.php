<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Koneksi ke Database
$db = new mysqli('localhost', 'root', '', 'seid_ac_line-monitor');

if ($db->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $db->connect_error]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$machine_id    = isset($input['machine_id']) ? $db->real_escape_string($input['machine_id']) : null;
$status        = isset($input['status']) ? strtoupper($input['status']) : null;
$stop_duration = isset($input['stop_duration']) ? (int)$input['stop_duration'] : 0;
$periode       = date('Y-m');

if ($machine_id) {

    // 1. JIKA SINYAL DARI ESP32 DENGAN STATUS "OFF" (Kirim hasil durasi stop)
    if ($status === 'OFF') {
        // Update last_signal menjadi NOW() dan akumulasikan durasi stop (misal ke kolom total_stop_seconds)
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, total_stop_seconds) 
                VALUES ('$machine_id', '$periode', NOW(), $stop_duration) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(), 
                    total_stop_seconds = total_stop_seconds + $stop_duration";
    }
    // 2. JIKA SINYAL DARI RASPBERRY PI (Hanya machine_id) ATAU STATUS "ON"
    else {
        // Otomatis menambah total_qty + 1 dan update last_signal ke NOW()
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, total_qty) 
                VALUES ('$machine_id', '$periode', NOW(), 1) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(), 
                    total_qty = total_qty + 1";
    }

    if ($db->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Data updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Query Failed: ' . $db->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Data Received', 'received' => $input]);
}
