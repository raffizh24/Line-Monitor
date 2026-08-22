<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$db = new mysqli('localhost', 'root', '', 'seid_ac_line-monitor');

if ($db->connect_error) {
    http_response_code(200);
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal: ' . $db->connect_error,
        'current_month' => date('F Y'),
        'machines' => []
    ]);
    exit;
}

$periode = date('Y-m');

$query = "SELECT machine_id, status, last_signal, total_qty, total_stop_seconds,
          TIMESTAMPDIFF(SECOND, last_signal, NOW()) as elapsed 
          FROM machine_monthly 
          WHERE periode = '$periode' 
          ORDER BY machine_id ASC";

$result = $db->query($query);

if (!$result) {
    http_response_code(200);
    echo json_encode([
        'status' => 'error',
        'message' => 'Query Error: ' . $db->error,
        'current_month' => date('F Y'),
        'machines' => []
    ]);
    exit;
}

$machines = [];

while ($row = $result->fetch_assoc()) {
    $elapsed   = $row['last_signal'] !== null ? (int)$row['elapsed'] : null;
    $db_status = strtoupper($row['status'] ?? 'GREEN');
    $machine_id = $row['machine_id'];

    // 1. Cek tipe mesin (Injection / A1-A4 / B1-B4)
    $is_injection = (stripos($machine_id, 'injection') !== false);
    $is_ab_range  = (bool) preg_match('/\b[AB][1-4]\b/i', $machine_id);
    $use_180_timeout = ($is_injection || $is_ab_range);

    // 2. Tentukan status akhir
    if ($use_180_timeout) {
        if ($elapsed === null || $db_status === 'RED' || $elapsed > 180) {
            $final_status = 'RED';
        } else {
            $final_status = 'GREEN';
        }
    } else {
        $final_status = $db_status;
    }

    // 3. Hitung Real-time Total Stop Seconds
    $base_stop_seconds = (int)($row['total_stop_seconds'] ?? 0);

    // Jika mesin sedang STOP (RED), tambahkan elapsed time sejak sinyal terakhir
    if ($final_status === 'RED' && $elapsed !== null) {
        $calculated_stop_seconds = $base_stop_seconds + $elapsed;
    } else {
        $calculated_stop_seconds = $base_stop_seconds;
    }

    $machines[] = [
        'machine_id'         => $machine_id,
        'status'             => $final_status,
        'last_signal'        => $row['last_signal'],
        'elapsed'            => $elapsed,
        'total_qty'          => (int)$row['total_qty'],
        'total_stop_seconds' => $calculated_stop_seconds
    ];
}

echo json_encode([
    'status'        => 'success',
    'current_month' => date('F Y'),
    'machines'      => $machines
]);

$db->close();
