<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Stop Mesin</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
        }

        .card-custom {
            background-color: #1e1e1e;
            border: 1px solid #333;
            border-radius: 12px;
        }

        .table-dark-custom {
            background-color: #1a1a1a;
            color: #fff;
        }

        .table-dark-custom th {
            background-color: #2c2c2c;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-2">
            <h2 class="fw-bold mb-0">RIWAYAT DOWNTIME / STOP MESIN</h2>
            <a href="index.php" class="btn btn-outline-light"> Kembali ke Dashboard</a>
        </div>

        <!-- FILTER PANEL -->
        <div class="card card-custom p-3 mb-4">
            <form id="filter-form" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label text-secondary">Area Line</label>
                    <select id="filter-area" class="form-select bg-dark text-white border-secondary">
                        <option value="ALL">Semua Area</option>
                        <option value="INJECTION">Injection Line</option>
                        <option value="IDU">IDU Line</option>
                        <option value="ODU">ODU Line</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary">Dari Tanggal</label>
                    <input type="date" id="filter-date-start" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary">Sampai Tanggal</label>
                    <input type="date" id="filter-date-end" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary">Jam Mulai</label>
                    <input type="time" id="filter-time-start" value="00:00" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-secondary">Jam Selesai</label>
                    <input type="time" id="filter-time-end" value="23:59" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold"> Tampilkan Data</button>
                </div>
            </form>
        </div>

        <!-- TABLE HISTORY -->
        <div class="card card-custom p-3">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mesin ID</th>
                            <th>Area</th>
                            <th>Jam Stop (Mulai)</th>
                            <th>Jam Running (Selesai)</th>
                            <th>Durasi Stop</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="table-history-body">
                        <!-- Content JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
        function formatTime(seconds) {
            if (!seconds || seconds <= 0) return "0s";
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            if (h > 0) return `${h}j ${m}m ${s}s`;
            return m > 0 ? `${m}m ${s}s` : `${s}s`;
        }

        function loadHistoryData() {
            const area = document.getElementById('filter-area').value;
            const dateStart = document.getElementById('filter-date-start').value;
            const dateEnd = document.getElementById('filter-date-end').value;
            const timeStart = document.getElementById('filter-time-start').value;
            const timeEnd = document.getElementById('filter-time-end').value;

            const url = `api_history.php?area=${area}&date_start=${dateStart}&date_end=${dateEnd}&time_start=${timeStart}&time_end=${timeEnd}`;

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    const tbody = document.getElementById('table-history-body');
                    if (res.status === 'success' && res.data.length > 0) {
                        tbody.innerHTML = res.data.map((row, index) => {
                            const isStillStop = row.start_time === 'MASIH STOP';
                            const badgeStatus = isStillStop ?
                                '<span class="badge bg-danger">STOPPING</span>' :
                                '<span class="badge bg-success">RESOLVED</span>';

                            return `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td class="fw-bold">${row.machine_id}</td>
                                    <td><span class="badge bg-secondary">${row.area}</span></td>
                                    <td class="text-warning">${row.stop_time}</td>
                                    <td class="text-info">${row.start_time}</td>
                                    <td class="fw-bold text-white">${formatTime(row.duration_seconds)}</td>
                                    <td>${badgeStatus}</td>
                                </tr>
                            `;
                        }).join('');
                    } else {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data riwayat stop pada periode/area ini.</td></tr>`;
                    }
                })
                .catch(err => console.error("Error loading history:", err));
        }

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('filter-date-start').value = today;
            document.getElementById('filter-date-end').value = today;

            loadHistoryData();

            document.getElementById('filter-form').addEventListener('submit', function(e) {
                e.preventDefault();
                loadHistoryData();
            });
        });
    </script>
</body>

</html>