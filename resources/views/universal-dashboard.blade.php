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
        
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col fixed h-full z-10">
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
                        <h2 class="text-2xl font-bold text-slate-900">Overview Sistem</h2>
                        <p class="text-slate-500 text-sm mt-1">Monitoring sensor dan kontrol aktuator realtime.</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75" id="status-ping"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500" id="status-dot"></span>
                        </span>
                        <span class="text-sm font-bold text-green-600" id="online-status">Online</span>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-50 rounded-xl text-blue-600"><i class="fa-solid fa-temperature-half text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Sensor Suhu</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="sensor-temp">--°C</h3>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600"><i class="fa-solid fa-droplet text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Kelembaban</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="sensor-humidity">--%</h3>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-amber-50 rounded-xl text-amber-600"><i class="fa-solid fa-lightbulb text-xl"></i></div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="toggleSwitch" class="sr-only peer" onchange="toggleRelay()">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-amber-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            </label>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Status Relay</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="relay-status">OFF</h3>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600"><i class="fa-solid fa-clock text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Uptime</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="uptime">0<span class="text-lg text-slate-400">j</span> 0<span class="text-lg text-slate-400">m</span></h3>
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
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Konfigurasi Sistem</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Card Otomasi -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i class="fa-solid fa-robot"></i></div>
                            <h3 class="font-bold text-lg">Otomasi & Threshold</h3>
                        </div>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Batas Suhu Kipas ON (°C)</label>
                                <div class="flex gap-4">
                                    <input type="range" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer mt-2" min="20" max="40" value="30" oninput="document.getElementById('tempVal').innerText = this.value">
                                    <span class="font-bold text-slate-700 w-12" id="tempVal">30</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Batas Kelembaban Pompa ON (%)</label>
                                <div class="flex gap-4">
                                    <input type="number" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500" value="40">
                                    <span class="text-sm text-slate-400 mt-3">%</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <span class="text-sm font-medium text-slate-700">Mode Hemat Daya</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-green-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                </label>
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
                                <p class="text-xs text-slate-500 mb-1">Statistics</p>
                                <code class="text-xs font-mono text-slate-700">GET /api/monitoring/stats</code>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500 mb-1">History</p>
                                <code class="text-xs font-mono text-slate-700">GET /api/monitoring/history?limit=50</code>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg">
                                <p class="text-xs text-slate-500 mb-1">Toggle Relay</p>
                                <code class="text-xs font-mono text-slate-700">POST /api/monitoring/relay/toggle</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ================= MODAL: MODE SELECTION ================= -->
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
                    
                    // Update cards
                    document.getElementById('sensor-temp').textContent = 
                        data.temperature ? `${data.temperature.toFixed(1)}°C` : '--°C';
                    document.getElementById('sensor-humidity').textContent = 
                        data.humidity ? `${data.humidity.toFixed(0)}%` : '--%';
                    document.getElementById('relay-status').textContent = 
                        data.relay_status ? 'ON' : 'OFF';
                    document.getElementById('uptime').innerHTML = 
                        `${data.uptime_hours}<span class="text-lg text-slate-400">j</span> ${data.uptime_minutes}<span class="text-lg text-slate-400">m</span>`;
                    
                    // Update toggle switch
                    document.getElementById('toggleSwitch').checked = data.relay_status;
                    
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
                        // Determine mode badge
                        let modeColor = '';
                        let modeName = '';
                        
                        switch(device.mode) {
                            case 1:
                                modeColor = 'bg-green-100 text-green-700 border-green-300';
                                modeName = '🟢 Basic';
                                break;
                            case 2:
                                modeColor = 'bg-blue-100 text-blue-700 border-blue-300';
                                modeName = '🔵 Fuzzy AI';
                                break;
                            case 3:
                                modeColor = 'bg-red-100 text-red-700 border-red-300';
                                modeName = '🔴 Schedule';
                                break;
                            default:
                                modeColor = 'bg-gray-100 text-gray-700 border-gray-300';
                                modeName = 'Unknown';
                        }
                        
                        // Status badge
                        let statusBadge = '<span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-300">● ONLINE</span>';
                        if (device.status === 'idle') {
                            statusBadge = '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full border border-yellow-300">● IDLE</span>';
                        } else if (device.status === 'offline') {
                            statusBadge = '<span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full border border-red-300">● OFFLINE</span>';
                        }
                        
                        return `
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover transition-all">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                            <i class="fa-solid fa-microchip text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-800">${device.device_name || device.device_id}</h3>
                                            <p class="text-xs text-slate-500">${device.device_id}</p>
                                        </div>
                                    </div>
                                    ${statusBadge}
                                </div>
                                
                                <!-- Mode Badge -->
                                <div class="mb-4 pb-4 border-b border-slate-100">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-slate-500">Mode Operasi:</span>
                                        <span class="px-3 py-1 ${modeColor} text-xs font-bold rounded-lg border">${modeName}</span>
                                    </div>
                                </div>
                                
                                <!-- Settings -->
                                <div class="space-y-2 text-sm text-slate-600 mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">🌱 Tanaman:</span> 
                                        <span class="font-semibold capitalize">${device.plant_type}</span>
                                    </div>
                                    ${device.mode === 1 ? `
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">📊 Threshold:</span> 
                                            <span class="font-mono text-xs">${device.batas_siram}% - ${device.batas_stop}%</span>
                                        </div>
                                    ` : ''}
                                    ${device.mode === 3 ? `
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">⏰ Jadwal:</span> 
                                            <span class="font-mono text-xs">${device.jam_pagi?.substring(0,5)} & ${device.jam_sore?.substring(0,5)}</span>
                                        </div>
                                    ` : ''}
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">🔧 Firmware:</span> 
                                        <span class="font-mono text-xs">${device.firmware_version || 'N/A'}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-500">⏱️ Last Seen:</span> 
                                        <span class="text-xs">${device.last_seen ? new Date(device.last_seen).toLocaleString('id-ID', {hour: '2-digit', minute: '2-digit'}) : 'Never'}</span>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex gap-2 pt-4 border-t border-slate-100">
                                    <button onclick="openModeModal(${device.id}, ${device.mode}, ${JSON.stringify(device).replace(/"/g, '&quot;')})" class="flex-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-semibold text-sm transition-all">
                                        <i class="fa-solid fa-sliders mr-1"></i> Ubah Mode
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    container.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <div class="inline-block p-6 bg-slate-50 rounded-2xl">
                                <i class="fa-solid fa-microchip text-4xl text-slate-300 mb-3"></i>
                                <p class="text-slate-400 font-medium">Tidak ada perangkat terhubung</p>
                                <p class="text-sm text-slate-400 mt-1">Upload Arduino code untuk registrasi otomatis</p>
                            </div>
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
            alert('Mobile menu belum diimplementasi');
        }

        // --- MODAL FUNCTIONS ---
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
