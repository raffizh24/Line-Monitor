<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$db = new mysqli('localhost', 'root', '', 'seid_ac_line-monitor');

if ($db->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$json_raw = file_get_contents('php://input');
$input    = json_decode($json_raw, true);

$machine_id    = isset($input['machine_id']) ? $db->real_escape_string($input['machine_id']) : null;
$status        = isset($input['status']) ? strtoupper($input['status']) : null;
$stop_duration = isset($input['stop_duration']) ? (int)$input['stop_duration'] : 0;
$periode       = date('Y-m');

if ($machine_id) {

    // CASE 1: Sinyal STOP / OFF (Tombol ditekan)
    if ($status === 'OFF') {
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, status, total_qty, total_stop_seconds) 
                VALUES ('$machine_id', '$periode', NOW(), 'RED', 0, 0) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(),
                    status = 'RED'";
    }

    // CASE 2: Sinyal Kembali RUNNING / ON (Tombol dilepas)
    else if ($status === 'ON') {
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, status, total_qty, total_stop_seconds) 
                VALUES ('$machine_id', '$periode', NOW(), 'GREEN', 1, $stop_duration) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(),
                    status = 'GREEN',
                    total_qty = total_qty + 1, 
                    total_stop_seconds = total_stop_seconds + $stop_duration";
    }

    // CASE 3: Sinyal Tambah QTY (Raspberry Pi)
    else {
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, status, total_qty, total_stop_seconds) 
                VALUES ('$machine_id', '$periode', NOW(), 'GREEN', 1, 0) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(),
                    status = 'GREEN',
                    total_qty = total_qty + 1";
    }

    if ($db->query($sql)) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Data updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Query Failed: ' . $db->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Data']);
}
