<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Universal IoT Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .transition-all { transition: all 0.3s ease; }
        .hidden-page { display: none; }
        .active-nav { background-color: #2563eb; color: white; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); }
        .inactive-nav { color: #94a3b8; }
        .inactive-nav:hover { background-color: #1e293b; color: white; }
    </style>
</head>
<body class="text-slate-800">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Mobile Overlay (background gelap saat menu terbuka) -->
        <div id="mobile-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-20 md:hidden" onclick="toggleMobileMenu()"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-slate-900 text-white fixed h-full z-30 transition-transform duration-300 -translate-x-full md:translate-x-0 md:flex flex-col">
            <div class="p-6 flex items-center gap-3 border-b border-slate-800">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <h1 class="font-bold text-lg tracking-wide">IoT Project</h1>
            </div>
            
            <nav class="flex-1 p-4 space-y-2">
                <button onclick="switchPage('dashboard')" id="nav-dashboard" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all active-nav text-left">
                    <i class="fa-solid fa-gauge-high w-5"></i> <span class="font-medium">Dashboard</span>
                </button>
                <button onclick="switchPage('devices')" id="nav-devices" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all inactive-nav text-left">
                    <i class="fa-solid fa-microchip w-5"></i> <span class="font-medium">Perangkat</span>
                </button>
                <button onclick="switchPage('logs')" id="nav-logs" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all inactive-nav text-left">
                    <i class="fa-solid fa-list-ul w-5"></i> <span class="font-medium">Riwayat Log</span>
                </button>
                <button onclick="switchPage('settings')" id="nav-settings" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all inactive-nav text-left">
                    <i class="fa-solid fa-sliders w-5"></i> <span class="font-medium">Pengaturan</span>
                </button>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3 px-4 py-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs">A</div>
                    <div>
                        <p class="text-sm font-semibold">Admin</p>
                        <p class="text-xs text-slate-500" id="connection-status">Connecting...</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Header -->
        <div class="md:hidden fixed w-full bg-slate-900 text-white z-20 p-4 flex justify-between items-center">
            <span class="font-bold">IoT Dashboard</span>
            <button class="text-white" onclick="toggleMobileMenu()"><i class="fa-solid fa-bars"></i></button>
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 md:ml-64 h-full overflow-y-auto p-4 md:p-8 pt-20 md:pt-8">
            
            <!-- ================= PAGE 1: DASHBOARD ================= -->
            <div id="page-dashboard" class="page-content">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">🌿 Monitoring Tanaman Real-time</h2>
                        <p class="text-slate-500 text-sm mt-1">Pantau kondisi sensor dan status perangkat secara langsung</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75" id="status-ping"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500" id="status-dot"></span>
                            </span>
                            <span class="text-sm font-bold text-green-600" id="online-status">Online</span>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Card 1: Suhu -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-50 rounded-xl text-blue-600"><i class="fa-solid fa-temperature-half text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Sensor Suhu</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="sensor-temp">--°C</h3>
                        <p class="text-xs text-slate-400 mt-2">Update setiap 3 detik</p>
                    </div>
                    
                    <!-- Card 2: Kelembaban Udara -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600"><i class="fa-solid fa-droplet text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Kelembaban Udara</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="sensor-humidity">--%</h3>
                        <p class="text-xs text-slate-400 mt-2">Relative Humidity</p>
                    </div>
                    
                    <!-- Card 3: Kelembaban Tanah -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-green-50 rounded-xl text-green-600"><i class="fa-solid fa-seedling text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Kelembaban Tanah</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="sensor-soil">--%</h3>
                        <p class="text-xs text-slate-400 mt-2">Soil Moisture Level</p>
                    </div>
                    
                    <!-- Card 4: Status Relay -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-amber-50 rounded-xl text-amber-600"><i class="fa-solid fa-lightbulb text-xl"></i></div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="toggleSwitch" class="sr-only peer" onchange="toggleRelay()">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-amber-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            </label>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Status Pompa</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="relay-status">OFF</h3>
                        <p class="text-xs text-slate-400 mt-2">Manual Control</p>
                    </div>
                </div>

                <!-- Device Info Card -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-2xl shadow-lg mb-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold mb-2">📱 <span id="device-name-display">Loading...</span></h3>
                            <p class="text-sm opacity-90">Jenis Tanaman: <span id="plant-type-display" class="font-bold">-</span></p>
                            <p class="text-sm opacity-90">Mode Operasi: <span id="mode-display" class="font-bold">-</span></p>
                            
                            <!-- Auto-Detected Devices -->
                            <div class="mt-4 pt-3 border-t border-white/20">
                                <p class="text-xs opacity-75 mb-2">🔌 Perangkat Terdeteksi Otomatis:</p>
                                <div id="detected-devices-list" class="flex flex-wrap gap-2">
                                    <span class="text-xs bg-white/20 px-2 py-1 rounded">Menunggu data...</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs opacity-75">IP Address</p>
                            <p class="font-mono text-sm" id="device-ip-display">-</p>
                            <p class="text-xs opacity-75 mt-2">Last Update</p>
                            <p class="text-sm font-medium" id="last-update-display">-</p>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-8">
                    <h3 class="font-bold text-lg text-slate-800 mb-4">Grafik Real-time</h3>
                    <div class="relative h-72 w-full"><canvas id="mainChart"></canvas></div>
                </div>
            </div>

            <!-- ================= PAGE 2: PERANGKAT (DEVICES) ================= -->
            <div id="page-devices" class="page-content hidden-page">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Manajemen Perangkat</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="devices-container">
                    <!-- Devices akan di-load via JavaScript -->
                    <div class="text-center py-12 text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i>
                        <p>Loading devices...</p>
                    </div>
                </div>
            </div>

            <!-- ================= PAGE 3: RIWAYAT LOG (LOGS) ================= -->
            <div id="page-logs" class="page-content hidden-page">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">Riwayat Aktivitas</h2>
                    <button class="text-sm text-blue-600 font-semibold hover:underline" onclick="refreshLogs()">
                        <i class="fa-solid fa-refresh mr-1"></i> Refresh
                    </button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="p-4">Waktu</th>
                                <th class="p-4">Level</th>
                                <th class="p-4">Perangkat</th>
                                <th class="p-4">Pesan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100" id="logs-tbody">
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400">
                                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i>
                                    <p>Loading logs...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================= PAGE 4: PENGATURAN (SETTINGS) ================= -->
            <div id="page-settings" class="page-content hidden-page">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">⚙️ Pengaturan Sistem</h2>
                        <p class="text-slate-500 text-sm mt-1">Konfigurasi mode operasi dan strategi penyiraman</p>
                    </div>
                </div>
                
                <!-- Smart Config Card (MOVED FROM DASHBOARD) -->
                <div class="bg-gradient-to-r from-red-500 to-red-600 p-8 rounded-2xl shadow-xl mb-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">🎮 Atur Strategi Penyiraman</h3>
                            <p class="text-red-100 mb-4">Pilih metode perawatan yang paling sesuai dengan kebutuhan tanaman Anda</p>
                            <button onclick="openSmartConfigModal()" class="px-8 py-3 bg-white text-red-600 rounded-xl hover:bg-red-50 font-bold shadow-lg transition-all flex items-center gap-2">
                                <i class="fa-solid fa-gear"></i>
                                Buka Wizard Pengaturan
                            </button>
                        </div>
                        <div class="hidden lg:block text-8xl opacity-20">
                            <i class="fa-solid fa-seedling"></i>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Card Info Mode Aktif -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i class="fa-solid fa-info-circle"></i></div>
                            <h3 class="font-bold text-lg">Mode Operasi Aktif</h3>
                        </div>
                        
                        <div id="current-mode-info" class="space-y-4">
                            <div class="bg-slate-50 p-4 rounded-xl">
                                <p class="text-sm text-slate-500 mb-2">Device:</p>
                                <p class="font-bold text-lg" id="settings-device-name">Loading...</p>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-xl">
                                <p class="text-sm text-slate-500 mb-2">Mode Saat Ini:</p>
                                <p class="font-bold text-lg" id="settings-current-mode">-</p>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-xl">
                                <p class="text-sm text-slate-500 mb-2">Tanaman:</p>
                                <p class="font-bold text-lg capitalize" id="settings-plant-type">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card API Info -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                            <div class="p-2 bg-purple-50 text-purple-600 rounded-lg"><i class="fa-solid fa-code"></i></div>
                            <h3 class="font-bold text-lg">API Endpoints</h3>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500 mb-1">Insert Data</p>
                                <code class="text-xs font-mono text-slate-700">POST /api/monitoring/insert</code>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500 mb-1">Latest Data</p>
                                <code class="text-xs font-mono text-slate-700">GET /api/monitoring/latest</code>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500 mb-1">Device Check-in</p>
                                <code class="text-xs font-mono text-slate-700">GET /api/device/check-in</code>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500 mb-1">Update Mode</p>
                                <code class="text-xs font-mono text-slate-700">POST /api/devices/{id}/mode</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ================= MODAL: SMART CONFIG (WIZARD STYLE) ================= -->
    <div id="smartConfigModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="if(event.target.id === 'smartConfigModal') closeSmartConfigModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-slate-200 bg-gradient-to-r from-red-500 to-red-600">
                <div>
                    <h3 class="text-xl font-bold text-white">🎮 Pilih Metode Perawatan Tanaman</h3>
                    <p class="text-sm text-red-100 mt-1">Pilih strategi yang paling sesuai dengan kebutuhan Anda</p>
                </div>
                <button onclick="closeSmartConfigModal()" class="text-white hover:text-red-100">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 bg-slate-50">
                <!-- Device Selection -->
                <div class="mb-6 bg-white p-4 rounded-xl shadow-sm">
                    <label class="block text-sm font-bold text-slate-700 mb-3">📱 Pilih Perangkat:</label>
                    <select id="config-device-id" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-red-500 font-medium">
                        <option value="">Loading devices...</option>
                    </select>
                </div>

                <!-- Mode Selection Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Mode 1: Pemula -->
                    <div id="card-mode-1" class="mode-card bg-white rounded-xl shadow-sm border-2 border-transparent hover:border-green-500 cursor-pointer transition-all p-6" onclick="selectSmartMode(1)">
                        <div class="text-center">
                            <div class="text-6xl mb-3">🌱</div>
                            <h5 class="text-lg font-bold text-slate-800 mb-2">Mode Pemula</h5>
                            <p class="text-sm text-slate-600 mb-3">Paling mudah. Siram otomatis jika tanah kering (< 40%). Tanpa ribet.</p>
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">✅ Rekomendasi Awal</span>
                        </div>
                    </div>

                    <!-- Mode 2: AI Fuzzy -->
                    <div id="card-mode-2" class="mode-card bg-white rounded-xl shadow-sm border-2 border-transparent hover:border-blue-500 cursor-pointer transition-all p-6" onclick="selectSmartMode(2)">
                        <div class="text-center">
                            <div class="text-6xl mb-3">🤖</div>
                            <h5 class="text-lg font-bold text-slate-800 mb-2">Mode AI (Fuzzy)</h5>
                            <p class="text-sm text-slate-600 mb-3">Hemat air & presisi. Menyesuaikan siraman dengan suhu udara panas/dingin.</p>
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">⭐ Paling Efisien</span>
                        </div>
                    </div>

                    <!-- Mode 3: Jadwal -->
                    <div id="card-mode-3" class="mode-card bg-white rounded-xl shadow-sm border-2 border-transparent hover:border-yellow-500 cursor-pointer transition-all p-6" onclick="selectSmartMode(3)">
                        <div class="text-center">
                            <div class="text-6xl mb-3">📅</div>
                            <h5 class="text-lg font-bold text-slate-800 mb-2">Mode Terjadwal</h5>
                            <p class="text-sm text-slate-600 mb-3">Siram rutin pagi & sore. Cocok untuk pembiasaan tanaman.</p>
                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">⏰ Teratur</span>
                        </div>
                    </div>

                    <!-- Mode 4: Manual -->
                    <div id="card-mode-4" class="mode-card bg-white rounded-xl shadow-sm border-2 border-transparent hover:border-slate-500 cursor-pointer transition-all p-6" onclick="selectSmartMode(4)">
                        <div class="text-center">
                            <div class="text-6xl mb-3">🛠️</div>
                            <h5 class="text-lg font-bold text-slate-800 mb-2">Mode Manual</h5>
                            <p class="text-sm text-slate-600 mb-3">Kendali penuh. Anda tentukan sendiri kapan pompa menyala.</p>
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full">🎛️ Advanced</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Settings Area -->
                <div id="detail-settings" class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hidden">
                    <h6 class="text-lg font-bold text-slate-800 mb-4 pb-3 border-b border-slate-200">⚙️ Konfigurasi Detail</h6>
                    
                    <input type="hidden" id="selected-mode" value="1">
                    
                    <!-- Message for Auto Modes (1 & 2) -->
                    <div id="msg-auto" class="hidden config-group">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-info-circle text-blue-600 text-xl mt-1"></i>
                                <div>
                                    <p class="font-semibold text-blue-800 mb-1">Mode Otomatis Aktif</p>
                                    <p class="text-sm text-blue-700">Sistem akan mengatur semuanya secara otomatis. Anda tidak perlu mengubah apa-apa. Cukup klik <strong>Simpan & Terapkan</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input for Mode 3: Schedule -->
                    <div id="input-jadwal" class="hidden config-group space-y-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                            <p class="text-sm text-yellow-800">
                                <i class="fa-solid fa-lightbulb mr-2"></i>
                                Tentukan jam penyiraman pagi dan sore. Sistem akan menyiram secara otomatis pada waktu yang ditentukan.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">⏰ Jam Pagi:</label>
                                <input type="time" id="conf-pagi" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-yellow-500" value="07:00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">🌅 Jam Sore:</label>
                                <input type="time" id="conf-sore" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-yellow-500" value="17:00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">⏱️ Durasi Siram (detik):</label>
                            <input type="number" id="conf-durasi" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-yellow-500" value="5" min="1" max="60">
                        </div>
                    </div>

                    <!-- Input for Mode 4: Manual -->
                    <div id="input-manual" class="hidden config-group space-y-4">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4">
                            <p class="text-sm text-slate-700">
                                <i class="fa-solid fa-sliders mr-2"></i>
                                Geser slider untuk menentukan kapan pompa harus menyala berdasarkan kelembapan tanah.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">
                                Batas Kelembapan Kering (Pompa ON):
                            </label>
                            <div class="flex items-center gap-4">
                                <input type="range" id="range-manual" class="flex-grow-1 w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer" min="0" max="100" value="40" oninput="document.getElementById('val-manual').textContent = this.value + '%'">
                                <span id="val-manual" class="px-4 py-2 bg-slate-800 text-white rounded-lg font-bold text-lg min-w-[70px] text-center">40%</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Pompa akan menyala jika kelembapan di bawah angka ini.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">
                                Batas Kelembapan Basah (Pompa OFF):
                            </label>
                            <div class="flex items-center gap-4">
                                <input type="range" id="range-manual-stop" class="flex-grow-1 w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer" min="0" max="100" value="70" oninput="document.getElementById('val-manual-stop').textContent = this.value + '%'">
                                <span id="val-manual-stop" class="px-4 py-2 bg-slate-800 text-white rounded-lg font-bold text-lg min-w-[70px] text-center">70%</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Pompa akan mati jika kelembapan mencapai angka ini atau lebih.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kalibrasi Sensor (Teknisi Only) -->
            <div class="p-6 border-t border-slate-200 bg-amber-50">
                <div class="flex items-start gap-3 mb-4">
                    <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                        <i class="fa-solid fa-wrench text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 mb-1">🔧 Kalibrasi Sensor (Teknisi)</h4>
                        <p class="text-sm text-slate-600">
                            Sesuaikan nilai ADC sensor untuk akurasi optimal. Perubahan ini akan otomatis dikirim ke Pico W tanpa upload ulang code.
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-solid fa-sun text-amber-500 mr-1"></i>
                            Nilai ADC Kering (Udara):
                        </label>
                        <input type="number" id="input-adc-min" 
                               class="w-full px-4 py-2 border-2 border-amber-200 rounded-xl focus:outline-none focus:border-amber-500" 
                               value="4095" min="0" max="4095" placeholder="Default: 4095">
                        <p class="text-xs text-slate-500 mt-1">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Nilai ADC saat sensor di udara (kering maksimal)
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-solid fa-droplet text-blue-500 mr-1"></i>
                            Nilai ADC Basah (Air):
                        </label>
                        <input type="number" id="input-adc-max" 
                               class="w-full px-4 py-2 border-2 border-blue-200 rounded-xl focus:outline-none focus:border-blue-500" 
                               value="1500" min="0" max="4095" placeholder="Default: 1500">
                        <p class="text-xs text-slate-500 mt-1">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Nilai ADC saat sensor di air (basah maksimal)
                        </p>
                    </div>
                </div>
                
                <div class="mt-3 p-3 bg-white border border-amber-200 rounded-lg">
                    <p class="text-xs text-slate-600">
                        <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i>
                        <strong>Cara Kalibrasi:</strong> 1) Ukur sensor di udara (catat nilai), 2) Celupkan ke air (catat nilai), 3) Masukkan kedua nilai di atas, 4) Simpan. Pico akan update otomatis dalam 10 detik.
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-between items-center p-6 border-t border-slate-200 bg-slate-50">
                <button onclick="closeSmartConfigModal()" class="px-6 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                <button onclick="saveSmartConfiguration()" class="px-8 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 font-bold shadow-lg shadow-green-500/30 transition-all">
                    <i class="fa-solid fa-check mr-2"></i> Simpan & Terapkan
                </button>
            </div>
        </div>
    </div>

    <!-- ================= MODAL: MODE SELECTION (OLD - Keep for device management page) ================= -->
    <div id="modeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" onclick="if(event.target.id === 'modeModal') closeModeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-slate-200">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">🎮 Pilih Strategi Penyiraman</h3>
                    <p class="text-sm text-slate-500 mt-1">Ubah mode operasi tanpa upload ulang Arduino</p>
                </div>
                <button onclick="closeModeModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <input type="hidden" id="modal-device-id">
                
                <!-- Mode Selection -->
                <label class="block text-sm font-bold text-slate-700 mb-3">Mode Operasi:</label>
                <select id="select-mode" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 mb-6 font-medium" onchange="showModeOptions()">
                    <option value="1">🟢 Mode 1: Basic Threshold (Sederhana)</option>
                    <option value="2">🔵 Mode 2: Fuzzy Logic AI (Cerdas)</option>
                    <option value="3">🔴 Mode 3: Schedule Timer (Terjadwal)</option>
                </select>

                <!-- Mode 1: Basic Options -->
                <div id="opt-mode-1" class="mode-opt space-y-4">
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-lightbulb text-green-600 text-xl mt-1"></i>
                            <div>
                                <p class="font-semibold text-green-800 mb-1">Mode Basic Threshold</p>
                                <p class="text-sm text-green-700">Pompa menyala jika kelembapan tanah di bawah batas, dan mati jika sudah mencukupi. Cocok untuk greenhouse dengan kondisi stabil.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Batas Kering - Pompa ON (%):</label>
                        <input type="number" id="input-batas-siram" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500" value="40" min="0" max="100">
                        <p class="text-xs text-slate-500 mt-1">Pompa akan hidup jika kelembapan < nilai ini</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Batas Basah - Pompa OFF (%):</label>
                        <input type="number" id="input-batas-stop" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500" value="70" min="0" max="100">
                        <p class="text-xs text-slate-500 mt-1">Pompa akan mati jika kelembapan >= nilai ini</p>
                    </div>
                </div>

                <!-- Mode 2: Fuzzy Logic Options -->
                <div id="opt-mode-2" class="mode-opt hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-robot text-blue-600 text-xl mt-1"></i>
                            <div>
                                <p class="font-semibold text-blue-800 mb-2">🤖 Fuzzy Logic AI - Fully Automatic</p>
                                <p class="text-sm text-blue-700 mb-3">Sistem AI menghitung durasi siram secara otomatis berdasarkan <strong>Suhu Udara</strong> dan <strong>Kelembapan Tanah</strong>. Tidak ada pengaturan manual!</p>
                                
                                <div class="bg-white rounded-lg p-3 space-y-2 text-sm">
                                    <p class="font-semibold text-slate-700">Logika Fuzzy:</p>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-fire text-red-500"></i>
                                        <span class="text-slate-600">Kering + Panas (>30°C) = Siram <strong class="text-slate-800">8 detik</strong></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-sun text-yellow-500"></i>
                                        <span class="text-slate-600">Kering + Sedang (25-30°C) = Siram <strong class="text-slate-800">5 detik</strong></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-snowflake text-blue-500"></i>
                                        <span class="text-slate-600">Kering + Dingin (<25°C) = Siram <strong class="text-slate-800">3 detik</strong></span>
                                    </div>
                                </div>

                                <div class="mt-3 text-xs text-blue-600">
                                    <i class="fa-solid fa-check-circle"></i> Hemat air hingga 30% | Adaptif cuaca | Zero configuration
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mode 3: Schedule Options -->
                <div id="opt-mode-3" class="mode-opt hidden space-y-4">
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-clock text-red-600 text-xl mt-1"></i>
                            <div>
                                <p class="font-semibold text-red-800 mb-1">Mode Schedule Timer</p>
                                <p class="text-sm text-red-700">Siram otomatis pada jam yang ditentukan, tidak bergantung pada sensor. Cocok untuk tanaman dengan rutinitas tetap.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">⏰ Jadwal Pagi:</label>
                            <input type="time" id="input-jam-pagi" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500" value="07:00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">🌅 Jadwal Sore:</label>
                            <input type="time" id="input-jam-sore" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500" value="17:00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">⏱️ Durasi Siram (detik):</label>
                        <input type="number" id="input-durasi" class="w-full px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-blue-500" value="5" min="1" max="60">
                        <p class="text-xs text-slate-500 mt-1">Lama pompa menyala setiap jadwal</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-between items-center p-6 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                <button onclick="closeModeModal()" class="px-6 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                <button onclick="saveMode()" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-semibold shadow-lg shadow-blue-500/30 transition-all">
                    <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <script>
        // --- CONFIGURATION ---
        const API_BASE_URL = '/api/monitoring';
        const UPDATE_INTERVAL = 3000; // 3 seconds

        // --- PAGE SWITCHING LOGIC ---
        function switchPage(pageId) {
            document.querySelectorAll('.page-content').forEach(page => {
                page.classList.add('hidden-page');
            });
            document.getElementById('page-' + pageId).classList.remove('hidden-page');

            document.querySelectorAll('nav button').forEach(btn => {
                btn.classList.remove('active-nav');
                btn.classList.add('inactive-nav');
            });
            document.getElementById('nav-' + pageId).classList.add('active-nav');
            document.getElementById('nav-' + pageId).classList.remove('inactive-nav');

            // Load data based on page
            if (pageId === 'logs') {
                loadLogs();
            } else if (pageId === 'devices') {
                loadDevices();
            }
        }

        // --- CHART SETUP ---
        const ctx = document.getElementById('mainChart').getContext('2d');
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        const mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Suhu (°C)',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: false, grid: { borderDash: [5, 5], color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // --- API FUNCTIONS ---
        async function fetchStats() {
            try {
                const response = await axios.get(`${API_BASE_URL}/stats`);
                if (response.data.success) {
                    const data = response.data.data;
                    
                    // Update sensor cards
                    document.getElementById('sensor-temp').textContent = 
                        data.temperature ? `${data.temperature.toFixed(1)}°C` : '--°C';
                    document.getElementById('sensor-humidity').textContent = 
                        data.humidity ? `${data.humidity.toFixed(0)}%` : '--%';
                    document.getElementById('sensor-soil').textContent = 
                        data.soil_moisture ? `${data.soil_moisture.toFixed(0)}%` : '--%';
                    document.getElementById('relay-status').textContent = 
                        data.relay_status ? 'ON' : 'OFF';
                    
                    // Update toggle switch
                    document.getElementById('toggleSwitch').checked = data.relay_status;
                    
                    // Update device info card (Dashboard)
                    document.getElementById('device-name-display').textContent = 
                        data.device_name || 'Smart Garden Device';
                    document.getElementById('plant-type-display').textContent = 
                        data.plant_type || '-';
                    
                    // Mode mapping
                    const modeNames = {
                        1: '🟢 Mode Pemula',
                        2: '🤖 Mode AI Fuzzy',
                        3: '📅 Mode Terjadwal',
                        4: '🛠️ Mode Manual'
                    };
                    document.getElementById('mode-display').textContent = 
                        modeNames[data.mode] || '-';
                    
                    document.getElementById('device-ip-display').textContent = 
                        data.ip_address || '-';
                    document.getElementById('last-update-display').textContent = 
                        new Date().toLocaleTimeString('id-ID');
                    
                    // Update detected devices list
                    const deviceListContainer = document.getElementById('detected-devices-list');
                    if (data.connected_devices) {
                        const devices = data.connected_devices.split(',');
                        let html = '';
                        devices.forEach(dev => {
                            dev = dev.trim();
                            if(dev) {
                                // Icon mapping
                                let icon = 'fa-microchip';
                                if(dev.includes('DHT')) icon = 'fa-temperature-high';
                                if(dev.includes('LCD')) icon = 'fa-tv';
                                if(dev.includes('Servo')) icon = 'fa-gears';
                                if(dev.includes('Soil')) icon = 'fa-droplet';
                                if(dev.includes('Relay')) icon = 'fa-toggle-on';
                                
                                html += `<span class="flex items-center gap-1 px-2 py-1 bg-white text-blue-600 text-xs font-bold rounded-lg shadow-sm">
                                    <i class="fa-solid ${icon}"></i> ${dev}
                                </span>`;
                            }
                        });
                        deviceListContainer.innerHTML = html || '<span class="text-xs bg-white/20 px-2 py-1 rounded">Tidak ada data</span>';
                    } else {
                        deviceListContainer.innerHTML = '<span class="text-xs bg-white/20 px-2 py-1 rounded">Menunggu data...</span>';
                    }
                    
                    // Update settings page info
                    if (document.getElementById('settings-device-name')) {
                        document.getElementById('settings-device-name').textContent = 
                            data.device_name || 'Smart Garden Device';
                    }
                    if (document.getElementById('settings-current-mode')) {
                        document.getElementById('settings-current-mode').textContent = 
                            modeNames[data.mode] || '-';
                    }
                    if (document.getElementById('settings-plant-type')) {
                        document.getElementById('settings-plant-type').textContent = 
                            data.plant_type || '-';
                    }
                    
                    // Update status
                    updateConnectionStatus(true);
                }
            } catch (error) {
                console.error('Error fetching stats:', error);
                updateConnectionStatus(false);
            }
        }

        async function fetchHistory() {
            try {
                const response = await axios.get(`${API_BASE_URL}/history?limit=20`);
                if (response.data.success && response.data.data.length > 0) {
                    const data = response.data.data;
                    
                    // Update chart
                    mainChart.data.labels = data.map(item => {
                        const date = new Date(item.created_at);
                        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    });
                    mainChart.data.datasets[0].data = data.map(item => item.temperature || 0);
                    mainChart.update();
                }
            } catch (error) {
                console.error('Error fetching history:', error);
            }
        }

        async function loadLogs() {
            try {
                const response = await axios.get(`${API_BASE_URL}/logs?limit=20`);
                const tbody = document.getElementById('logs-tbody');
                
                if (response.data.success && response.data.data.length > 0) {
                    const logs = response.data.data;
                    tbody.innerHTML = logs.map(log => {
                        const levelColors = {
                            'INFO': 'bg-blue-100 text-blue-700',
                            'SUCCESS': 'bg-green-100 text-green-700',
                            'WARN': 'bg-amber-100 text-amber-700',
                            'ERROR': 'bg-red-100 text-red-700'
                        };
                        const levelClass = levelColors[log.level] || 'bg-slate-100 text-slate-700';
                        
                        return `
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-mono text-slate-500">${log.time}</td>
                                <td class="p-4"><span class="px-2 py-1 ${levelClass} rounded text-xs font-bold">${log.level}</span></td>
                                <td class="p-4 text-slate-700">${log.device}</td>
                                <td class="p-4 text-slate-600">${log.message}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-slate-400">Belum ada log</td></tr>';
                }
            } catch (error) {
                console.error('Error loading logs:', error);
                document.getElementById('logs-tbody').innerHTML = 
                    '<tr><td colspan="4" class="p-8 text-center text-red-400">Error loading logs</td></tr>';
            }
        }

        async function loadDevices() {
            const container = document.getElementById('devices-container');
            
            try {
                const response = await axios.get('/api/devices'); 
                const devices = response.data.data;
                
                if (devices && devices.length > 0) {
                    container.innerHTML = devices.map(device => {
                        
                        // 1. Cek Status Online/Offline (Hitung selisih waktu data terakhir)
                        let isOnline = false;
                        if(device.created_at) {
                            const lastSeen = new Date(device.created_at);
                            const now = new Date();
                            const diffSeconds = (now - lastSeen) / 1000;
                            isOnline = diffSeconds < 60; // Online jika ada data masuk < 60 detik lalu
                        }

                        const statusBadge = isOnline 
                            ? '<span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-300 shadow-sm"><i class="fa-solid fa-circle text-[8px] mr-1"></i> ONLINE</span>'
                            : '<span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full border border-red-300 shadow-sm"><i class="fa-solid fa-circle text-[8px] mr-1"></i> OFFLINE</span>';

                        // 2. Tampilkan Sensor yang Terdeteksi
                        let sensorListHtml = '';
                        if (device.connected_devices) {
                            const sensors = device.connected_devices.split(',');
                            
                            sensors.forEach(s => {
                                s = s.trim();
                                // Filter "Pico W" agar tidak muncul dua kali, dan pastikan string tidak kosong
                                if(s && s !== "Pico W") { 
                                    // Tentukan Icon & Warna tiap sensor
                                    let icon = 'fa-microchip';
                                    let color = 'text-slate-600 bg-slate-50 border-slate-200';
                                    
                                    if(s.includes('DHT')) { icon = 'fa-temperature-high'; color = 'text-orange-600 bg-orange-50 border-orange-200'; }
                                    else if(s.includes('Soil')) { icon = 'fa-water'; color = 'text-blue-600 bg-blue-50 border-blue-200'; }
                                    else if(s.includes('LCD') || s.includes('OLED')) { icon = 'fa-tv'; color = 'text-cyan-600 bg-cyan-50 border-cyan-200'; }
                                    else if(s.includes('Relay')) { icon = 'fa-bolt'; color = 'text-yellow-600 bg-yellow-50 border-yellow-200'; }
                                    else if(s.includes('Servo')) { icon = 'fa-gears'; color = 'text-purple-600 bg-purple-50 border-purple-200'; }

                                    // Render Baris Sensor
                                    sensorListHtml += `
                                        <div class="flex items-center justify-between p-3 rounded-lg border ${color} mb-2">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                                    <i class="fa-solid ${icon}"></i>
                                                </div>
                                                <span class="font-semibold text-sm">${s}</span>
                                            </div>
                                            <div class="flex items-center gap-1 text-green-600 text-xs font-bold bg-white px-2 py-1 rounded-md shadow-sm">
                                                <i class="fa-solid fa-check"></i> OK
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                        } 
                        
                        if (sensorListHtml === '') {
                            sensorListHtml = `
                                <div class="text-center p-4 border-2 border-dashed border-slate-200 rounded-xl text-slate-400">
                                    <i class="fa-solid fa-plug-circle-xmark text-xl mb-1"></i>
                                    <p class="text-xs">Tidak ada sensor terdeteksi</p>
                                </div>
                            `;
                        }

                        // 3. Render Card Utama
                        return `
                            <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-100 relative overflow-hidden group">
                                <div class="flex justify-between items-start mb-6 relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 bg-slate-900 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-slate-300">
                                            <i class="fa-brands fa-raspberry-pi text-2xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-lg text-slate-800 leading-tight">${device.device_name || 'Smart Garden'}</h3>
                                            <p class="text-xs text-slate-500 font-mono mt-1">${device.device_id}</p>
                                        </div>
                                    </div>
                                    ${statusBadge}
                                </div>
                                
                                <div class="relative z-10">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                                        Hardware Terhubung
                                    </h4>
                                    <div class="space-y-1">
                                        ${sensorListHtml}
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
                                    <span><i class="fa-regular fa-clock mr-1"></i> Update: ${device.created_at ? new Date(device.created_at).toLocaleTimeString('id-ID') : '-'}</span>
                                    <span>IP: ${device.ip_address || '-'}</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    // Tampilan jika belum ada device sama sekali
                    container.innerHTML = `
                        <div class="col-span-full text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300">
                            <div class="inline-block p-4 bg-slate-50 rounded-full mb-4">
                                <i class="fa-solid fa-satellite-dish text-4xl text-slate-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700">Belum Ada Perangkat</h3>
                            <p class="text-slate-500 text-sm mt-1">Nyalakan Pico W Anda untuk memulai deteksi otomatis.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading devices:', error);
                container.innerHTML = `
                    <div class="col-span-full text-center text-red-400 py-12">
                        <i class="fa-solid fa-exclamation-triangle text-3xl mb-3"></i>
                        <p>Error loading devices</p>
                    </div>
                `;
            }
        }

        async function toggleRelay() {
            const isChecked = document.getElementById('toggleSwitch').checked;
            
            try {
                const response = await axios.post(`${API_BASE_URL}/relay/toggle`, {
                    status: isChecked
                });
                
                if (response.data.success) {
                    document.getElementById('relay-status').textContent = isChecked ? 'ON' : 'OFF';
                    if (isChecked) {
                        document.getElementById('relay-status').classList.add('text-amber-500');
                        document.getElementById('relay-status').classList.remove('text-slate-800');
                    } else {
                        document.getElementById('relay-status').classList.remove('text-amber-500');
                        document.getElementById('relay-status').classList.add('text-slate-800');
                    }
                }
            } catch (error) {
                console.error('Error toggling relay:', error);
                // Revert switch on error
                document.getElementById('toggleSwitch').checked = !isChecked;
            }
        }

        function updateConnectionStatus(isOnline) {
            const statusText = document.getElementById('online-status');
            const statusDot = document.getElementById('status-dot');
            const statusPing = document.getElementById('status-ping');
            const connectionStatus = document.getElementById('connection-status');
            
            if (isOnline) {
                statusText.textContent = 'Online';
                statusText.classList.remove('text-red-600');
                statusText.classList.add('text-green-600');
                statusDot.classList.remove('bg-red-500');
                statusDot.classList.add('bg-green-500');
                statusPing.classList.remove('bg-red-400');
                statusPing.classList.add('bg-green-400');
                connectionStatus.textContent = 'Online';
                connectionStatus.classList.remove('text-red-500');
                connectionStatus.classList.add('text-slate-500');
            } else {
                statusText.textContent = 'Offline';
                statusText.classList.remove('text-green-600');
                statusText.classList.add('text-red-600');
                statusDot.classList.remove('bg-green-500');
                statusDot.classList.add('bg-red-500');
                statusPing.classList.remove('bg-green-400');
                statusPing.classList.add('bg-red-400');
                connectionStatus.textContent = 'Offline';
                connectionStatus.classList.remove('text-slate-500');
                connectionStatus.classList.add('text-red-500');
            }
        }

        function refreshLogs() {
            loadLogs();
        }

        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            // Toggle sidebar: geser masuk/keluar
            sidebar.classList.toggle('-translate-x-full');
            
            // Toggle overlay (background gelap)
            overlay.classList.toggle('hidden');
        }

        // --- SMART CONFIG MODAL FUNCTIONS ---
        function openSmartConfigModal() {
            document.getElementById('smartConfigModal').classList.remove('hidden');
            document.getElementById('smartConfigModal').classList.add('flex');
            
            // Load devices for selection
            loadDevicesForConfig();
            
            // Default select Mode 1
            selectSmartMode(1);
        }

        function closeSmartConfigModal() {
            document.getElementById('smartConfigModal').classList.add('hidden');
            document.getElementById('smartConfigModal').classList.remove('flex');
        }

        async function loadDevicesForConfig() {
            try {
                const response = await axios.get('/api/devices');
                const devices = response.data.data;
                const select = document.getElementById('config-device-id');
                
                if (devices && devices.length > 0) {
                    select.innerHTML = devices.map(device => 
                        `<option value="${device.id}">${device.device_name || device.device_id} (${device.plant_type})</option>`
                    ).join('');
                    
                    // Load kalibrasi ADC dari device pertama
                    const firstDevice = devices[0];
                    document.getElementById('input-adc-min').value = firstDevice.sensor_min || 4095;
                    document.getElementById('input-adc-max').value = firstDevice.sensor_max || 1500;
                    
                    // Add event listener untuk update ADC saat device berubah
                    select.addEventListener('change', async (e) => {
                        const selectedDevice = devices.find(d => d.id == e.target.value);
                        if (selectedDevice) {
                            document.getElementById('input-adc-min').value = selectedDevice.sensor_min || 4095;
                            document.getElementById('input-adc-max').value = selectedDevice.sensor_max || 1500;
                        }
                    });
                } else {
                    select.innerHTML = '<option value="">Tidak ada perangkat tersedia</option>';
                }
            } catch (error) {
                console.error('Error loading devices for config:', error);
                document.getElementById('config-device-id').innerHTML = '<option value="">Error loading devices</option>';
            }
        }

        function selectSmartMode(mode) {
            // Reset all cards
            document.querySelectorAll('.mode-card').forEach(card => {
                card.classList.remove('border-green-500', 'border-blue-500', 'border-yellow-500', 'border-slate-500', 'bg-green-50', 'bg-blue-50', 'bg-yellow-50', 'bg-slate-50', 'ring-4', 'ring-green-200', 'ring-blue-200', 'ring-yellow-200', 'ring-slate-200');
            });
            
            // Highlight selected card
            const selectedCard = document.getElementById(`card-mode-${mode}`);
            if (mode === 1) {
                selectedCard.classList.add('border-green-500', 'bg-green-50', 'ring-4', 'ring-green-200');
            } else if (mode === 2) {
                selectedCard.classList.add('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-200');
            } else if (mode === 3) {
                selectedCard.classList.add('border-yellow-500', 'bg-yellow-50', 'ring-4', 'ring-yellow-200');
            } else if (mode === 4) {
                selectedCard.classList.add('border-slate-500', 'bg-slate-50', 'ring-4', 'ring-slate-200');
            }
            
            // Save selected mode
            document.getElementById('selected-mode').value = mode;
            
            // Show detail settings area
            document.getElementById('detail-settings').classList.remove('hidden');
            
            // Hide all config groups first
            document.querySelectorAll('.config-group').forEach(group => {
                group.classList.add('hidden');
            });
            
            // Show appropriate config based on mode
            if (mode === 1 || mode === 2) {
                // Mode Pemula & AI Fuzzy: Auto (no config needed)
                document.getElementById('msg-auto').classList.remove('hidden');
            } else if (mode === 3) {
                // Mode Jadwal: Show time inputs
                document.getElementById('input-jadwal').classList.remove('hidden');
            } else if (mode === 4) {
                // Mode Manual: Show sliders
                document.getElementById('input-manual').classList.remove('hidden');
            }
        }

        async function saveSmartConfiguration() {
            const deviceId = document.getElementById('config-device-id').value;
            const mode = parseInt(document.getElementById('selected-mode').value);
            
            if (!deviceId) {
                alert('⚠️ Silakan pilih perangkat terlebih dahulu!');
                return;
            }
            
            // Build request data based on mode
            const requestData = { mode };
            
            // === KALIBRASI ADC (ALWAYS SEND) ===
            const adcMin = parseInt(document.getElementById('input-adc-min').value);
            const adcMax = parseInt(document.getElementById('input-adc-max').value);
            
            // Validation: ADC Min must be greater than ADC Max
            if (adcMin <= adcMax) {
                alert('⚠️ Nilai ADC Kering harus lebih besar dari ADC Basah!\n\nContoh: Kering=4095, Basah=1500');
                return;
            }
            
            requestData.sensor_min = adcMin;
            requestData.sensor_max = adcMax;
            
            if (mode === 1) {
                // Mode Pemula: Force to standard (40% ON, 70% OFF)
                requestData.batas_siram = 40;
                requestData.batas_stop = 70;
            } else if (mode === 2) {
                // Mode AI Fuzzy: No additional parameters (fully automatic)
                // Backend will handle fuzzy logic
            } else if (mode === 3) {
                // Mode Jadwal: Get schedule times
                requestData.jam_pagi = document.getElementById('conf-pagi').value;
                requestData.jam_sore = document.getElementById('conf-sore').value;
                requestData.durasi_siram = parseInt(document.getElementById('conf-durasi').value);
            } else if (mode === 4) {
                // Mode Manual: Get user-defined thresholds
                requestData.batas_siram = parseInt(document.getElementById('range-manual').value);
                requestData.batas_stop = parseInt(document.getElementById('range-manual-stop').value);
                
                // Validation: batas_stop must be greater than batas_siram
                if (requestData.batas_stop <= requestData.batas_siram) {
                    alert('⚠️ Batas Basah (OFF) harus lebih tinggi dari Batas Kering (ON)!');
                    return;
                }
            }
            
            try {
                const response = await axios.post(`/api/devices/${deviceId}/mode`, requestData);
                
                if (response.data.success) {
                    // Show success message with mode name
                    const modeNames = {
                        1: '🌱 Mode Pemula',
                        2: '🤖 Mode AI (Fuzzy)',
                        3: '📅 Mode Terjadwal',
                        4: '🛠️ Mode Manual'
                    };
                    
                    alert(`✅ Berhasil! ${modeNames[mode]} + Kalibrasi ADC telah diterapkan.\n\n🔄 Pico W akan update konfigurasi dalam 10 detik.\n📊 ADC Range: ${adcMin} (kering) → ${adcMax} (basah)`);
                    
                    // Close modal
                    closeSmartConfigModal();
                    
                    // Refresh dashboard if on devices page
                    if (!document.getElementById('page-devices').classList.contains('hidden-page')) {
                        loadDevices();
                    }
                    
                    // Refresh stats
                    fetchStats();
                } else {
                    alert('❌ Gagal menyimpan pengaturan: ' + (response.data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving smart configuration:', error);
                alert('❌ Error: ' + (error.response?.data?.message || 'Network error'));
            }
        }

        // --- MODAL FUNCTIONS (OLD - for device management page) ---
        function openModeModal(deviceId, currentMode, deviceData) {
            document.getElementById('modeModal').classList.remove('hidden');
            document.getElementById('modeModal').classList.add('flex');
            document.getElementById('modal-device-id').value = deviceId;
            document.getElementById('select-mode').value = currentMode;
            
            // Parse device data if passed as string
            if (typeof deviceData === 'string') {
                try {
                    deviceData = JSON.parse(deviceData.replace(/&quot;/g, '"'));
                } catch (e) {
                    console.error('Error parsing device data:', e);
                }
            }
            
            // Populate form with current values
            if (deviceData) {
                document.getElementById('input-batas-siram').value = deviceData.batas_siram || 40;
                document.getElementById('input-batas-stop').value = deviceData.batas_stop || 70;
                document.getElementById('input-jam-pagi').value = deviceData.jam_pagi ? deviceData.jam_pagi.substring(0, 5) : '07:00';
                document.getElementById('input-jam-sore').value = deviceData.jam_sore ? deviceData.jam_sore.substring(0, 5) : '17:00';
                document.getElementById('input-durasi').value = deviceData.durasi_siram || 5;
            }
            
            showModeOptions();
        }

        function closeModeModal() {
            document.getElementById('modeModal').classList.add('hidden');
            document.getElementById('modeModal').classList.remove('flex');
        }

        function showModeOptions() {
            const mode = document.getElementById('select-mode').value;
            
            // Hide all mode options
            document.querySelectorAll('.mode-opt').forEach(opt => {
                opt.classList.add('hidden');
            });
            
            // Show selected mode options
            document.getElementById(`opt-mode-${mode}`).classList.remove('hidden');
        }

        async function saveMode() {
            const deviceId = document.getElementById('modal-device-id').value;
            const mode = parseInt(document.getElementById('select-mode').value);
            
            const requestData = { mode };
            
            // Add mode-specific parameters
            if (mode === 1) {
                requestData.batas_siram = parseInt(document.getElementById('input-batas-siram').value);
                requestData.batas_stop = parseInt(document.getElementById('input-batas-stop').value);
            } else if (mode === 3) {
                requestData.jam_pagi = document.getElementById('input-jam-pagi').value;
                requestData.jam_sore = document.getElementById('input-jam-sore').value;
                requestData.durasi_siram = parseInt(document.getElementById('input-durasi').value);
            }
            
            try {
                const response = await axios.post(`/api/devices/${deviceId}/mode`, requestData);
                
                if (response.data.success) {
                    // Show success message
                    alert('✅ Mode berhasil diubah! Arduino akan update dalam 1 menit.');
                    
                    // Close modal and reload devices
                    closeModeModal();
                    loadDevices();
                } else {
                    alert('❌ Gagal mengubah mode: ' + (response.data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving mode:', error);
                alert('❌ Error: ' + (error.response?.data?.message || 'Network error'));
            }
        }

        // ...existing code...
    </script>
</body>
</html>
