<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Line Monitoring System - AC Factory Dashboard</title>
    <!-- Bootstrap 5 CSS LOKAL -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .carousel-item {
            min-height: 90vh;
        }

        .card-machine {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-machine:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255, 255, 255, 0.1);
        }

        .bg-running {
            background: linear-gradient(135deg, #198754, #146c43);
        }

        .bg-stop {
            background: linear-gradient(135deg, #dc3545, #b02a37);
        }

        .border-dashed {
            border: 2px dashed #6c757d !important;
            background-color: #1e1e1e;
        }

        .area-title {
            letter-spacing: 2px;
            text-transform: uppercase;
            display: inline-block;
            padding-bottom: 5px;
        }

        .carousel-indicators [data-bs-target] {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin: 0 6px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR HEADER -->
    <nav class="navbar navbar-dark bg-dark px-4 shadow-sm">
        <span class="navbar-brand mb-0 h1 fs-3 fw-bold text-primary">LINE MONITORING DASHBOARD</span>
        <span id="current-month" class="badge bg-secondary fs-6">Loading Period...</span>
    </nav>

    <!-- CAROUSEL WRAPPER (Pindah Slide Otomatis Tiap 5000ms / 5 Detik) -->
    <div id="dashboardCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

        <!-- Indikator Slide -->
        <div class="carousel-indicators mb-1">
            <button type="button" data-bs-target="#dashboardCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Injection"></button>
            <button type="button" data-bs-target="#dashboardCarousel" data-bs-slide-to="1" aria-label="Main Assy"></button>
            <button type="button" data-bs-target="#dashboardCarousel" data-bs-slide-to="2" aria-label="HE & Piping"></button>
        </div>

        <div class="carousel-inner">

            <!-- SLIDE 1: AREA INJECTION (A1-A4 & B1-B4) -->
            <div class="carousel-item active">
                <div class="container-fluid p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-info area-title">INHOUSE INJECTION</h2>
                    </div>
                    <div class="row g-4" id="area-injection">
                        <!-- Kartu Mesin Injection akan dirender otomatis -->
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: AREA MAIN ASSY (IDU & ODU) -->
            <div class="carousel-item">
                <div class="container-fluid p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-success area-title">MAIN ASSY (IDU & ODU)</h2>
                    </div>
                    <div class="row g-4" id="area-main-assy">
                        <!-- Kartu Mesin Main Assy akan dirender otomatis -->
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: AREA HE & PIPING -->
            <div class="carousel-item">
                <div class="container-fluid p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-warning area-title">INHOUSE HE & PIPING</h2>
                    </div>
                    <div class="row g-4" id="area-he-piping">
                        <!-- Placeholder untuk area yang belum ada ESP32 -->
                        <div class="col-md-6">
                            <div class="card card-machine text-center p-5 border-dashed">
                                <h3 class="text-light fw-bold">AREA HE</h3>
                                <p class="text-warning mt-2 fs-5">Belum Terpasang ESP32</p>
                                <span class="badge bg-secondary w-50 mx-auto mt-3">STATUS: OFFLINE</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-machine text-center p-5 border-dashed">
                                <h3 class="text-light fw-bold">AREA PIPING</h3>
                                <p class="text-warning mt-2 fs-5">Belum Terpasang ESP32</p>
                                <span class="badge bg-secondary w-50 mx-auto mt-3">STATUS: OFFLINE</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tombol Navigasi Manual Next / Prev -->
        <button class="carousel-control-prev d-none" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next d-none" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Bootstrap 5 JS Bundle LOKAL (Menggunakan path js/ bukan /js/) -->
    <script src="js/bootstrap.bundle.min.js"></script>

    <!-- REAL-TIME FETCHING & RENDER SCRIPT -->
    <script>
        // Inisialisasi Carousel via JavaScript agar Terjamin Berjalan Otomatis
        document.addEventListener('DOMContentLoaded', function() {
            var myCarousel = document.querySelector('#dashboardCarousel');
            if (myCarousel && typeof bootstrap !== 'undefined') {
                var carousel = new bootstrap.Carousel(myCarousel, {
                    interval: 5000,
                    ride: 'carousel',
                    wrap: true
                });
            }
        });

        function fetchDashboardData() {
            fetch('api_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('current-month').innerText = data.current_month;

                        // Filter & Render Area Injection (A1-A4 & B1-B4)
                        renderArea('area-injection', data.machines, id => /^([AB][1-4])$/.test(id));

                        // Filter & Render Area Main Assy (IDU-* & ODU-*)
                        renderArea('area-main-assy', data.machines, id => /^(IDU|ODU)-/.test(id));
                    }
                })
                .catch(err => console.error("Error fetching data:", err));
        }

        function formatTime(seconds) {
            if (!seconds || seconds <= 0) return "0s";
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return m > 0 ? `${m}m ${s}s` : `${s}s`;
        }

        function renderArea(containerId, machines, filterFn) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const filtered = machines.filter(m => filterFn(m.machine_id));

            if (filtered.length === 0) {
                container.innerHTML = `
                <div class="col-12 text-center my-5">
                <p class="text-muted fs-4">Tidak ada mesin terdeteksi di area ini.</p>
                </div>`;
                return;
            }

            // Cek apakah area saat ini adalah Injection
            const isInjection = containerId === 'area-injection';

            container.innerHTML = filtered.map(m => {
                const isGreen = m.status === 'GREEN';
                const bgClass = isGreen ? 'bg-running' : 'bg-stop';
                const statusText = isGreen ? 'RUNNING' : 'STOP';
                const statusBadge = isGreen ? 'bg-light text-success' : 'bg-dark text-white';

                // Hanya tampilkan Stop Time jika BUKAN area Injection
                const stopTimeHtml = isInjection ? '' : `
            <div class="d-flex justify-content-between fs-6 border-top border-secondary pt-1 mt-1">
              <span>Total Stop Time:</span>
              <strong class="fs-6 text-warning">${formatTime(m.total_stop_seconds)}</strong>
            </div>
        `;

                return `
                <div class="col-md-3">
                <div class="card card-machine ${bgClass} text-white shadow-lg h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-between p-4">
                    <div>
                        <h3 class="card-title fw-bold display-6 mb-2">${m.machine_id}</h3>
                        <span class="badge ${statusBadge} fs-5 px-3 py-2 fw-bold shadow-sm mb-3">${statusText}</span>
                    </div>
                    
                    <div class="bg-dark bg-opacity-25 rounded-3 p-3 text-start mt-3">
                        <div class="d-flex justify-content-between mb-1 fs-6">
                        <span>Total Qty:</span>
                        <strong class="fs-6">${m.total_qty}</strong>
                        </div>
                        ${stopTimeHtml}
                    </div>
                    </div>
                </div>
                </div>
            `;
            }).join('');
        }

        fetchDashboardData();
        setInterval(fetchDashboardData, 3000);
    </script>
</body>

</html>