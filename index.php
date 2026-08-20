<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Line Monitoring System - AC Factory Dashboard</title>

    <!-- Bootstrap 5 CSS LOKAL -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (CDN / Lokal) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
        <div>
            <span id="off-production-badge" class="badge bg-warning text-dark fs-6 d-none me-2">OFF PRODUCTION</span>
            <span id="current-month" class="badge bg-secondary fs-6">Loading Period...</span>
        </div>
    </nav>

    <!-- CAROUSEL WRAPPER -->
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
                    <div class="text-center mb-3">
                        <h2 class="fw-bold text-success area-title">MAIN ASSY</h2>
                    </div>

                    <!-- SUB-AREA IDU (INDOOR AC) -->
                    <div class="mb-4">
                        <h4 class="text-info fw-bold border-bottom border-info pb-2 mb-3">
                            <i class="bi bi-fan text-info me-2"></i> INDOOR LINE
                            <div class="row g-3" id="area-idu">
                                <!-- Kartu Mesin IDU akan dirender otomatis -->
                            </div>
                    </div>

                    <!-- SUB-AREA ODU (OUTDOOR AC) -->
                    <div>
                        <h4 class="text-warning fw-bold border-bottom border-warning pb-2 mb-3">
                            <i class="bi bi-cpu text-warning me-2"></i> OUTDOOR LINE
                        </h4>
                        <div class="row g-3" id="area-odu">
                            <!-- Kartu Mesin ODU akan dirender otomatis -->
                        </div>
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

        <!-- Tombol Navigasi Manual -->
        <button class="carousel-control-prev d-none" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next d-none" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Bootstrap 5 JS Bundle LOKAL -->
    <script src="js/bootstrap.bundle.min.js"></script>

    <!-- REAL-TIME FETCHING & RENDER SCRIPT -->
    <script>
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

        // 1. MASTER LIST MESIN IDU & ODU
        const MASTER_IDU = [{
                id: 'IDU-Cabinet',
                name: 'Cabinet Assy Conv'
            },
            {
                id: 'IDU-Helium',
                name: 'Evaporator Assy Conv'
            },
            {
                id: 'IDU-Main',
                name: 'Main Assy Conv'
            },
            {
                id: 'IDU-Electrical',
                name: 'Electrical Ins Conv'
            },
            {
                id: 'IDU-Final',
                name: 'Final Process Conv'
            }
        ];

        const MASTER_ODU = [{
                id: 'ODU-Basepan',
                name: 'Basepan Assy Conv'
            },
            {
                id: 'ODU-Vacuum',
                name: 'Vacuum Process Conv'
            },
            {
                id: 'ODU-Charging',
                name: 'Charging Process Conv'
            },
            {
                id: 'ODU-Main',
                name: 'Main Assy Conv'
            },
            {
                id: 'ODU-Aging',
                name: 'Electrical Aging Conv'
            },
            {
                id: 'ODU-Final',
                name: 'Final Process Conv'
            }
        ];

        // Cek Apakah Semua Mesin Injection (A1-A4 & B1-B4) Sedang Mati / STOP
        function isAllInjectionStopped(machines) {
            const injectionMachines = machines.filter(m => /^([AB][1-4])$/.test(m.machine_id));
            if (injectionMachines.length === 0) return false;
            return injectionMachines.every(m => m.status !== 'GREEN');
        }

        function formatTime(seconds) {
            if (!seconds || seconds <= 0) return "0s";
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return m > 0 ? `${m}m ${s}s` : `${s}s`;
        }

        function fetchDashboardData() {
            fetch('api_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('current-month').innerText = data.current_month;

                        const isOffProduction = isAllInjectionStopped(data.machines);

                        const offBadge = document.getElementById('off-production-badge');
                        if (offBadge) {
                            if (isOffProduction) {
                                offBadge.classList.remove('d-none');
                            } else {
                                offBadge.classList.add('d-none');
                            }
                        }

                        // 1. Render Area Injection
                        renderArea('area-injection', data.machines, id => /^([AB][1-4])$/.test(id), isOffProduction, 'col-md-3');

                        // 2. Render Sub-Area IDU (Indoor AC)
                        renderMasterArea('area-idu', MASTER_IDU, data.machines, isOffProduction, 'col-md-2');

                        // 3. Render Sub-Area ODU (Outdoor AC)
                        renderMasterArea('area-odu', MASTER_ODU, data.machines, isOffProduction, 'col-md-2');
                    }
                })
                .catch(err => console.error("Error fetching data:", err));
        }

        // Fungsi Render Standard (Untuk Injection)
        function renderArea(containerId, machines, filterFn, isOffProduction, colClass = 'col-md-3') {
            const container = document.getElementById(containerId);
            if (!container) return;

            const filtered = machines.filter(m => filterFn(m.machine_id));

            if (filtered.length === 0) {
                container.innerHTML = `
                <div class="col-12 text-center my-4">
                    <p class="text-muted fs-5">Tidak ada mesin terdeteksi di area ini.</p>
                </div>`;
                return;
            }

            container.innerHTML = filtered.map(m => {
                const isGreen = m.status === 'GREEN';
                const bgClass = isGreen ? 'bg-running' : 'bg-stop';
                const statusText = isGreen ? 'RUNNING' : 'STOP';
                const statusBadge = isGreen ? 'bg-light text-success' : 'bg-dark text-white';

                return `
                <div class="${colClass}">
                    <div class="card card-machine ${bgClass} text-white shadow-lg h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-between p-3">
                            <div>
                                <h4 class="card-title fw-bold mb-2">${m.machine_id}</h4>
                                <span class="badge ${statusBadge} fs-6 px-3 py-1 fw-bold shadow-sm mb-2">${statusText}</span>
                            </div>
                            <div class="bg-dark bg-opacity-25 rounded-3 p-2 text-start mt-2">
                                <div class="d-flex justify-content-between mb-1 fs-6">
                                    <span>Total Qty:</span>
                                    <strong class="fs-6">${m.total_qty || 0}</strong>
                                </div>
                                <div class="d-flex justify-content-between fs-6 border-top border-secondary pt-1 mt-1">
                                    <span>Last Signal:</span>
                                    <strong class="fs-6 text-info">${m.last_signal || '-'}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        // Fungsi Khusus IDU & ODU (Master List + Dummy Fallback)
        function renderMasterArea(containerId, masterList, apiMachines, isOffProduction, colClass = 'col-md-2') {
            const container = document.getElementById(containerId);
            if (!container) return;

            container.innerHTML = masterList.map(item => {
                // Cari data asli berdasarkan ID Mesin
                const realData = apiMachines.find(m => m.machine_id.toUpperCase() === item.id.toUpperCase());

                if (realData) {
                    // RENDER DATA ASLI DATABASE
                    const isGreen = realData.status === 'GREEN';
                    const bgClass = isGreen ? 'bg-running' : 'bg-stop';
                    const statusText = isGreen ? 'RUNNING' : 'STOP';
                    const statusBadge = isGreen ? 'bg-light text-success' : 'bg-dark text-white';
                    const displayStopTime = isOffProduction ? 'OFF' : formatTime(realData.total_stop_seconds || 0);

                    return `
                    <div class="${colClass}">
                        <div class="card card-machine ${bgClass} text-white shadow-lg h-100">
                            <div class="card-body text-center d-flex flex-column justify-content-between p-2">
                                <div>
                                    <small class="text-light fw-semibold d-block text-truncate" title="${item.name}">${item.name}</small>
                                    <h5 class="card-title fw-bold my-1">${realData.machine_id}</h5>
                                    <span class="badge ${statusBadge} fs-6 px-2 py-1 fw-bold shadow-sm mb-2">${statusText}</span>
                                </div>
                                <div class="bg-dark bg-opacity-25 rounded-3 p-2 text-start mt-1 fs-6">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Total Qty:</span>
                                        <strong>${realData.total_qty || 0}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between border-top border-secondary pt-1 mt-1">
                                        <span>Stop Time:</span>
                                        <strong class="text-warning">${displayStopTime}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                } else {
                    // RENDER DUMMY CARD (Belum ada di database / belum terpasang ESP32)
                    return `
                    <div class="${colClass}">
                        <div class="card card-machine border-dashed text-white shadow h-100 opacity-75">
                            <div class="card-body text-center d-flex flex-column justify-content-between p-2">
                                <div>
                                    <small class="text-warning fw-semibold d-block text-truncate" title="${item.name}">${item.name}</small>
                                    <h5 class="card-title fw-bold my-1 text-light">${item.id}</h5>
                                    <span class="badge bg-secondary fs-6 px-2 py-1 fw-bold mb-2">OFFLINE</span>
                                </div>
                                <div class="bg-dark bg-opacity-50 rounded-3 p-2 text-center mt-1">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Belum Terpasang ESP32</small>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }
            }).join('');
        }

        fetchDashboardData();
        setInterval(fetchDashboardData, 3000);
    </script>
</body>

</html>