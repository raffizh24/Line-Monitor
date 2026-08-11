while ($row = $result->fetch_assoc()) {
$elapsed = $row['last_signal'] !== null ? (int)$row['elapsed'] : null;
$db_status = strtoupper($row['status'] ?? 'GREEN');
$machine_id = $row['machine_id'];

// 1. Cek apakah mesin tergolong Injection atau kode A1-A4 / B1-B4
$is_injection = (stripos($machine_id, 'injection') !== false);

// Pattern Regex untuk mencocokkan kode mesin A1 s/d A4 atau B1 s/d B4
$is_ab_range = (bool) preg_match('/\b[AB][1-4]\b/i', $machine_id);

// Combine kondisi
$use_180_timeout = ($is_injection || $is_ab_range);

// 2. Tentukan status akhir
if ($use_180_timeout) {
// Khusus Injection & A1-A4 / B1-B4:
// Jadi RED jika tidak ada sinyal, status DB RED, atau elapsed > 180 detik
if ($elapsed === null || $db_status === 'RED' || $elapsed > 180) {
$final_status = 'RED';
} else {
$final_status = 'GREEN';
}
} else {
// Mesin lainnya (Main Assy, dll):
// Tetap menggunakan status terakhir dari database tanpa cek 180 detik
$final_status = $db_status;
}

$machines[] = [
'machine_id' => $machine_id,
'status' => $final_status,
'last_signal' => $row['last_signal'],
'elapsed' => $elapsed,
'total_qty' => (int)$row['total_qty'],
'total_stop_seconds' => (int)($row['total_stop_seconds'] ?? 0)
];
}