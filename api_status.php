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
    $elapsed = $row['last_signal'] !== null ? (int)$row['elapsed'] : null;
    $db_status = strtoupper($row['status'] ?? 'RED');

    // Cek apakah machine_id mengandung kata 'Injection' (case-insensitive)
    $is_injection = (stripos($row['machine_id'], 'injection') !== false);

    // LOGIKA THRESHOLD & STATUS:
    // 1. Jika status di DB sudah 'RED' atau belum ada sinyal -> RED
    if ($elapsed === null || $db_status === 'RED') {
        $final_status = 'RED';
    }
    // 2. Khusus mesin Injection: jika elapsed > 180 detik -> RED
    else if ($is_injection && $elapsed > 180) {
        $final_status = 'RED';
    }
    // 3. Selain kondisi di atas -> GREEN
    else {
        $final_status = 'GREEN';
    }

    $machines[] = [
        'machine_id'         => $row['machine_id'],
        'status'             => $final_status,
        'last_signal'        => $row['last_signal'],
        'elapsed'            => $elapsed,
        'total_qty'          => (int)$row['total_qty'],
        'total_stop_seconds' => (int)($row['total_stop_seconds'] ?? 0)
    ];
}

echo json_encode([
    'status'        => 'success',
    'current_month' => date('F Y'),
    'machines'      => $machines
]);

$db->close();
