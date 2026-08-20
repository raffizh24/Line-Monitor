<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Produksi 3D</title>

    <!-- BOOTSTRAP CSS LOKAL -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .card-machine {
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-machine:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5) !important;
        }

        .bg-running {
            background: linear-gradient(135deg, #198754, #0f5132) !important;
            border: 1px solid #25cff2;
        }

        .bg-stop {
            background: linear-gradient(135deg, #dc3545, #842029) !important;
            border: 1px solid #ea868f;
        }

        /* Container 3D di Atas */
        .canvas-container {
            width: 100%;
            height: 450px;
            position: relative;
            background-color: #1e1e1e;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #333;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(18, 18, 18, 0.85);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 3%;
        }

        /* Custom Indikator Carousel */
        .carousel-indicators {
            position: relative;
            margin-bottom: 0;
            margin-top: 10px;
        }

        .carousel-indicators [data-bs-target] {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #6c757d;
            border: none;
            opacity: 0.5;
            transition: all 0.3s ease;
        }

        .carousel-indicators .active {
            background-color: #0d6efd;
            width: 28px;
            border-radius: 10px;
            opacity: 1;
        }
    </style>

    <!-- THREE.JS LIBRARY LOKAL -->
    <script src="js/three.min.js"></script>
    <script src="js/GLTFLoader.js"></script>
    <script src="js/OrbitControls.js"></script>
</head>

<body>

    <div class="container-fluid py-3 px-4">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom border-secondary pb-2">
            <h2 class="fw-bold mb-0 text-white">DASHBOARD LINE MONITORING</h2>
            <div class="d-flex align-items-center gap-3">
                <span id="off-production-badge" class="badge bg-warning text-dark fs-6 d-none">OFF PRODUCTION</span>
                <span class="badge bg-primary fs-6" id="current-month">MEMUAT...</span>
            </div>
        </div>

        <!-- CAROUSEL / SLIDER UTAMA -->
        <div id="dashboardCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="10000">

            <!-- INDIKATOR TITIK HALAMAN -->
            <div class="carousel-indicators mb-3">
                <button type="button" data-bs-target="#dashboardCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1 (Main Assy)"></button>
                <button type="button" data-bs-target="#dashboardCarousel" data-bs-slide-to="1" aria-label="Slide 2 (Injection)"></button>
            </div>

            <div class="carousel-inner">

                <!-- SLIDE 1: MAIN ASSY LINE (IDU & ODU) -->
                <div class="carousel-item active">
                    <div class="d-flex flex-column gap-3">
                        <!-- ATAS: 3D MODEL MAIN ASSY -->
                        <div class="canvas-container">
                            <div id="loading-main" class="loading-overlay">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <span class="loading-text text-light">Memuat 3D Model Main Assy...</span>
                            </div>
                            <div id="3d-container-main" style="width: 100%; height: 100%;"></div>
                        </div>

                        <!-- BAWAH: CARD STATUS IDU & ODU -->
                        <div class="row g-3">
                            <!-- AREA IDU LINE -->
                            <div class="col-12 col-xl-6">
                                <div class="card bg-dark text-white border-secondary h-100">
                                    <div class="card-header bg-secondary bg-opacity-25 fw-bold text-uppercase fs-6 py-2">
                                        AREA IDU LINE
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2 justify-content-start" id="area-idu"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- AREA ODU LINE -->
                            <div class="col-12 col-xl-6">
                                <div class="card bg-dark text-white border-secondary h-100">
                                    <div class="card-header bg-secondary bg-opacity-25 fw-bold text-uppercase fs-6 py-2">
                                        AREA ODU LINE
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2 justify-content-start" id="area-odu"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2: INJECTION LINE -->
                <div class="carousel-item">
                    <div class="d-flex flex-column gap-3">
                        <!-- ATAS: 3D MODEL INJECTION -->
                        <div class="canvas-container">
                            <div id="loading-inj" class="loading-overlay">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <span class="loading-text text-light">Memuat 3D Model Injection...</span>
                            </div>
                            <div id="3d-container-inj" style="width: 100%; height: 100%;"></div>
                        </div>

                        <!-- BAWAH: CARD STATUS INJECTION -->
                        <div class="card bg-dark text-white border-secondary">
                            <div class="card-header bg-secondary bg-opacity-25 fw-bold text-uppercase fs-5 py-2">
                                AREA INJECTION LINE
                            </div>
                            <div class="card-body">
                                <div class="row g-2 justify-content-start" id="area-injection"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- CAROUSEL CONTROLS -->
            <button class="carousel-control-prev" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <!-- BOOTSTRAP JS LOKAL -->
    <script src="js/bootstrap.bundle.min.js"></script>

    <script>
        // Master List Data IDU & ODU
        const MASTER_IDU = [
            { id: 'IDU-Cabinet', name: 'Cabinet Assy' },
            { id: 'IDU-Helium', name: 'Evaporator Assy' },
            { id: 'IDU-Main', name: 'Main Assy' },
            { id: 'IDU-Electrical', name: 'Electrical Inspection' },
            { id: 'IDU-Final', name: 'Final Process' }
        ];

        const MASTER_ODU = [
            { id: 'ODU-Basepan', name: 'Basepan Assy' },
            { id: 'ODU-Vacuum', name: 'Vacuum Process' },
            { id: 'ODU-Charging', name: 'Charging Process' },
            { id: 'ODU-Main', name: 'Main Assy' },
            { id: 'ODU-Aging', name: 'Electrical Aging' },
            { id: 'ODU-Final', name: 'Final Process' }
        ];

        // MAPPING NODE & MESH UNTUK MAIN ASSY LINE 3D (IDU & ODU)
        const MESH_MAPPING_MAIN = {
            'IDU-Cabinet': ['IDU.Cabinet', 'IDU_Cabinet', 'Cabinet'],
            'IDU-Electrical': ['IDU.EI', 'IDU_EI', 'EI'],
            'IDU-Helium': ['IDU.Evaporator', 'IDU_Evaporator', 'Evaporator', 'Helium'],
            'IDU-Final': ['IDU.Final', 'IDU_Final'],
            'IDU-Main': ['IDU.MAP', 'IDU_MAP', 'MAP'],
            'ODU-Basepan': ['ODU.Basepan', 'ODU_Basepan', 'Basepan'],
            'ODU-Vacuum': ['ODU.Vacuum', 'ODU_Vacuum', 'Vacuum'],
            'ODU-Charging': ['ODU.Charging', 'ODU_Charging', 'Charging'],
            'ODU-Main': ['ODU.MAP', 'ODU_MAP'],
            'ODU-Aging': ['ODU.Aging', 'ODU_Aging', 'Aging'],
            'ODU-Final': ['ODU.Final', 'ODU_Final']
        };

        const MESH_MAPPING_INJECTION = {
            'A1': ['Machine1', 'Mc1', 'A1'],
            'A2': ['Machine2', 'Mc2', 'A2'],
            'A3': ['Machine3', 'Mc3', 'A3'],
            'A4': ['Machine4', 'Mc4', 'A4'],
            'B1': ['Machine5', 'Mc5', 'B1'],
            'B2': ['Machine6', 'Mc6', 'B2'],
            'B3': ['Machine7', 'Mc7', 'B3'],
            'B4': ['Machine8', 'Mc8', 'B4']
        };

        const COLOR_RUNNING = 0x00FF00; // Hijau
        const COLOR_STOP = 0xFF0000;    // Merah
        const COLOR_OFFLINE = 0x6c757d; // Abu-abu

        const scenes3D = {
            main: {
                containerId: '3d-container-main',
                loadingId: 'loading-main',
                path: '3D/MainAssy.glb',
                loadedModel: null,
                scene: null,
                camera: null,
                renderer: null,
                controls: null
            },
            inj: {
                containerId: '3d-container-inj',
                loadingId: 'loading-inj',
                path: '3D/Injection.glb',
                loadedModel: null,
                scene: null,
                camera: null,
                renderer: null,
                controls: null
            }
        };

        let latestMachineData = [];

        function cleanString(str) {
            return String(str || '').replace(/[^a-zA-Z0-9]/g, '').toLowerCase();
        }

        function findMachineData(machineId) {
            if (!latestMachineData || latestMachineData.length === 0) return null;
            const target = cleanString(machineId);
            return latestMachineData.find(m => cleanString(m.machine_id) === target);
        }

        function initSingle3D(key) {
            const item = scenes3D[key];
            const container = document.getElementById(item.containerId);
            if (!container || typeof THREE === 'undefined') return;

            item.scene = new THREE.Scene();
            item.scene.background = new THREE.Color(0x1a1a1a);

            item.camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);

            item.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            item.renderer.setSize(container.clientWidth, container.clientHeight);
            item.renderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(item.renderer.domElement);

            // Pencahayaan
            const ambientLight = new THREE.AmbientLight(0xffffff, 1.5);
            item.scene.add(ambientLight);

            const dirLight1 = new THREE.DirectionalLight(0xffffff, 1.2);
            dirLight1.position.set(10, 20, 15);
            item.scene.add(dirLight1);

            if (typeof THREE.OrbitControls !== 'undefined') {
                item.controls = new THREE.OrbitControls(item.camera, item.renderer.domElement);
                item.controls.enableDamping = true;
                item.controls.dampingFactor = 0.05;
            }

            if (typeof THREE.GLTFLoader === 'undefined') return;

            const loader = new THREE.GLTFLoader();
            loader.load(
                item.path,
                function(gltf) {
                    item.loadedModel = gltf.scene;
                    item.scene.add(item.loadedModel);

                    const box = new THREE.Box3().setFromObject(item.loadedModel);
                    const center = box.getCenter(new THREE.Vector3());
                    const size = box.getSize(new THREE.Vector3());

                    const maxDim = Math.max(size.x, size.y, size.z);
                    item.camera.position.set(center.x, center.y + (maxDim * 0.35), center.z + (maxDim * 0.35));
                    item.camera.lookAt(center);

                    if (item.controls) {
                        item.controls.target.copy(center);
                        item.controls.update();
                    }

                    const loadingElem = document.getElementById(item.loadingId);
                    if (loadingElem) loadingElem.style.display = 'none';

                    update3DStatus();

                    function animate() {
                        requestAnimationFrame(animate);
                        if (item.controls) item.controls.update();
                        item.renderer.render(item.scene, item.camera);
                    }
                    animate();
                },
                null,
                function(error) {
                    console.error(`GAGAL MEMUAT GLB (${item.path}):`, error);
                    const loadingElem = document.getElementById(item.loadingId);
                    if (loadingElem) {
                        const txt = loadingElem.querySelector('.loading-text');
                        if (txt) {
                            txt.innerText = `Gagal memuat file 3D GLB`;
                            txt.classList.add('text-danger');
                        }
                    }
                }
            );
        }

        // PENETRASI MATERIAL KEBAL TEKSTUR & UNTUK SELURUH SUB-CHILD MESH
        function applyColorToMesh(object3d, hexColor) {
            if (!object3d) return;

            object3d.traverse((child) => {
                if (child.isMesh) {
                    child.material = new THREE.MeshBasicMaterial({
                        color: hexColor,
                        wireframe: false,
                        side: THREE.DoubleSide
                    });
                    child.material.needsUpdate = true;
                }
            });
        }

        function update3DStatus() {
            // 1. Update Main Assy Model
            if (scenes3D.main.loadedModel) {
                Object.keys(MESH_MAPPING_MAIN).forEach(machineId => {
                    const targetNames = MESH_MAPPING_MAIN[machineId];
                    const realData = findMachineData(machineId);

                    let targetColor = COLOR_RUNNING;
                    if (realData) {
                        targetColor = (realData.status === 'GREEN') ? COLOR_RUNNING : COLOR_STOP;
                    }

                    scenes3D.main.loadedModel.traverse((child) => {
                        const childClean = cleanString(child.name);
                        if (!childClean) return;

                        const isMatch = targetNames.some(tName => {
                            const tClean = cleanString(tName);
                            return childClean.includes(tClean) || tClean.includes(childClean);
                        });

                        if (isMatch) {
                            applyColorToMesh(child, targetColor);
                        }
                    });
                });
            }

            // 2. Update Injection Model
            if (scenes3D.inj.loadedModel) {
                Object.keys(MESH_MAPPING_INJECTION).forEach(machineId => {
                    const targetNames = MESH_MAPPING_INJECTION[machineId];
                    const realData = findMachineData(machineId);

                    let targetColor = COLOR_OFFLINE;
                    if (realData) {
                        targetColor = (realData.status === 'GREEN') ? COLOR_RUNNING : COLOR_STOP;
                    }

                    scenes3D.inj.loadedModel.traverse((child) => {
                        const childClean = cleanString(child.name);
                        if (!childClean) return;

                        const isMatch = targetNames.some(tName => {
                            const tClean = cleanString(tName);
                            return childClean.includes(tClean) || tClean.includes(childClean);
                        });

                        if (isMatch) {
                            applyColorToMesh(child, targetColor);
                        }
                    });
                });
            }
        }

        function handleResize() {
            Object.keys(scenes3D).forEach(key => {
                const item = scenes3D[key];
                const container = document.getElementById(item.containerId);
                if (container && item.renderer && item.camera) {
                    item.camera.aspect = container.clientWidth / container.clientHeight;
                    item.camera.updateProjectionMatrix();
                    item.renderer.setSize(container.clientWidth, container.clientHeight);
                }
            });
        }

        function isAllInjectionStopped(machines) {
            const injectionMachines = machines.filter(m => /^([AB][1-4])$/i.test(m.machine_id));
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
                            if (isOffProduction) offBadge.classList.remove('d-none');
                            else offBadge.classList.add('d-none');
                        }

                        latestMachineData = data.machines;

                        renderArea('area-injection', data.machines, id => /^([AB][1-4])$/i.test(id), isOffProduction, 'col-md-3 col-6');
                        renderMasterArea('area-idu', MASTER_IDU, data.machines, isOffProduction, 'col-md-4 col-6');
                        renderMasterArea('area-odu', MASTER_ODU, data.machines, isOffProduction, 'col-md-4 col-6');

                        update3DStatus();
                    }
                })
                .catch(err => console.error("Error fetching data:", err));
        }

        function renderArea(containerId, machines, filterFn, isOffProduction, colClass = 'col-md-3') {
            const container = document.getElementById(containerId);
            if (!container) return;

            const filtered = machines.filter(m => filterFn(m.machine_id));
            if (filtered.length === 0) {
                container.innerHTML = `<div class="col-12 text-center my-3"><p class="text-muted fs-5">Tidak ada mesin terdeteksi di area ini.</p></div>`;
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
                        <div class="card-body text-center d-flex flex-column justify-content-between p-2">
                            <div>
                                <h5 class="card-title fw-bold my-1">${m.machine_id}</h5>
                                <span class="badge ${statusBadge} fs-6 px-3 py-1 fw-bold shadow-sm mb-2">${statusText}</span>
                            </div>
                            <div class="bg-dark bg-opacity-25 rounded-3 p-2 text-start mt-1 fs-6">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Qty:</span>
                                    <strong>${m.total_qty || 0}</strong>
                                </div>
                                <div class="d-flex justify-content-between border-top border-secondary pt-1 mt-1">
                                    <span>Last Signal:</span>
                                    <strong class="text-info">${m.last_signal || '-'}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        function renderMasterArea(containerId, masterList, apiMachines, isOffProduction, colClass = 'col-md-4') {
            const container = document.getElementById(containerId);
            if (!container) return;

            container.innerHTML = masterList.map(item => {
                const realData = findMachineData(item.id);

                const machineObj = realData || {
                    machine_id: item.id,
                    status: 'GREEN',
                    total_qty: 0,
                    total_stop_seconds: 0
                };

                const isGreen = machineObj.status === 'GREEN';
                const bgClass = isGreen ? 'bg-running' : 'bg-stop';
                const statusText = isGreen ? 'RUNNING' : 'STOP';
                const statusBadge = isGreen ? 'bg-light text-success' : 'bg-dark text-white';
                const displayStopTime = isOffProduction ? 'OFF' : formatTime(machineObj.total_stop_seconds || 0);

                return `
                <div class="${colClass}">
                    <div class="card card-machine ${bgClass} text-white shadow-lg h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-between p-2">
                            <div>
                                <h5 class="card-title fw-bold my-2 text-truncate" title="${item.name}">${item.name}</h5>
                                <span class="badge ${statusBadge} fs-6 px-2 py-1 fw-bold shadow-sm mb-2">${statusText}</span>
                            </div>
                            <div class="bg-dark bg-opacity-25 rounded-3 p-2 text-start mt-1 fs-6">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Qty:</span>
                                    <strong>${machineObj.total_qty || 0}</strong>
                                </div>
                                <div class="d-flex justify-content-between border-top border-secondary pt-1 mt-1">
                                    <span>Stop Time:</span>
                                    <strong class="text-warning">${displayStopTime}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        document.addEventListener('DOMContentLoaded', function() {
            initSingle3D('main');
            initSingle3D('inj');

            fetchDashboardData();
            setInterval(fetchDashboardData, 3000);

            const carouselElem = document.getElementById('dashboardCarousel');
            if (carouselElem) {
                const bsCarousel = new bootstrap.Carousel(carouselElem, {
                    interval: 10000,
                    ride: 'carousel'
                });

                carouselElem.addEventListener('slid.bs.carousel', function() {
                    handleResize();
                });
            }

            window.addEventListener('resize', handleResize);
        });
    </script>
</body>

</html>
