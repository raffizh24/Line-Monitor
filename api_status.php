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

// Query mengambil semua mesin yang terdaftar di periode bulan ini
$query = "SELECT machine_id, last_signal, total_qty, total_stop_seconds,
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
$threshold = 180; // 3 menit timeout status RUNNING/STOP

// Mengiterasi langsung dari hasil database secara dinamis
while ($row = $result->fetch_assoc()) {
    $elapsed = $row['last_signal'] !== null ? (int)$row['elapsed'] : null;
    $status = ($row['last_signal'] !== null && $elapsed <= $threshold) ? 'GREEN' : 'RED';

    $machines[] = [
        'machine_id' => $row['machine_id'],
        'status' => $status,
        'last_signal' => $row['last_signal'],
        'elapsed' => $elapsed,
        'total_qty' => (int)$row['total_qty'],
        'total_stop_seconds' => (int)($row['total_stop_seconds'] ?? 0)
    ];
}

echo json_encode([
    'status' => 'success',
    'current_month' => date('F Y'),
    'machines' => $machines
]);
