<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$db = new mysqli('localhost', 'root', '', 'seid_ac_line-monitor');

if ($db->connect_error) {
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']);
    exit;
}

$area       = $_GET['area'] ?? 'ALL';
$date_start = $_GET['date_start'] ?? date('Y-m-d');
$date_end   = $_GET['date_end'] ?? date('Y-m-d');
$time_start = $_GET['time_start'] ?? '00:00';
$time_end   = $_GET['time_end'] ?? '23:59';

$start_datetime = $db->real_escape_string("$date_start $time_start:00");
$end_datetime   = $db->real_escape_string("$date_end $time_end:59");

$where = ["stop_time BETWEEN '$start_datetime' AND '$end_datetime'"];

if ($area !== 'ALL') {
    $clean_area = $db->real_escape_string($area);
    $where[] = "area = '$clean_area'";
}

$where_clause = implode(' AND ', $where);

$query = "SELECT id, machine_id, area, stop_time, start_time, 
          IFNULL(duration_seconds, TIMESTAMPDIFF(SECOND, stop_time, NOW())) as duration_seconds
          FROM machine_downtime_log 
          WHERE $where_clause 
          ORDER BY stop_time DESC";

$result = $db->query($query);
$logs = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = [
            'id'               => $row['id'],
            'machine_id'       => $row['machine_id'],
            'area'             => $row['area'],
            'stop_time'        => $row['stop_time'],
            'start_time'       => $row['start_time'] ?? 'MASIH STOP',
            'duration_seconds' => (int)$row['duration_seconds']
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'data'   => $logs
]);

$db->close();
