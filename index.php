<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Line Monitoring System - Monthly</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1e1e2f;
            color: #ffffff;
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 26px;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #00d68f;
            font-size: 14px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            max-width: 1300px;
            margin: 0 auto;
        }

        @media (max-width: 1024px) {
            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background-color: #27293d;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            border: 1px solid #32354a;
        }

        .card h2 {
            font-size: 20px;
            color: #e1e1e6;
            margin-bottom: 10px;
        }

        .status-badge {
            font-size: 20px;
            font-weight: bold;
            padding: 10px 0;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: background-color 0.5s ease;
            letter-spacing: 1px;
        }

        .bg-green {
            background-color: #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.4);
        }

        .bg-red {
            background-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.4);
        }

        .metrics-container {
            display: flex;
            justify-content: space-around;
            background: #1e1e2f;
            padding: 12px 5px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .metric-item {
            flex: 1;
        }

        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #00d68f;
        }

        .metric-label {
            font-size: 10px;
            color: #a1a1b5;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .info {
            font-size: 11px;
            color: #8f8f9d;
            margin-top: 4px;
        }
    </style>
</head>

<body>

    <h1>MONITORING STATUS LINE PRODUCTION</h1>
    <div class="subtitle" id="month-label">PERIODE REKAP: MEMUAT...</div>

    <div class="grid-container" id="machine-grid">
        <!-- Grid items dinamis diproduksi oleh JavaScript -->
    </div>

    <script>
        function loadStatus() {
            fetch('api_status.php')
                .then(res => res.json())
                .then(resData => {
                    // Update teks bulan/periode
                    if (resData.current_month) {
                        document.getElementById('month-label').innerText = 'PERIODE REKAP: ' + resData.current_month.toUpperCase();
                    }

                    const grid = document.getElementById('machine-grid');
                    grid.innerHTML = '';

                    resData.machines.forEach(m => {
                        const isGreen = m.status === 'GREEN';
                        const card = document.createElement('div');
                        card.className = 'card';
                        card.innerHTML = `
                        <h2>MESIN ${m.machine_id}</h2>
                        <div class="status-badge ${isGreen ? 'bg-green' : 'bg-red'}">
                            ${isGreen ? 'RUNNING' : 'STOP'}
                        </div>
                        
                        <div class="metrics-container">
                            <div class="metric-item">
                                <div class="metric-value">${m.total_qty}</div>
                                <div class="metric-label">QTY BULAN INI</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value">${m.work_time_formatted}</div>
                                <div class="metric-label">JAM KERJA</div>
                            </div>
                        </div>

                        <div class="info">Sinyal Terakhir: ${m.last_signal ? m.last_signal : 'Belum Ada'}</div>
                        <div class="info">Idle: ${m.elapsed !== null ? m.elapsed : '-'} detik</div>
                    `;
                        grid.appendChild(card);
                    });
                })
                .catch(err => console.error('Error fetching data:', err));
        }

        // Refresh otomatis setiap 3 detik
        setInterval(loadStatus, 3000);

        // Panggil saat halaman pertama kali dibuka
        loadStatus();
    </script>

</body>

</html>