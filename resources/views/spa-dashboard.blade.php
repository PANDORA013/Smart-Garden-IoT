<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Garden IoT - Dashboard Terintegrasi</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* TEMA BIRU ELEGAN */
        :root {
            --primary-blue: #004d7a;
            --secondary-blue: #008793;
            --light-blue: #e3f2fd;
            --hover-blue: #f0f8ff;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-blue {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            box-shadow: 0 4px 20px rgba(0, 77, 122, 0.3);
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .nav-link {
            color: rgba(255,255,255,0.85) !important;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0.5rem 1.2rem !important;
            margin: 0 0.2rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.25);
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* PAGE TRANSITIONS */
        .page-section {
            display: none;
            animation: fadeInUp 0.5s ease-out;
        }
        
        .page-section.active {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* CARD STYLES */
        .card {
            border: none;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        /* MODE CARDS */
        .card-mode {
            border: 2px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 12px;
        }
        
        .card-mode:hover {
            border-color: var(--secondary-blue);
            background-color: var(--hover-blue);
            transform: scale(1.02);
        }
        
        .card-mode.selected {
            border-color: var(--primary-blue);
            background: linear-gradient(135deg, var(--light-blue) 0%, #ffffff 100%);
            box-shadow: 0 8px 20px rgba(0, 77, 122, 0.2);
            transform: scale(1.02);
        }

        /* BADGES */
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 600;
            border-radius: 8px;
        }

        /* BUTTONS */
        .btn {
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #003d60 0%, #006d77 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 77, 122, 0.3);
        }

        /* TABLE STYLES */
        .table-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%) !important;
        }

        /* PROGRESS BAR */
        .signal-indicator {
            display: flex;
            gap: 3px;
            align-items: flex-end;
        }

        .signal-bar {
            width: 4px;
            background: #28a745;
            border-radius: 2px;
        }

        /* STATUS INDICATOR */
        .status-online {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .status-online::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* SPINNER */
        .spinner-container {
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* GLOBAL STATUS ALERT */
        .global-status {
            background: white;
            border-left: 5px solid var(--secondary-blue);
            border-radius: 12px;
            padding: 1rem 1.5rem;
        }
    </style>
</head>
<body>

    <!-- NAVBAR BIRU -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-blue sticky-top mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <i class="bi bi-droplet-fill"></i> Smart Garden IoT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" onclick="switchPage('dashboard', this)">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="switchPage('devices', this)">
                            <i class="bi bi-cpu-fill"></i> Perangkat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="switchPage('settings', this)">
                            <i class="bi bi-gear-wide-connected"></i> Pengaturan
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        
        <!-- GLOBAL STATUS -->
        <div class="global-status shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-primary fw-bold">
                    <i class="bi bi-cloud-check-fill"></i> Sistem Terhubung
                </span>
                <small class="text-muted" id="last-update">
                    <i class="bi bi-clock"></i> Update: -
                </small>
            </div>
        </div>

        <!-- HALAMAN 1: DASHBOARD -->
        <div id="page-dashboard" class="page-section active">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">
                    <i class="bi bi-grid-3x3-gap-fill text-primary"></i> Monitoring Tanaman
                </h3>
                <button class="btn btn-outline-primary btn-sm" onclick="fetchData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            
            <div class="row g-4" id="dashboard-container">
                <div class="col-12 spinner-container">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                    <p class="mt-3 text-muted">Menghubungkan ke perangkat...</p>
                </div>
            </div>
        </div>

        <!-- HALAMAN 2: PERANGKAT -->
        <div id="page-devices" class="page-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">
                    <i class="bi bi-router-fill text-primary"></i> Manajemen Perangkat
                </h3>
            </div>
            
            <div class="card shadow-lg">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-primary text-white">
                                <tr>
                                    <th class="ps-4"><i class="bi bi-hdd-network"></i> Nama Perangkat</th>
                                    <th><i class="bi bi-activity"></i> Status</th>
                                    <th><i class="bi bi-wifi"></i> Sinyal</th>
                                    <th><i class="bi bi-clock-history"></i> Terakhir Aktif</th>
                                    <th class="text-end pe-4"><i class="bi bi-tools"></i> Kontrol</th>
                                </tr>
                            </thead>
                            <tbody id="device-table-body">
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="spinner-border spinner-border-sm text-primary"></div>
                                        <span class="ms-2">Memuat data perangkat...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-4 border-0 shadow-sm">
                <i class="bi bi-info-circle-fill"></i>
                <strong>Info:</strong> Tombol <b>Reboot</b> akan mengirim perintah restart ke mikrokontroler.
                Device akan offline beberapa detik lalu kembali online.
            </div>
        </div>

        <!-- HALAMAN 3: PENGATURAN -->
        <div id="page-settings" class="page-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark mb-0">
                    <i class="bi bi-sliders text-primary"></i> Konfigurasi Mode Operasi
                </h3>
            </div>

            <div class="row">
                <!-- PILIH DEVICE -->
                <div class="col-lg-3 mb-4">
                    <div class="card shadow-lg h-100">
                        <div class="card-header bg-white fw-bold border-0 pt-3">
                            <i class="bi bi-cpu"></i> Pilih Perangkat
                        </div>
                        <div class="card-body">
                            <select class="form-select" id="setting-device-select">
                                <option disabled selected>Memuat...</option>
                            </select>
                            
                            <div class="mt-3 p-3 bg-light rounded" id="device-info">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Pilih device untuk melihat status
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODE CARDS -->
                <div class="col-lg-9">
                    <div class="card shadow-lg">
                        <div class="card-header bg-white fw-bold border-0 pt-3">
                            <i class="bi bi-toggles"></i> Pilih Mode Operasi
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- MODE 1: BASIC -->
                            <div class="card card-mode p-3 mb-3" onclick="selectMode(1)" id="mode-1">
                                <div class="d-flex align-items-start">
                                    <div class="fs-1 text-success me-3">
                                        <i class="bi bi-toggle2-on"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-2">
                                            <i class="bi bi-1-circle-fill text-success"></i> Mode Basic (Threshold Manual)
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Sistem bekerja seperti <b>saklar otomatis sederhana</b>. 
                                            Pompa akan menyala HANYA ketika kelembapan tanah turun di bawah batas yang Anda tentukan.
                                        </p>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small><i class="bi bi-lightbulb text-warning"></i> 
                                            <b>Contoh:</b> Set 40% → Jika kelembapan 39%, pompa nyala sampai mencapai 70%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top d-none config-input" id="conf-1">
                                    <label class="form-label">
                                        <i class="bi bi-moisture"></i> Batas Kelembapan Kering: 
                                        <span id="val-limit-on" class="fw-bold text-danger">40</span>%
                                    </label>
                                    <input type="range" class="form-range" min="0" max="100" value="40" id="inp-limit-on" 
                                           oninput="$('#val-limit-on').text(this.value)">
                                    
                                    <label class="form-label mt-3">
                                        <i class="bi bi-moisture"></i> Batas Kelembapan Basah (Stop): 
                                        <span id="val-limit-off" class="fw-bold text-success">70</span>%
                                    </label>
                                    <input type="range" class="form-range" min="0" max="100" value="70" id="inp-limit-off" 
                                           oninput="$('#val-limit-off').text(this.value)">
                                </div>
                            </div>

                            <!-- MODE 2: FUZZY AI -->
                            <div class="card card-mode p-3 mb-3" onclick="selectMode(2)" id="mode-2">
                                <div class="d-flex align-items-start">
                                    <div class="fs-1 text-primary me-3">
                                        <i class="bi bi-cpu-fill"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-2">
                                            <i class="bi bi-2-circle-fill text-primary"></i> Mode Smart AI (Fuzzy Logic)
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            <b>Kecerdasan buatan</b> yang mengatur durasi penyiraman secara adaptif. 
                                            Jika cuaca panas terik, sistem akan menyiram lebih lama. 
                                            Jika mendung atau dingin, durasi penyiraman dikurangi untuk menghemat air.
                                        </p>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small><i class="bi bi-robot text-primary"></i> 
                                            <b>Teknologi:</b> Fuzzy Logic 8 Rules - Kombinasi suhu & kelembapan tanah</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top d-none config-input" id="conf-2">
                                    <div class="alert alert-primary mb-0">
                                        <i class="bi bi-check-circle-fill"></i> 
                                        <strong>AI Aktif.</strong> Semua parameter dikelola otomatis oleh sistem cerdas.
                                        Tidak perlu setting manual.
                                    </div>
                                </div>
                            </div>

                            <!-- MODE 3: JADWAL -->
                            <div class="card card-mode p-3 mb-3" onclick="selectMode(3)" id="mode-3">
                                <div class="d-flex align-items-start">
                                    <div class="fs-1 text-warning me-3">
                                        <i class="bi bi-calendar-check-fill"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-2">
                                            <i class="bi bi-3-circle-fill text-warning"></i> Mode Terjadwal (Timer NTP)
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Penyiraman <b>disiplin berdasarkan waktu</b>, mengabaikan kondisi tanah (kecuali tanah sudah sangat basah). 
                                            Cocok untuk membiasakan tanaman dengan jadwal rutin pagi dan sore.
                                        </p>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small><i class="bi bi-alarm text-warning"></i> 
                                            <b>NTP Sync:</b> Waktu otomatis sinkron dari internet (pool.ntp.org)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top d-none config-input" id="conf-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-sunrise"></i> Jam Pagi (Penyiraman Pertama)
                                            </label>
                                            <input type="time" class="form-control" id="inp-pagi" value="07:00">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-sunset"></i> Jam Sore (Penyiraman Kedua)
                                            </label>
                                            <input type="time" class="form-control" id="inp-sore" value="17:00">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">
                                                <i class="bi bi-stopwatch"></i> Durasi Penyiraman: 
                                                <span id="val-duration" class="fw-bold">5</span> detik
                                            </label>
                                            <input type="range" class="form-range" min="1" max="30" value="5" id="inp-duration"
                                                   oninput="$('#val-duration').text(this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODE 4: MANUAL -->
                            <div class="card card-mode p-3 mb-4" onclick="selectMode(4)" id="mode-4">
                                <div class="d-flex align-items-start">
                                    <div class="fs-1 text-secondary me-3">
                                        <i class="bi bi-hand-index-thumb"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-2">
                                            <i class="bi bi-4-circle-fill text-secondary"></i> Mode Manual (Custom Advanced)
                                        </h5>
                                        <p class="text-muted small mb-0">
                                            Untuk pengguna <b>advanced</b> yang ingin kontrol penuh. 
                                            Anda bisa atur threshold custom sesuai jenis tanaman dan kondisi lingkungan spesifik.
                                        </p>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small><i class="bi bi-person-gear text-secondary"></i> 
                                            <b>Fleksibel:</b> Cocok untuk eksperimen atau tanaman dengan kebutuhan khusus</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top d-none config-input" id="conf-4">
                                    <label class="form-label">
                                        <i class="bi bi-moisture"></i> Custom Threshold ON: 
                                        <span id="val-manual-on" class="fw-bold text-danger">35</span>%
                                    </label>
                                    <input type="range" class="form-range" min="0" max="100" value="35" id="inp-manual-on" 
                                           oninput="$('#val-manual-on').text(this.value)">
                                    
                                    <label class="form-label mt-3">
                                        <i class="bi bi-moisture"></i> Custom Threshold OFF: 
                                        <span id="val-manual-off" class="fw-bold text-success">75</span>%
                                    </label>
                                    <input type="range" class="form-range" min="0" max="100" value="75" id="inp-manual-off" 
                                           oninput="$('#val-manual-off').text(this.value)">
                                </div>
                            </div>

                            <!-- SAVE BUTTON -->
                            <button class="btn btn-primary btn-lg w-100 shadow" onclick="saveSettings()">
                                <i class="bi bi-save"></i> Simpan Pengaturan Mode
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // CSRF TOKEN
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ===== 1. NAVIGASI TAB (TANPA RELOAD) =====
        function switchPage(pageName, element) {
            // Hide semua halaman
            $('.page-section').removeClass('active');
            // Show halaman yang dipilih
            $('#page-' + pageName).addClass('active');
            
            // Update navbar active state
            $('.nav-link').removeClass('active');
            $(element).addClass('active');
            
            // Refresh data ketika buka halaman devices
            if(pageName === 'devices') {
                fetchData();
            }
        }

        // ===== 2. DATA FETCHER (POLLING SETIAP 3 DETIK) =====
        let currentData = [];

        function fetchData() {
            $.get('/api/monitoring', function(data) {
                currentData = data.data || data;
                
                // Update timestamp
                let now = new Date();
                $('#last-update').text('Update: ' + now.toLocaleTimeString('id-ID'));

                // Render dashboard cards
                renderDashboard();
                
                // Render device table
                renderDeviceTable();
                
                // Update device select
                updateDeviceSelect();
            }).fail(function() {
                console.error('Failed to fetch data');
                $('#dashboard-container').html(`
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-wifi-off text-danger fs-1"></i>
                        <p class="mt-3 text-danger">Koneksi ke server gagal. Periksa backend Laravel.</p>
                    </div>
                `);
            });
        }

        // ===== 3. RENDER DASHBOARD CARDS =====
        function renderDashboard() {
            let html = '';
            
            if(!currentData || currentData.length === 0) {
                html = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="mt-3 text-muted">Belum ada perangkat terhubung.</p>
                        <button class="btn btn-primary" onclick="fetchData()">
                            <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                        </button>
                    </div>
                `;
            } else {
                currentData.forEach(d => {
                    let badge = '';
                    let displayValue = '';
                    let subText = '';
                    let cardColor = 'border-success';

                    // Tentukan tampilan berdasarkan mode
                    switch(parseInt(d.mode)) {
                        case 2: // Fuzzy AI
                            badge = '<span class="badge bg-primary">Smart AI</span>';
                            displayValue = `
                                <div class="d-flex justify-content-center gap-4">
                                    <div class="text-center">
                                        <h2 class="fw-bold text-danger mb-0">${d.temperature || 29}°C</h2>
                                        <small class="text-muted">Suhu Udara</small>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="text-center">
                                        <h2 class="fw-bold text-primary mb-0">${d.soil_moisture || 0}%</h2>
                                        <small class="text-muted">Kelembapan</small>
                                    </div>
                                </div>
                            `;
                            subText = 'Optimasi AI Fuzzy Logic';
                            cardColor = 'border-primary';
                            break;
                            
                        case 3: // Jadwal
                            badge = '<span class="badge bg-warning text-dark">Timer</span>';
                            displayValue = `
                                <div class="text-center">
                                    <h3 class="fw-bold text-warning mb-2">
                                        <i class="bi bi-sunrise"></i> ${d.jam_pagi || '07:00'} 
                                        <span class="mx-2">&</span>
                                        <i class="bi bi-sunset"></i> ${d.jam_sore || '17:00'}
                                    </h3>
                                    <small class="text-muted">Kelembapan Saat Ini: ${d.soil_moisture || 0}%</small>
                                </div>
                            `;
                            subText = 'Jadwal Siram Otomatis';
                            cardColor = 'border-warning';
                            break;
                            
                        case 4: // Manual
                            badge = '<span class="badge bg-secondary">Manual</span>';
                            let manualColor = d.soil_moisture < d.batas_siram ? 'text-danger' : 'text-success';
                            displayValue = `
                                <h1 class="${manualColor} fw-bold display-3">${d.soil_moisture || 0}%</h1>
                                <small class="text-muted">Target: ${d.batas_siram}% - ${d.batas_stop}%</small>
                            `;
                            subText = 'Mode Custom Advanced';
                            cardColor = 'border-secondary';
                            break;
                            
                        default: // Basic
                            badge = '<span class="badge bg-success">Basic</span>';
                            let basicColor = d.soil_moisture < 40 ? 'text-danger' : 'text-success';
                            displayValue = `
                                <h1 class="${basicColor} fw-bold display-3">${d.soil_moisture || 0}%</h1>
                            `;
                            subText = 'Kelembapan Tanah Saat Ini';
                            cardColor = 'border-success';
                    }

                    let pumpStatus = d.relay_status == 1 ? 
                        '<span class="badge bg-success"><i class="bi bi-droplet-fill"></i> Menyiram</span>' : 
                        '<span class="badge bg-secondary"><i class="bi bi-droplet"></i> Mati</span>';

                    html += `
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-lg h-100 ${cardColor}" style="border-width: 3px;">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">
                                        <i class="bi bi-hdd-network-fill"></i> ${d.device_name || 'Unknown'}
                                    </span>
                                    ${badge}
                                </div>
                                <div class="card-body text-center py-4">
                                    ${displayValue}
                                    <p class="text-muted mt-3 mb-0 fw-bold">${subText}</p>
                                </div>
                                <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">Status Pompa:</small><br>
                                        ${pumpStatus}
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Koneksi:</small><br>
                                        <small class="status-online text-success fw-bold">Online</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            $('#dashboard-container').html(html);
        }

        // ===== 4. RENDER DEVICE TABLE =====
        function renderDeviceTable() {
            let html = '';
            
            if(!currentData || currentData.length === 0) {
                html = `
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox"></i> Belum ada perangkat
                        </td>
                    </tr>
                `;
            } else {
                currentData.forEach(d => {
                    // Sinyal indicator (random for demo)
                    let signalStrength = Math.floor(Math.random() * 30) + 70; // 70-100%
                    let signalClass = signalStrength > 80 ? 'bg-success' : 'bg-warning';
                    
                    html += `
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary">${d.device_name || 'Unknown'}</div>
                                <small class="text-muted">IP: ${d.ip_address || 'N/A'}</small>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="bi bi-circle-fill"></i> Online
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 80px; height: 8px;">
                                        <div class="progress-bar ${signalClass}" style="width: ${signalStrength}%"></div>
                                    </div>
                                    <small class="text-muted">${signalStrength}%</small>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> Baru saja
                                </small>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-danger" onclick="rebootDevice('${d.device_name}')">
                                    <i class="bi bi-arrow-clockwise"></i> Reboot
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            
            $('#device-table-body').html(html);
        }

        // ===== 5. UPDATE DEVICE SELECT =====
        function updateDeviceSelect() {
            if($('#setting-device-select option').length <= 1 && currentData.length > 0) {
                let optHtml = '<option disabled selected>Pilih perangkat...</option>';
                currentData.forEach(d => {
                    optHtml += `<option value="${d.device_name}">${d.device_name}</option>`;
                });
                $('#setting-device-select').html(optHtml);
            }
        }

        // ===== 6. REBOOT DEVICE =====
        function rebootDevice(deviceName) {
            if(confirm(`⚠️ Yakin ingin merestart perangkat "${deviceName}"?\n\nAlat akan offline beberapa detik dan restart otomatis.`)) {
                $.post('/api/settings/reboot', { device_id: deviceName }, function(res) {
                    alert('✅ Perintah reboot terkirim!\n\nPerangkat akan restart dalam beberapa detik.');
                }).fail(function() {
                    alert('❌ Gagal mengirim perintah reboot. Periksa koneksi.');
                });
            }
        }

        // ===== 7. MODE SELECTION =====
        let selectedMode = 1;

        function selectMode(mode) {
            selectedMode = mode;
            
            // Update visual
            $('.card-mode').removeClass('selected');
            $('#mode-' + mode).addClass('selected');
            
            // Show/hide config inputs
            $('.config-input').addClass('d-none');
            $('#conf-' + mode).removeClass('d-none');
        }

        // ===== 8. SAVE SETTINGS =====
        function saveSettings() {
            let deviceId = $('#setting-device-select').val();
            
            if(!deviceId) {
                alert('⚠️ Pilih perangkat terlebih dahulu!');
                return;
            }

            let payload = {
                device_id: deviceId,
                mode: selectedMode
            };

            // Add mode-specific parameters
            switch(selectedMode) {
                case 1: // Basic
                    payload.batas_siram = $('#inp-limit-on').val();
                    payload.batas_stop = $('#inp-limit-off').val();
                    break;
                case 3: // Jadwal
                    payload.jam_pagi = $('#inp-pagi').val();
                    payload.jam_sore = $('#inp-sore').val();
                    payload.durasi_siram = $('#inp-duration').val();
                    break;
                case 4: // Manual
                    payload.batas_siram = $('#inp-manual-on').val();
                    payload.batas_stop = $('#inp-manual-off').val();
                    break;
            }

            // Send to backend
            $.post('/api/settings/update', payload, function(res) {
                alert('✅ Pengaturan Berhasil Disimpan!\n\nPerangkat akan menyesuaikan dalam 30 detik saat check-in berikutnya.');
                fetchData(); // Refresh data
            }).fail(function(xhr) {
                alert('❌ Gagal menyimpan pengaturan.\n\n' + (xhr.responseJSON?.message || 'Error tidak diketahui'));
            });
        }

        // ===== AUTO REFRESH DATA =====
        setInterval(fetchData, 3000); // Polling setiap 3 detik
        
        // Initial load
        fetchData();
        selectMode(1); // Default select mode 1

    </script>
</body>
</html>
