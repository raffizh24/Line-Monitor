<?php
// Tampilkan error PHP jika ada bug sintaks saat debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Koneksi ke Database
$db = new mysqli('localhost', 'root', '', 'line_monitor');

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

// Query mengambil data berdasarkan periode
$query = "SELECT machine_id, last_signal, total_qty, total_work_seconds, 
          TIMESTAMPDIFF(SECOND, last_signal, NOW()) as elapsed 
          FROM machine_monthly 
          WHERE periode = '$periode' 
          ORDER BY machine_id ASC";

$result = $db->query($query);

// Cek jika query SQL gagal (misal: kolom 'periode' belum ada)
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

$all_machines = ['A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'B3', 'B4'];
$data_map = [];

while ($row = $result->fetch_assoc()) {
    $data_map[$row['machine_id']] = $row;
}

function formatSeconds($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

$machines = [];
$threshold = 180; // 3 menit timeout

foreach ($all_machines as $m_id) {
    if (isset($data_map[$m_id])) {
        $row = $data_map[$m_id];
        $elapsed = $row['last_signal'] !== null ? (int)$row['elapsed'] : null;
        $status = ($row['last_signal'] !== null && $elapsed <= $threshold) ? 'GREEN' : 'RED';

        $machines[] = [
            'machine_id' => $m_id,
            'status' => $status,
            'last_signal' => $row['last_signal'],
            'elapsed' => $elapsed,
            'total_qty' => (int)$row['total_qty'],
            'work_time_formatted' => formatSeconds((int)$row['total_work_seconds'])
        ];
    } else {
        $machines[] = [
            'machine_id' => $m_id,
            'status' => 'RED',
            'last_signal' => null,
            'elapsed' => null,
            'total_qty' => 0,
            'work_time_formatted' => '00:00:00'
        ];
    }
}

// Response JSON
echo json_encode([
    'status' => 'success',
    'current_month' => date('F Y'),
    'machines' => $machines
]);
