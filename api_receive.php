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
        // 1. Update status di tabel monthly ke RED (Qty tidak disentuh)
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, status, total_qty, total_stop_seconds) 
                VALUES ('$machine_id', '$periode', NOW(), 'RED', 0, 0) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(),
                    status = 'RED'";
        $db->query($sql);

        // 2. Tentukan nama area
        $area = (strpos($machine_id, 'ODU') !== false) ? 'ODU LINE' : 'IDU LINE';

        // 3. Catat riwayat STOP di machine_downtime_log jika belum ada record menggantung
        $check_log = $db->query("SELECT id FROM machine_downtime_log WHERE machine_id = '$machine_id' AND start_time IS NULL");
        if ($check_log->num_rows == 0) {
            $db->query("INSERT INTO machine_downtime_log (machine_id, area, stop_time) VALUES ('$machine_id', '$area', NOW())");
        }
    }

    // CASE 2: Sinyal Kembali RUNNING / ON (Tombol dilepas)
    else if ($status === 'ON') {
        // 1. Update status ke GREEN & TAMBAH DURASI STOP (Tanpa menambah total_qty)
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, status, total_qty, total_stop_seconds) 
                VALUES ('$machine_id', '$periode', NOW(), 'GREEN', 0, $stop_duration) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(),
                    status = 'GREEN',
                    total_stop_seconds = total_stop_seconds + $stop_duration";
        $db->query($sql);

        // 2. Tutup riwayat STOP di machine_downtime_log agar muncul di history.php
        $get_last = $db->query("SELECT id FROM machine_downtime_log WHERE machine_id = '$machine_id' AND start_time IS NULL ORDER BY id DESC LIMIT 1");
        if ($get_last->num_rows > 0) {
            $row = $get_last->fetch_assoc();
            $log_id = $row['id'];

            if ($stop_duration > 0) {
                $db->query("UPDATE machine_downtime_log 
                            SET start_time = NOW(), 
                                duration_seconds = $stop_duration 
                            WHERE id = $log_id");
            } else {
                $db->query("UPDATE machine_downtime_log 
                            SET start_time = NOW(), 
                                duration_seconds = TIMESTAMPDIFF(SECOND, stop_time, NOW()) 
                            WHERE id = $log_id");
            }
        }
    }

    // CASE 3: Sinyal Pulsa / Heartbeat Biasa
    else {
        $sql = "INSERT INTO machine_monthly (machine_id, periode, last_signal, status, total_qty, total_stop_seconds) 
                VALUES ('$machine_id', '$periode', NOW(), 'GREEN', 0, 0) 
                ON DUPLICATE KEY UPDATE 
                    last_signal = NOW(),
                    status = 'GREEN'";
        $db->query($sql);
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Data updated successfully']);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Data']);
}

$db->close();
