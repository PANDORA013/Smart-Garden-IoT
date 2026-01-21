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
                        <p class="text-xs text-slate-400 mt-2" id="temp-status">Menunggu data...</p>
                    </div>
                    
                    <!-- Card 2: Kelembaban Tanah -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-green-50 rounded-xl text-green-600"><i class="fa-solid fa-seedling text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Kelembaban Tanah</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="sensor-soil">--%</h3>
                        <p class="text-xs text-slate-400 mt-2">Soil Moisture Level</p>
                    </div>
                    
                    <!-- Card 3: Status Tanah (Real-time berdasarkan ADC) -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 card-hover">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-purple-50 rounded-xl text-purple-600"><i class="fa-solid fa-chart-line text-xl"></i></div>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Kondisi Tanah</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1" id="soil-condition">--</h3>
                        <p class="text-xs mt-2" id="soil-adc-value">ADC: --</p>
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
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-slate-800">Grafik Real-time</h3>
                            <p class="text-xs text-slate-500 mt-1">Monitoring suhu & nilai ADC sensor tanah untuk deteksi kondisi basah/kering</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-lg font-semibold">
                                <i class="fa-solid fa-temperature-half"></i> Suhu
                            </span>
                            <span class="px-2 py-1 bg-green-50 text-green-700 rounded-lg font-semibold">
                                <i class="fa-solid fa-droplet"></i> RAW ADC
                            </span>
                        </div>
                    </div>
                    <div class="relative h-72 w-full"><canvas id="mainChart"></canvas></div>
                    
                    <!-- Panduan Nilai ADC - Tabel Detail -->
                    <div class="mt-4 p-4 bg-gradient-to-br from-slate-50 to-blue-50 rounded-xl border border-slate-200">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-info-circle text-blue-600 text-lg"></i>
                            <p class="text-sm font-bold text-slate-700">📊 Panduan Nilai ADC (12-bit: 0-4095) - Sensor Kelembaban Tanah</p>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
                                <thead class="bg-gradient-to-r from-slate-700 to-slate-600 text-white">
                                    <tr>
                                        <th class="px-3 py-3 text-center font-bold">Nilai ADC (Raw)</th>
                                        <th class="px-3 py-3 text-center font-bold">Persentase (%)</th>
                                        <th class="px-3 py-3 text-left font-bold">Status Kelembapan</th>
                                        <th class="px-3 py-3 text-left font-bold">Tindakan Pompa (Rekomendasi)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr class="hover:bg-red-50 transition-colors">
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-block px-2 py-1 bg-red-100 text-red-700 font-bold rounded-full text-[11px]">
                                                0 – 500
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="font-bold text-red-600">0% – 12%</span>
                                        </td>
                                        <td class="px-3 py-3 font-semibold text-red-700">
                                            <i class="fa-solid fa-triangle-exclamation text-red-500 mr-2"></i>
                                            Sangat Kering
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            <span class="font-bold text-red-600">🔴 HIDUPKAN</span> (Segera siram)
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-orange-50 transition-colors">
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-block px-2 py-1 bg-orange-100 text-orange-700 font-bold rounded-full text-[11px]">
                                                501 – 1199
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="font-bold text-orange-600">12% – 29%</span>
                                        </td>
                                        <td class="px-3 py-3 font-semibold text-orange-700">
                                            <i class="fa-solid fa-fire text-orange-500 mr-2"></i>
                                            Kering
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            <span class="font-bold text-orange-600">🟠 HIDUPKAN</span> (Mulai menyiram)
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-yellow-50 transition-colors">
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-700 font-bold rounded-full text-[11px]">
                                                1200 – 1800
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="font-bold text-yellow-600">29% – 44%</span>
                                        </td>
                                        <td class="px-3 py-3 font-semibold text-yellow-700">
                                            <i class="fa-solid fa-cloud text-yellow-500 mr-2"></i>
                                            Lembab (Awal)
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            <span class="font-bold text-green-600">⚫ MATIKAN</span> (Cukup air)
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-green-50 transition-colors">
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-block px-2 py-1 bg-green-100 text-green-700 font-bold rounded-full text-[11px]">
                                                1801 – 2500
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="font-bold text-green-600">44% – 61%</span>
                                        </td>
                                        <td class="px-3 py-3 font-semibold text-green-700">
                                            <i class="fa-solid fa-seedling text-green-500 mr-2"></i>
                                            Lembab (Ideal)
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            <span class="font-bold text-green-600">✅ MATIKAN</span> (Kondisi Terbaik)
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-cyan-50 transition-colors">
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-block px-2 py-1 bg-cyan-100 text-cyan-700 font-bold rounded-full text-[11px]">
                                                2501 – 3000
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="font-bold text-cyan-600">61% – 73%</span>
                                        </td>
                                        <td class="px-3 py-3 font-semibold text-cyan-700">
                                            <i class="fa-solid fa-droplet text-cyan-500 mr-2"></i>
                                            Basah
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            <span class="font-bold text-slate-600">⚫ MATIKAN</span> (Jangan disiram)
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-blue-50 transition-colors">
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 font-bold rounded-full text-[11px]">
                                                3001 – 4095
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="font-bold text-blue-600">73% – 100%</span>
                                        </td>
                                        <td class="px-3 py-3 font-semibold text-blue-700">
                                            <i class="fa-solid fa-water text-blue-500 mr-2"></i>
                                            Sangat Basah
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            <span class="font-bold text-red-600">⛔ MATIKAN</span> (Risiko busuk akar)
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3 p-3 bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg">
                            <p class="text-xs text-slate-700 flex items-start gap-2 mb-2">
                                <i class="fa-solid fa-lightbulb text-green-600 mt-0.5"></i>
                                <span><strong>Tips Mode Manual:</strong> Untuk tanaman cabai, atur <strong>Batas Kering (ON)</strong> di <strong>29%</strong> (ADC 1200) dan <strong>Batas Basah (OFF)</strong> di <strong>61%</strong> (ADC 2500) untuk hasil optimal.</span>
                            </p>
                            <p class="text-xs text-slate-600 flex items-start gap-2">
                                <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                                <span><strong>Rentang Ideal:</strong> 29% - 61% (ADC 1200-2500) = Zona hijau untuk pertumbuhan terbaik. Hindari < 12% (sangat kering) dan > 73% (risiko busuk akar).</span>
                            </p>
                        </div>
                    </div>
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
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">📋 Riwayat Aktivitas & Log</h2>
                        <p class="text-slate-500 text-sm mt-1">Pantau semua perubahan status relay, sensor, dan kondisi sistem</p>
                    </div>
                    <button class="text-sm text-blue-600 font-semibold hover:underline flex items-center gap-2" onclick="refreshLogs()">
                        <i class="fa-solid fa-refresh"></i> Refresh
                    </button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="p-4">Waktu</th>
                                <th class="p-4">Level</th>
                                <th class="p-4">Perangkat</th>
                                <th class="p-4">Aktivitas</th>
                                <th class="p-4">Detail Sensor</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100" id="logs-tbody">
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
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
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">⚙️ Pengaturan Sistem</h2>
                        <p class="text-slate-500 text-sm mt-1">Konfigurasi mode operasi dan strategi penyiraman</p>
                    </div>
                </div>

                <!-- Main Settings Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <h3 class="text-lg font-bold text-slate-800">Konfigurasi Perangkat</h3>
                        <div id="settings-device-status" class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-slate-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-slate-500"></span>
                            </span>
                            <span id="settings-status-text" class="text-xs font-medium text-slate-600">Checking...</span>
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div class="p-6 space-y-6">
                        <!-- Nama Perangkat -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Perangkat</label>
                            <input type="text" id="minimal-device-name" 
                                   class="w-full px-4 py-3 text-base font-medium border-2 border-slate-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors" 
                                   placeholder="Smart Garden #1">
                        </div>
                        
                        <!-- Mode Selector -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Mode Operasi</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button onclick="setMinimalMode(2)" id="minimal-mode-2" 
                                        class="group relative py-4 px-4 rounded-xl text-sm font-semibold transition-all border-2 border-slate-200 text-slate-600 hover:border-blue-400 bg-white">
                                    <div class="text-2xl mb-1">🤖</div>
                                    <div>Fuzzy AI</div>
                                    <div class="text-[10px] text-slate-400 font-normal mt-1">Threshold 35-45%</div>
                                </button>
                                <button onclick="setMinimalMode(4)" id="minimal-mode-4" 
                                        class="group relative py-4 px-4 rounded-xl text-sm font-semibold transition-all border-2 border-slate-200 text-slate-600 hover:border-slate-400 bg-white">
                                    <div class="text-2xl mb-1">🛠️</div>
                                    <div>Manual</div>
                                    <div class="text-[10px] text-slate-400 font-normal mt-1">Threshold + Jadwal</div>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Dynamic Settings Area -->
                        <div class="bg-slate-50 rounded-xl p-5 border border-slate-100">
                            <div id="minimal-settings-area" class="space-y-4">
                                <!-- Content akan diisi via JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Save Button -->
                        <button onclick="saveMinimalSettings()" id="minimal-save-btn" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-xl font-semibold transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save"></i>
                            Simpan Perubahan
                        </button>
                        
                        <!-- Notification -->
                        <div id="minimal-notif" class="hidden text-center text-sm font-medium py-3 rounded-xl"></div>
                    </div>
                </div>

                <!-- Info Cards Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Card: Mode Info -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-50">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                <i class="fa-solid fa-info-circle text-lg"></i>
                            </div>
                            <h3 class="font-bold text-base text-slate-800">Status Mode Aktif</h3>
                        </div>
                        <div id="minimal-mode-info" class="space-y-3">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-slate-500">Mode Saat Ini:</span>
                                <span class="font-bold text-slate-800" id="current-mode-display">Basic</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-slate-500">Device ID:</span>
                                <span class="font-mono text-sm text-slate-700" id="current-device-id">PICO_01</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-slate-500">Last Update:</span>
                                <span class="text-sm text-slate-600" id="settings-last-update">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Quick Actions -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-50">
                            <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                                <i class="fa-solid fa-bolt text-lg"></i>
                            </div>
                            <h3 class="font-bold text-base text-slate-800">Quick Actions</h3>
                        </div>
                        <div class="space-y-2">
                            <button onclick="testPump()" class="w-full text-left px-4 py-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors flex items-center gap-3">
                                <i class="fa-solid fa-flask text-blue-600"></i>
                                <div>
                                    <div class="text-sm font-medium text-slate-800">Test Pompa</div>
                                    <div class="text-xs text-slate-500">Nyalakan pompa 5 detik</div>
                                </div>
                            </button>
                            <button onclick="refreshSettings()" class="w-full text-left px-4 py-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors flex items-center gap-3">
                                <i class="fa-solid fa-refresh text-green-600"></i>
                                <div>
                                    <div class="text-sm font-medium text-slate-800">Refresh Config</div>
                                    <div class="text-xs text-slate-500">Muat ulang pengaturan</div>
                                </div>
                            </button>
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
                    
                    <!-- Device Status Indicator -->
                    <div id="config-device-status" class="mt-3 hidden">
                        <span id="config-status-badge" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold">
                            <i class="fa-solid fa-circle text-[8px]"></i>
                            <span id="config-status-text">Checking...</span>
                        </span>
                        <span id="config-status-message" class="ml-2 text-xs text-slate-600"></span>
                    </div>
                </div>

                <!-- Mode Selection Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Mode 2: AI Fuzzy -->
                    <div id="card-mode-2" class="mode-card bg-white rounded-xl shadow-sm border-2 border-transparent hover:border-blue-500 cursor-pointer transition-all p-6" onclick="selectSmartMode(2)">
                        <div class="text-center">
                            <div class="text-6xl mb-3">🤖</div>
                            <h5 class="text-lg font-bold text-slate-800 mb-2">Mode AI (Fuzzy)</h5>
                            <p class="text-sm text-slate-600 mb-3">Pompa menyala otomatis saat kelembapan < 35-45% (kering), dan berhenti saat kelembapan kembali normal.</p>
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">⭐ Paling Efisien</span>
                        </div>
                    </div>

                    <!-- Mode 4: Manual -->
                    <div id="card-mode-4" class="mode-card bg-white rounded-xl shadow-sm border-2 border-transparent hover:border-slate-500 cursor-pointer transition-all p-6" onclick="selectSmartMode(4)">
                        <div class="text-center">
                            <div class="text-6xl mb-3">�️</div>
                            <h5 class="text-lg font-bold text-slate-800 mb-2">Mode Manual</h5>
                            <p class="text-sm text-slate-600 mb-3">Kendali penuh dengan Threshold + Jadwal. Atur kapan dan bagaimana pompa bekerja.</p>
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

                    <!-- Input for Mode 4: Manual (Weekly Loop System) -->
                    <div id="input-manual" class="hidden config-group space-y-6">
                        <!-- Info Weekly Loop -->
                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-calendar-week text-purple-600 text-xl mt-0.5"></i>
                                <div>
                                    <p class="text-sm font-bold text-purple-800 mb-1">📅 Sistem Penjadwalan Mingguan (Weekly Loop)</p>
                                    <p class="text-xs text-purple-700">
                                        Pilih hari aktif dan atur threshold + jam penyiraman per hari. Sistem akan berjalan otomatis sesuai siklus mingguan yang tersimpan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Day Selector -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-3">
                                <i class="fa-solid fa-calendar-days mr-2"></i>
                                Pilih Hari Aktif untuk Dikonfigurasi:
                            </label>
                            <div class="grid grid-cols-7 gap-2">
                                <button type="button" onclick="selectDay('senin')" id="btn-day-senin" 
                                        class="day-selector px-3 py-4 rounded-xl border-2 border-slate-200 hover:border-blue-400 transition-all text-center">
                                    <div class="text-xs font-bold text-slate-600">Sen</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Senin</div>
                                </button>
                                <button type="button" onclick="selectDay('selasa')" id="btn-day-selasa" 
                                        class="day-selector px-3 py-4 rounded-xl border-2 border-slate-200 hover:border-blue-400 transition-all text-center">
                                    <div class="text-xs font-bold text-slate-600">Sel</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Selasa</div>
                                </button>
                                <button type="button" onclick="selectDay('rabu')" id="btn-day-rabu" 
                                        class="day-selector px-3 py-4 rounded-xl border-2 border-slate-200 hover:border-blue-400 transition-all text-center">
                                    <div class="text-xs font-bold text-slate-600">Rab</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Rabu</div>
                                </button>
                                <button type="button" onclick="selectDay('kamis')" id="btn-day-kamis" 
                                        class="day-selector px-3 py-4 rounded-xl border-2 border-slate-200 hover:border-blue-400 transition-all text-center">
                                    <div class="text-xs font-bold text-slate-600">Kam</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Kamis</div>
                                </button>
                                <button type="button" onclick="selectDay('jumat')" id="btn-day-jumat" 
                                        class="day-selector px-3 py-4 rounded-xl border-2 border-slate-200 hover:border-blue-400 transition-all text-center">
                                    <div class="text-xs font-bold text-slate-600">Jum</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Jumat</div>
                                </button>
                                <button type="button" onclick="selectDay('sabtu')" id="btn-day-sabtu" 
                                        class="day-selector px-3 py-4 rounded-xl border-2 border-slate-200 hover:border-blue-400 transition-all text-center">
                                    <div class="text-xs font-bold text-slate-600">Sab</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Sabtu</div>
                                </button>
                                <button type="button" onclick="selectDay('minggu')" id="btn-day-minggu" 
                                        class="day-selector px-3 py-4 rounded-xl border-2 border-slate-200 hover:border-blue-400 transition-all text-center">
                                    <div class="text-xs font-bold text-slate-600">Min</div>
                                    <div class="text-[10px] text-slate-400 mt-1">Minggu</div>
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Klik hari untuk mengatur konfigurasi. Hari yang sudah dikonfigurasi akan ditandai dengan warna biru.
                            </p>
                        </div>

                        <!-- Day Configuration Area -->
                        <div id="day-config-area" class="hidden space-y-6 p-5 bg-blue-50 border-2 border-blue-200 rounded-xl">
                            <div class="flex justify-between items-center pb-3 border-b border-blue-300">
                                <h6 class="text-base font-bold text-blue-800 flex items-center gap-2">
                                    <i class="fa-solid fa-gear"></i>
                                    Konfigurasi <span id="current-day-name" class="text-blue-600">Hari</span>
                                </h6>
                                <button type="button" onclick="toggleDayActive()" id="toggle-day-active" 
                                        class="px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition-all">
                                    <i class="fa-solid fa-check mr-1"></i> Aktifkan Hari Ini
                                </button>
                            </div>

                            <!-- Threshold Settings per Day -->
                            <div class="space-y-4">
                                <h6 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                                    <i class="fa-solid fa-droplet"></i> Threshold Kelembapan
                                </h6>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-3">
                                        � Batas Kering (Pompa ON):
                                    </label>
                                    <div class="flex items-center gap-4">
                                        <input type="range" id="range-day-on" class="flex-grow w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer" min="0" max="100" value="29" oninput="updateDayThresholdOnDisplay()">
                                        <div class="text-right min-w-[100px]">
                                            <div id="val-day-on" class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-lg text-center">29%</div>
                                            <div id="adc-day-on" class="text-[10px] text-slate-500 mt-1 text-center font-medium">ADC: ~1200</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-3">
                                        💧 Batas Basah (Pompa OFF):
                                    </label>
                                    <div class="flex items-center gap-4">
                                        <input type="range" id="range-day-off" class="flex-grow w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer" min="0" max="100" value="61" oninput="updateDayThresholdOffDisplay()">
                                        <div class="text-right min-w-[100px]">
                                            <div id="val-day-off" class="px-4 py-2 bg-green-600 text-white rounded-lg font-bold text-lg text-center">61%</div>
                                            <div id="adc-day-off" class="text-[10px] text-slate-500 mt-1 text-center font-medium">ADC: ~2500</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Watering Time per Day -->
                            <div class="space-y-4">
                                <h6 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                                    <i class="fa-solid fa-clock"></i> Jam Penyiraman
                                </h6>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-2">⏰ Jam Pagi:</label>
                                        <input type="time" id="time-day-pagi" class="w-full px-3 py-2 text-sm rounded-lg border-2 border-slate-200 focus:border-blue-500 focus:outline-none font-medium" value="07:00">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-2">🌅 Jam Sore:</label>
                                        <input type="time" id="time-day-sore" class="w-full px-3 py-2 text-sm rounded-lg border-2 border-slate-200 focus:border-blue-500 focus:outline-none font-medium" value="17:00">
                                    </div>
                                </div>
                            </div>

                            <!-- Save Day Config Button -->
                            <button type="button" onclick="saveDayConfig()" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-xl font-semibold transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-save"></i>
                                Simpan Konfigurasi Hari Ini
                            </button>
                        </div>

                        <!-- Summary of Configured Days -->
                        <div id="weekly-summary" class="hidden p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <h6 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-list-check"></i>
                                Ringkasan Konfigurasi Mingguan
                            </h6>
                            <div id="summary-content" class="space-y-2 text-xs">
                                <!-- Will be populated dynamically -->
                            </div>
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
            } else if (pageId === 'settings') {
                updateSettingsDeviceStatus();
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
                datasets: [
                    {
                        label: 'Suhu (°C)',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'RAW ADC Tanah',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 12, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                if (context.datasetIndex === 1) {
                                    const rawAdc = context.parsed.y;
                                    if (rawAdc > 3500) return '🌵 Sangat Kering';
                                    if (rawAdc > 3000) return '⚠️ Kering';
                                    if (rawAdc > 2000) return '💧 Lembab';
                                    if (rawAdc > 1000) return '💦 Basah';
                                    return '🌊 Sangat Basah';
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        type: 'linear',
                        position: 'left',
                        beginAtZero: false,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        title: {
                            display: true,
                            text: 'Suhu (°C)',
                            color: '#3b82f6',
                            font: { size: 12, weight: 'bold' }
                        },
                        ticks: { color: '#3b82f6' }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: { display: false },
                        title: {
                            display: true,
                            text: 'RAW ADC (0-4095)',
                            color: '#10b981',
                            font: { size: 12, weight: 'bold' }
                        },
                        ticks: { 
                            color: '#10b981',
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        },
                        max: 4095
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // --- API FUNCTIONS ---
        async function fetchStats() {
            try {
                const response = await axios.get(`${API_BASE_URL}/stats`, {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
                if (response.data.success) {
                    const data = response.data.data;
                    const isOnline = data.is_online;
                    
                    // Jika offline, tampilkan data sebagai tidak tersedia
                    if (!isOnline) {
                        document.getElementById('sensor-temp').textContent = '--°C';
                        document.getElementById('sensor-soil').textContent = '--%';
                        document.getElementById('relay-status').textContent = 'OFF';
                        document.getElementById('toggleSwitch').checked = false;
                        document.getElementById('toggleSwitch').disabled = true;
                        
                        // Tampilkan peringatan offline di dashboard
                        const deviceListContainer = document.getElementById('detected-devices-list');
                        deviceListContainer.innerHTML = '<span class="text-red-500 text-xs font-bold">⚠️ Device Offline - Tidak ada data sensor</span>';
                    } else {
                        // Online: tampilkan data normal
                        const temp = data.temperature;
                        const soil = data.soil_moisture;
                        const rawAdc = data.raw_adc || 0;
                        
                        // Update Suhu
                        document.getElementById('sensor-temp').textContent = 
                            temp !== null && temp !== undefined 
                                ? `${temp.toFixed(1)}°C` 
                                : '--°C';
                        
                        // Update status suhu
                        const tempStatusEl = document.getElementById('temp-status');
                        if (temp !== null && temp !== undefined) {
                            if (temp < 20) {
                                tempStatusEl.textContent = '❄️ Dingin';
                                tempStatusEl.className = 'text-xs text-blue-600 mt-2 font-semibold';
                            } else if (temp >= 20 && temp <= 32) {
                                tempStatusEl.textContent = '✅ Normal';
                                tempStatusEl.className = 'text-xs text-green-600 mt-2 font-semibold';
                            } else {
                                tempStatusEl.textContent = '🔥 Panas';
                                tempStatusEl.className = 'text-xs text-red-600 mt-2 font-semibold';
                            }
                        } else {
                            tempStatusEl.textContent = 'Menunggu data...';
                            tempStatusEl.className = 'text-xs text-slate-400 mt-2';
                        }
                        
                        // Update Kelembaban Tanah
                        document.getElementById('sensor-soil').textContent = 
                            soil !== null && soil !== undefined 
                                ? `${soil.toFixed(0)}%` 
                                : '--%';
                        
                        // Update Kondisi Tanah berdasarkan RAW ADC (Real-time dari Pico W)
                        const soilConditionEl = document.getElementById('soil-condition');
                        const soilAdcEl = document.getElementById('soil-adc-value');
                        
                        if (rawAdc > 0) {
                            soilAdcEl.textContent = `ADC: ${rawAdc}`;
                            
                            if (rawAdc >= 0 && rawAdc <= 500) {
                                // Kering (Di udara)
                                soilConditionEl.textContent = '�️ Kering (Udara)';
                                soilConditionEl.className = 'text-2xl font-bold text-slate-600 mt-1';
                                soilAdcEl.className = 'text-xs text-slate-500 mt-2 font-semibold';
                            } else if (rawAdc >= 1200 && rawAdc <= 2500) {
                                // Lembab (Ideal)
                                soilConditionEl.textContent = '✅ Lembab (Ideal)';
                                soilConditionEl.className = 'text-2xl font-bold text-green-600 mt-1';
                                soilAdcEl.className = 'text-xs text-green-500 mt-2 font-semibold';
                            } else if (rawAdc > 3000) {
                                // Basah (Air)
                                soilConditionEl.textContent = '💧 Basah (Air)';
                                soilConditionEl.className = 'text-2xl font-bold text-blue-600 mt-1';
                                soilAdcEl.className = 'text-xs text-blue-500 mt-2 font-semibold';
                            } else if (rawAdc > 500 && rawAdc < 1200) {
                                // Transisi: Agak Kering
                                soilConditionEl.textContent = '⚠️ Agak Kering';
                                soilConditionEl.className = 'text-2xl font-bold text-orange-600 mt-1';
                                soilAdcEl.className = 'text-xs text-orange-500 mt-2 font-semibold';
                            } else {
                                // Transisi: Cukup Basah (2500-3000)
                                soilConditionEl.textContent = '💦 Cukup Basah';
                                soilConditionEl.className = 'text-2xl font-bold text-cyan-600 mt-1';
                                soilAdcEl.className = 'text-xs text-cyan-500 mt-2 font-semibold';
                            }
                        } else {
                            soilConditionEl.textContent = '--';
                            soilConditionEl.className = 'text-2xl font-bold text-slate-800 mt-1';
                            soilAdcEl.textContent = 'ADC: --';
                            soilAdcEl.className = 'text-xs text-slate-400 mt-2';
                        }
                        
                        // Update Status Relay
                        document.getElementById('relay-status').textContent = 
                            data.relay_status ? 'ON' : 'OFF';
                        
                        // Update toggle switch dan enable control
                        document.getElementById('toggleSwitch').checked = data.relay_status;
                        document.getElementById('toggleSwitch').disabled = false;
                        
                        // Update detected devices list dengan data hardware_status dari Pico
                        const deviceListContainer = document.getElementById('detected-devices-list');
                        const hwStatus = data.hardware_status || {};
                        
                        const hardwareList = [
                            { name: 'DHT Sensor', icon: 'fa-temperature-high', status: hwStatus.dht11 || hwStatus.dht22 || false },
                            { name: 'Soil Sensor', icon: 'fa-droplet', status: hwStatus.soil_sensor || false },
                            { name: 'Relay', icon: 'fa-toggle-on', status: hwStatus.relay !== false },
                            { name: 'LCD', icon: 'fa-display', status: hwStatus.lcd || false }
                        ];
                        
                        let html = '';
                        hardwareList.forEach(hw => {
                            const statusColor = hw.status ? 'text-green-600' : 'text-red-500';
                            const statusIcon = hw.status ? 'fa-check-circle' : 'fa-times-circle';
                            html += `<span class="flex items-center gap-1 px-2 py-1 bg-white ${statusColor} text-xs font-bold rounded-lg shadow-sm">
                                <i class="fa-solid ${hw.icon}"></i> ${hw.name}
                                <i class="fa-solid ${statusIcon} text-[10px]"></i>
                            </span>`;
                        });
                        deviceListContainer.innerHTML = html;
                    }
                    
                    // Update device info card (Dashboard) - selalu tampilkan
                    document.getElementById('device-name-display').textContent = 
                        data.device_name || 'Smart Garden Device';
                    document.getElementById('plant-type-display').textContent = 
                        data.plant_type || '-';
                    
                    // Mode mapping
                    const modeNames = {
                        2: '🤖 Mode AI Fuzzy',
                        4: '🛠️ Mode Manual'
                    };
                    document.getElementById('mode-display').textContent = 
                        modeNames[data.mode] || '-';
                    
                    document.getElementById('device-ip-display').textContent = 
                        data.ip_address || '-';
                    document.getElementById('last-update-display').textContent = 
                        new Date().toLocaleTimeString('id-ID');
                    
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
                    
                    // Update status Admin di sidebar
                    updateConnectionStatus(isOnline);
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
                    mainChart.data.datasets[1].data = data.map(item => item.raw_adc || 0);
                    mainChart.update();
                }
            } catch (error) {
                console.error('Error fetching history:', error);
            }
        }

        async function loadLogs() {
            try {
                const response = await axios.get(`${API_BASE_URL}/logs?limit=20`, {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
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
                        
                        // Format details dengan badge kecil
                        const detailsHtml = log.details ? 
                            `<div class="text-xs text-slate-500 mt-1">${log.details}</div>` : '';
                        
                        return `
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-mono text-slate-500 text-xs">
                                    <div class="font-bold">${log.time}</div>
                                    <div class="text-[10px] text-slate-400">${log.date}</div>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 ${levelClass} rounded-full text-xs font-bold whitespace-nowrap">
                                        ${log.level}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-700 font-medium">${log.device}</td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-800">${log.message}</div>
                                    ${detailsHtml}
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1 text-xs">
                                        ${log.soil_moisture ? `<span class="text-blue-600">💧 ${log.soil_moisture}%</span>` : ''}
                                        ${log.temperature ? `<span class="text-orange-600">🌡️ ${log.temperature}°C</span>` : ''}
                                        ${log.relay_status !== undefined ? 
                                            `<span class="${log.relay_status ? 'text-green-600' : 'text-slate-400'}">
                                                ${log.relay_status ? '🟢 Relay ON' : '⚫ Relay OFF'}
                                            </span>` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada log</td></tr>';
                }
            } catch (error) {
                console.error('Error loading logs:', error);
                document.getElementById('logs-tbody').innerHTML = 
                    '<tr><td colspan="5" class="p-8 text-center text-red-400"><i class="fa-solid fa-exclamation-triangle mr-2"></i>Error loading logs</td></tr>';
            }
        }

        async function loadDevices() {
            const container = document.getElementById('devices-container');
            
            try {
                const response = await axios.get('/api/devices', {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                }); 
                const devices = response.data.data;
                
                if (devices && devices.length > 0) {
                    container.innerHTML = devices.map(device => {
                        
                        const statusMap = {
                            'online': { badge: 'bg-green-100 text-green-700 border-green-300', text: 'ONLINE', icon: 'fa-circle-check' },
                            'idle': { badge: 'bg-yellow-100 text-yellow-700 border-yellow-300', text: 'IDLE', icon: 'fa-circle-pause' },
                            'offline': { badge: 'bg-red-100 text-red-700 border-red-300', text: 'OFFLINE', icon: 'fa-circle-xmark' },
                            'never_connected': { badge: 'bg-slate-100 text-slate-700 border-slate-300', text: 'NEVER', icon: 'fa-circle-question' }
                        };
                        
                        const statusInfo = statusMap[device.status] || statusMap['offline'];
                        const statusBadge = `<span class="px-3 py-1 ${statusInfo.badge} text-xs font-bold rounded-full border shadow-sm">
                            <i class="fa-solid ${statusInfo.icon} text-[8px] mr-1"></i> ${statusInfo.text}
                        </span>`;

                        let lastSeenText = 'Never';
                        if (device.last_seen) {
                            const lastSeen = new Date(device.last_seen);
                            const now = new Date();
                            const diffSeconds = Math.floor((now - lastSeen) / 1000);
                            
                            if (diffSeconds < 60) {
                                lastSeenText = `${diffSeconds}s ago`;
                            } else if (diffSeconds < 3600) {
                                lastSeenText = `${Math.floor(diffSeconds / 60)}m ago`;
                            } else if (diffSeconds < 86400) {
                                lastSeenText = `${Math.floor(diffSeconds / 3600)}h ago`;
                            } else {
                                lastSeenText = lastSeen.toLocaleDateString('id-ID');
                            }
                        }

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
                                
                                <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
                                    <span><i class="fa-regular fa-clock mr-1"></i> Last Seen: ${lastSeenText}</span>
                                    <span><i class="fa-solid fa-network-wired mr-1"></i> IP: ${device.ip_address || '-'}</span>
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
            const toggleSwitch = document.getElementById('toggleSwitch');
            const isChecked = toggleSwitch.checked;
            
            // Cek apakah device offline
            if (toggleSwitch.disabled) {
                alert('⚠️ Tidak dapat mengontrol pompa! Device sedang OFFLINE.\n\nSilakan cek koneksi Pico W dan tunggu hingga status Online.');
                toggleSwitch.checked = false;
                return;
            }
            
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
                alert('❌ Gagal mengontrol pompa. Pastikan device terhubung.');
                // Revert switch on error
                toggleSwitch.checked = !isChecked;
            }
        }

        function updateConnectionStatus(isOnline) {
            const statusText = document.getElementById('online-status');
            const statusDot = document.getElementById('status-dot');
            const statusPing = document.getElementById('status-ping');
            const connectionStatus = document.getElementById('connection-status');
            
            if (isOnline) {
                // Update status di dashboard header
                statusText.textContent = 'Online';
                statusText.classList.remove('text-red-600');
                statusText.classList.add('text-green-600');
                statusDot.classList.remove('bg-red-500');
                statusDot.classList.add('bg-green-500');
                statusPing.classList.remove('bg-red-400');
                statusPing.classList.add('bg-green-400');
                
                // Update status Admin di sidebar
                connectionStatus.textContent = 'Device Online';
                connectionStatus.classList.remove('text-red-500');
                connectionStatus.classList.add('text-green-500');
            } else {
                // Update status di dashboard header
                statusText.textContent = 'Offline';
                statusText.classList.remove('text-green-600');
                statusText.classList.add('text-red-600');
                statusDot.classList.remove('bg-green-500');
                statusDot.classList.add('bg-red-500');
                statusPing.classList.remove('bg-green-400');
                statusPing.classList.add('bg-red-400');
                
                // Update status Admin di sidebar
                connectionStatus.textContent = 'Device Offline';
                connectionStatus.classList.remove('text-green-500', 'text-slate-500');
                connectionStatus.classList.add('text-red-500');
            }
        }

        function refreshLogs() {
            loadLogs();
        }

        async function updateSettingsDeviceStatus() {
            try {
                const response = await axios.get(`${API_BASE_URL}/stats`, {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
                
                if (response.data.success) {
                    const data = response.data.data;
                    const isOnline = data.is_online;
                    
                    const statusContainer = document.getElementById('settings-device-status');
                    const statusText = document.getElementById('settings-status-text');
                    
                    // Update ping animation dan status
                    const pingSpan = statusContainer.querySelector('.animate-ping');
                    const dotSpan = statusContainer.querySelector('.relative.inline-flex');
                    
                    if (isOnline) {
                        // Online: hijau
                        pingSpan.classList.remove('bg-slate-400', 'bg-red-400', 'bg-yellow-400');
                        pingSpan.classList.add('bg-green-400');
                        dotSpan.classList.remove('bg-slate-500', 'bg-red-500', 'bg-yellow-500');
                        dotSpan.classList.add('bg-green-500');
                        statusText.textContent = 'Online';
                        statusText.classList.remove('text-slate-600', 'text-red-600', 'text-yellow-600');
                        statusText.classList.add('text-green-600');
                    } else {
                        // Offline: merah
                        pingSpan.classList.remove('bg-slate-400', 'bg-green-400', 'bg-yellow-400');
                        pingSpan.classList.add('bg-red-400');
                        dotSpan.classList.remove('bg-slate-500', 'bg-green-500', 'bg-yellow-500');
                        dotSpan.classList.add('bg-red-500');
                        statusText.textContent = 'Offline';
                        statusText.classList.remove('text-slate-600', 'text-green-600', 'text-yellow-600');
                        statusText.classList.add('text-red-600');
                    }
                }
            } catch (error) {
                console.error('Error updating settings device status:', error);
            }
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
            
            // Default select Mode 2 (AI Fuzzy)
            selectSmartMode(2);
        }

        function closeSmartConfigModal() {
            document.getElementById('smartConfigModal').classList.add('hidden');
            document.getElementById('smartConfigModal').classList.remove('flex');
        }

        async function loadDevicesForConfig() {
            try {
                const response = await axios.get('/api/devices', {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
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
                    
                    // Update status device pertama
                    updateDeviceStatusInModal(firstDevice);
                    
                    // Add event listener untuk update ADC dan status saat device berubah
                    select.addEventListener('change', async (e) => {
                        const selectedDevice = devices.find(d => d.id == e.target.value);
                        if (selectedDevice) {
                            document.getElementById('input-adc-min').value = selectedDevice.sensor_min || 4095;
                            document.getElementById('input-adc-max').value = selectedDevice.sensor_max || 1500;
                            updateDeviceStatusInModal(selectedDevice);
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

        function updateDeviceStatusInModal(device) {
            const statusContainer = document.getElementById('config-device-status');
            const statusBadge = document.getElementById('config-status-badge');
            const statusText = document.getElementById('config-status-text');
            const statusMessage = document.getElementById('config-status-message');
            
            statusContainer.classList.remove('hidden');
            
            const statusMap = {
                'online': {
                    badge: 'bg-green-100 text-green-700',
                    text: 'ONLINE',
                    icon: 'fa-check-circle',
                    message: 'Device terhubung dan mengirim data'
                },
                'idle': {
                    badge: 'bg-yellow-100 text-yellow-700',
                    text: 'IDLE',
                    icon: 'fa-clock',
                    message: 'Device tidak mengirim data dalam 2 menit terakhir'
                },
                'offline': {
                    badge: 'bg-red-100 text-red-700',
                    text: 'OFFLINE',
                    icon: 'fa-times-circle',
                    message: 'Device tidak terhubung, cek koneksi WiFi dan power'
                },
                'never_connected': {
                    badge: 'bg-slate-100 text-slate-700',
                    text: 'NEVER CONNECTED',
                    icon: 'fa-question-circle',
                    message: 'Device belum pernah mengirim data'
                }
            };
            
            const status = statusMap[device.status] || statusMap['offline'];
            
            statusBadge.className = `inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold ${status.badge}`;
            statusText.textContent = status.text;
            statusMessage.textContent = status.message;
            
            // Update icon
            const icon = statusBadge.querySelector('i');
            icon.className = `fa-solid ${status.icon} text-[8px]`;
        }

        // Helper functions for ADC conversion and status display
        function percentageToADC(percentage) {
            // Konversi persentase (0-100%) ke ADC (4095-0)
            // 0% = 4095 ADC (kering), 100% = 0 ADC (basah)
            return Math.round(4095 - (percentage * 40.95));
        }

        function getStatusByPercentage(percentage) {
            if (percentage >= 0 && percentage <= 12) {
                return {
                    text: '🔴 Sangat Kering - HIDUPKAN Pompa',
                    class: 'text-red-600',
                    recommendation: '💡 Segera siram! Tanaman kekurangan air.'
                };
            } else if (percentage >= 12 && percentage <= 29) {
                return {
                    text: '🟠 Kering - HIDUPKAN Pompa',
                    class: 'text-orange-600',
                    recommendation: '💡 Mulai menyiram untuk mencegah stress tanaman.'
                };
            } else if (percentage >= 29 && percentage <= 44) {
                return {
                    text: '🟡 Lembab Awal - MATIKAN Pompa',
                    class: 'text-yellow-600',
                    recommendation: '✅ Cukup air, tidak perlu menyiram.'
                };
            } else if (percentage >= 44 && percentage <= 61) {
                return {
                    text: '✅ Lembab Ideal - MATIKAN Pompa',
                    class: 'text-green-600',
                    recommendation: '✅ Kondisi Terbaik! Pertahankan kelembapan ini.'
                };
            } else if (percentage >= 61 && percentage <= 73) {
                return {
                    text: '💧 Basah - MATIKAN Pompa',
                    class: 'text-cyan-600',
                    recommendation: '⚠️ Jangan disiram, sudah terlalu basah.'
                };
            } else {
                return {
                    text: '⛔ Sangat Basah - MATIKAN Pompa',
                    class: 'text-blue-700',
                    recommendation: '⛔ Risiko busuk akar! Stop penyiraman.'
                };
            }
        }

        // ========== WEEKLY LOOP SYSTEM - MODE MANUAL ==========
        
        // Global weekly configuration object for 7 days
        let weeklyConfig = {
            senin: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            selasa: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            rabu: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            kamis: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            jumat: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            sabtu: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            minggu: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' }
        };
        let currentSelectedDay = null;
        
        const dayNames = {
            senin: 'Senin',
            selasa: 'Selasa',
            rabu: 'Rabu',
            kamis: 'Kamis',
            jumat: 'Jumat',
            sabtu: 'Sabtu',
            minggu: 'Minggu'
        };
        
        // Select day for configuration
        function selectDay(day) {
            currentSelectedDay = day;
            
            // Update button styles - highlight selected
            document.querySelectorAll('.day-selector').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'border-blue-600', 'ring-4', 'ring-blue-200');
                btn.classList.add('border-slate-200', 'text-slate-600');
            });
            
            const selectedBtn = document.getElementById(`btn-day-${day}`);
            selectedBtn.classList.add('bg-blue-500', 'text-white', 'border-blue-600', 'ring-4', 'ring-blue-200');
            selectedBtn.classList.remove('border-slate-200', 'text-slate-600');
            
            // Show config area
            document.getElementById('day-config-area').classList.remove('hidden');
            
            // Update day name display
            document.getElementById('current-day-name').textContent = dayNames[day];
            
            // Load existing config for this day
            const config = weeklyConfig[day];
            document.getElementById('range-day-on').value = config.threshold_on;
            document.getElementById('range-day-off').value = config.threshold_off;
            document.getElementById('time-day-pagi').value = config.jam_pagi;
            document.getElementById('time-day-sore').value = config.jam_sore;
            
            // Update displays
            updateDayThresholdOnDisplay();
            updateDayThresholdOffDisplay();
            
            // Update active button state
            const toggleBtn = document.getElementById('toggle-day-active');
            if (config.active) {
                toggleBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Hari Aktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition-all';
            } else {
                toggleBtn.innerHTML = '<i class="fa-solid fa-times mr-1"></i> Hari Nonaktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-slate-400 text-white text-xs font-bold hover:bg-slate-500 transition-all';
            }
            
            updateDayMarkers();
        }
        
        // Toggle day active/inactive
        function toggleDayActive() {
            if (!currentSelectedDay) return;
            
            weeklyConfig[currentSelectedDay].active = !weeklyConfig[currentSelectedDay].active;
            
            // Update button appearance
            const toggleBtn = document.getElementById('toggle-day-active');
            if (weeklyConfig[currentSelectedDay].active) {
                toggleBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Hari Aktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition-all';
            } else {
                toggleBtn.innerHTML = '<i class="fa-solid fa-times mr-1"></i> Hari Nonaktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-slate-400 text-white text-xs font-bold hover:bg-slate-500 transition-all';
            }
            
            updateDayMarkers();
        }
        
        // Update threshold ON slider display
        function updateDayThresholdOnDisplay() {
            const percentage = parseInt(document.getElementById('range-day-on').value);
            const adc = percentageToADC(percentage);
            
            document.getElementById('val-day-on').textContent = percentage + '%';
            document.getElementById('adc-day-on').textContent = `ADC: ~${adc}`;
            
            // Color coding based on percentage
            const valBox = document.getElementById('val-day-on');
            if (percentage <= 29) {
                valBox.className = 'px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 44) {
                valBox.className = 'px-4 py-2 bg-yellow-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 61) {
                valBox.className = 'px-4 py-2 bg-green-600 text-white rounded-lg font-bold text-lg text-center';
            } else {
                valBox.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-lg text-center';
            }
        }
        
        // Update threshold OFF slider display
        function updateDayThresholdOffDisplay() {
            const percentage = parseInt(document.getElementById('range-day-off').value);
            const adc = percentageToADC(percentage);
            
            document.getElementById('val-day-off').textContent = percentage + '%';
            document.getElementById('adc-day-off').textContent = `ADC: ~${adc}`;
            
            // Color coding based on percentage
            const valBox = document.getElementById('val-day-off');
            if (percentage <= 29) {
                valBox.className = 'px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 44) {
                valBox.className = 'px-4 py-2 bg-yellow-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 61) {
                valBox.className = 'px-4 py-2 bg-green-600 text-white rounded-lg font-bold text-lg text-center';
            } else {
                valBox.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-lg text-center';
            }
        }
        
        // Save configuration for current day
        function saveDayConfig() {
            if (!currentSelectedDay) return;
            
            // Get values from inputs
            weeklyConfig[currentSelectedDay].threshold_on = parseInt(document.getElementById('range-day-on').value);
            weeklyConfig[currentSelectedDay].threshold_off = parseInt(document.getElementById('range-day-off').value);
            weeklyConfig[currentSelectedDay].jam_pagi = document.getElementById('time-day-pagi').value;
            weeklyConfig[currentSelectedDay].jam_sore = document.getElementById('time-day-sore').value;
            
            // Validation
            if (weeklyConfig[currentSelectedDay].threshold_off <= weeklyConfig[currentSelectedDay].threshold_on) {
                alert('⚠️ Batas Basah (OFF) harus lebih tinggi dari Batas Kering (ON)!');
                return;
            }
            
            // Update markers and summary
            updateDayMarkers();
            updateWeeklySummary();
            
            alert(`✅ Konfigurasi ${dayNames[currentSelectedDay]} berhasil disimpan!`);
        }
        
        // Update visual markers (checkmarks) on day buttons
        function updateDayMarkers() {
            const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
            days.forEach(day => {
                const btn = document.getElementById(`btn-day-${day}`);
                const config = weeklyConfig[day];
                
                if (config.active) {
                    // Mark as configured and active
                    if (day !== currentSelectedDay) {
                        btn.classList.add('bg-blue-100', 'border-blue-400');
                        btn.classList.remove('border-slate-200');
                    }
                    
                    // Add checkmark indicator
                    const dayLabel = btn.querySelector('.text-[10px]');
                    if (!dayLabel.innerHTML.includes('✓')) {
                        dayLabel.innerHTML = '✓ ' + dayLabel.textContent.replace('✓ ', '');
                        dayLabel.classList.add('text-blue-700', 'font-bold');
                    }
                } else {
                    // Remove active styling and checkmark
                    if (day !== currentSelectedDay) {
                        btn.classList.remove('bg-blue-100', 'border-blue-400');
                        btn.classList.add('border-slate-200');
                    }
                    const dayLabel = btn.querySelector('.text-[10px]');
                    dayLabel.innerHTML = dayLabel.textContent.replace('✓ ', '');
                    dayLabel.classList.remove('text-blue-700', 'font-bold');
                }
            });
        }
        
        // Generate and display weekly summary
        function updateWeeklySummary() {
            const summaryContent = document.getElementById('summary-content');
            const summaryContainer = document.getElementById('weekly-summary');
            
            const activeDays = Object.entries(weeklyConfig).filter(([day, config]) => config.active);
            
            if (activeDays.length === 0) {
                summaryContainer.classList.add('hidden');
                return;
            }
            
            summaryContainer.classList.remove('hidden');
            
            let html = '';
            activeDays.forEach(([day, config]) => {
                html += `
                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check-circle text-green-600"></i>
                            <span class="font-bold text-slate-700">${dayNames[day]}</span>
                        </div>
                        <div class="text-right text-[11px] text-slate-600">
                            <div><strong>Threshold:</strong> ${config.threshold_on}%-${config.threshold_off}%</div>
                            <div><strong>Pagi:</strong> ${config.jam_pagi} | <strong>Sore:</strong> ${config.jam_sore}</div>
                        </div>
                    </div>
                `;
            });
            
            summaryContent.innerHTML = html;
        }

        // Weekly Loop System - Mode Manual
        let weeklyConfig = {
            senin: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            selasa: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            rabu: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            kamis: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            jumat: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            sabtu: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' },
            minggu: { active: false, threshold_on: 29, threshold_off: 61, jam_pagi: '07:00', jam_sore: '17:00' }
        };
        let currentSelectedDay = null;

        function selectDay(day) {
            currentSelectedDay = day;
            
            // Update day selector buttons
            document.querySelectorAll('.day-selector').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'border-blue-600', 'ring-4', 'ring-blue-200');
                btn.classList.add('border-slate-200', 'text-slate-600');
            });
            
            const selectedBtn = document.getElementById(`btn-day-${day}`);
            selectedBtn.classList.remove('border-slate-200', 'text-slate-600');
            selectedBtn.classList.add('bg-blue-500', 'text-white', 'border-blue-600', 'ring-4', 'ring-blue-200');
            
            // Show config area
            document.getElementById('day-config-area').classList.remove('hidden');
            
            // Update day name
            const dayNames = {
                senin: 'Senin',
                selasa: 'Selasa',
                rabu: 'Rabu',
                kamis: 'Kamis',
                jumat: 'Jumat',
                sabtu: 'Sabtu',
                minggu: 'Minggu'
            };
            document.getElementById('current-day-name').textContent = dayNames[day];
            
            // Load existing config for this day
            const config = weeklyConfig[day];
            document.getElementById('range-day-on').value = config.threshold_on;
            document.getElementById('range-day-off').value = config.threshold_off;
            document.getElementById('time-day-pagi').value = config.jam_pagi;
            document.getElementById('time-day-sore').value = config.jam_sore;
            
            // Update displays
            updateDayThresholdOnDisplay();
            updateDayThresholdOffDisplay();
            
            // Update active button
            const toggleBtn = document.getElementById('toggle-day-active');
            if (config.active) {
                toggleBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Hari Aktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition-all';
            } else {
                toggleBtn.innerHTML = '<i class="fa-solid fa-times mr-1"></i> Hari Nonaktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-slate-400 text-white text-xs font-bold hover:bg-slate-500 transition-all';
            }
            
            // Mark configured days
            updateDayMarkers();
        }

        function toggleDayActive() {
            if (!currentSelectedDay) return;
            
            weeklyConfig[currentSelectedDay].active = !weeklyConfig[currentSelectedDay].active;
            
            const toggleBtn = document.getElementById('toggle-day-active');
            if (weeklyConfig[currentSelectedDay].active) {
                toggleBtn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Hari Aktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition-all';
            } else {
                toggleBtn.innerHTML = '<i class="fa-solid fa-times mr-1"></i> Hari Nonaktif';
                toggleBtn.className = 'px-4 py-2 rounded-lg bg-slate-400 text-white text-xs font-bold hover:bg-slate-500 transition-all';
            }
            
            updateDayMarkers();
        }

        function updateDayThresholdOnDisplay() {
            const slider = document.getElementById('range-day-on');
            const percentage = parseInt(slider.value);
            const adc = percentageToADC(percentage);
            
            document.getElementById('val-day-on').textContent = percentage + '%';
            document.getElementById('adc-day-on').textContent = `ADC: ~${adc}`;
            
            // Update background color
            const valBox = document.getElementById('val-day-on');
            if (percentage <= 29) {
                valBox.className = 'px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 44) {
                valBox.className = 'px-4 py-2 bg-yellow-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 61) {
                valBox.className = 'px-4 py-2 bg-green-600 text-white rounded-lg font-bold text-lg text-center';
            } else {
                valBox.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-lg text-center';
            }
        }

        function updateDayThresholdOffDisplay() {
            const slider = document.getElementById('range-day-off');
            const percentage = parseInt(slider.value);
            const adc = percentageToADC(percentage);
            
            document.getElementById('val-day-off').textContent = percentage + '%';
            document.getElementById('adc-day-off').textContent = `ADC: ~${adc}`;
            
            // Update background color
            const valBox = document.getElementById('val-day-off');
            if (percentage <= 29) {
                valBox.className = 'px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 44) {
                valBox.className = 'px-4 py-2 bg-yellow-600 text-white rounded-lg font-bold text-lg text-center';
            } else if (percentage <= 61) {
                valBox.className = 'px-4 py-2 bg-green-600 text-white rounded-lg font-bold text-lg text-center';
            } else {
                valBox.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg font-bold text-lg text-center';
            }
        }

        function saveDayConfig() {
            if (!currentSelectedDay) return;
            
            // Save config for current day
            weeklyConfig[currentSelectedDay].threshold_on = parseInt(document.getElementById('range-day-on').value);
            weeklyConfig[currentSelectedDay].threshold_off = parseInt(document.getElementById('range-day-off').value);
            weeklyConfig[currentSelectedDay].jam_pagi = document.getElementById('time-day-pagi').value;
            weeklyConfig[currentSelectedDay].jam_sore = document.getElementById('time-day-sore').value;
            
            // Validation
            if (weeklyConfig[currentSelectedDay].threshold_off <= weeklyConfig[currentSelectedDay].threshold_on) {
                alert('⚠️ Batas Basah (OFF) harus lebih tinggi dari Batas Kering (ON)!');
                return;
            }
            
            // Update markers and summary
            updateDayMarkers();
            updateWeeklySummary();
            
            // Show success message
            alert(`✅ Konfigurasi ${document.getElementById('current-day-name').textContent} berhasil disimpan!`);
        }

        function updateDayMarkers() {
            const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
            days.forEach(day => {
                const btn = document.getElementById(`btn-day-${day}`);
                const config = weeklyConfig[day];
                
                if (config.active) {
                    // Mark as configured and active
                    if (day !== currentSelectedDay) {
                        btn.classList.add('bg-blue-100', 'border-blue-400');
                        btn.classList.remove('border-slate-200');
                    }
                    
                    // Add checkmark indicator
                    const dayLabel = btn.querySelector('.text-xs');
                    if (!dayLabel.innerHTML.includes('✓')) {
                        dayLabel.innerHTML = '✓ ' + dayLabel.textContent;
                        dayLabel.classList.add('text-blue-700');
                    }
                } else {
                    if (day !== currentSelectedDay) {
                        btn.classList.remove('bg-blue-100', 'border-blue-400');
                        btn.classList.add('border-slate-200');
                    }
                    
                    // Remove checkmark
                    const dayLabel = btn.querySelector('.text-xs');
                    dayLabel.innerHTML = dayLabel.textContent.replace('✓ ', '');
                    dayLabel.classList.remove('text-blue-700');
                }
            });
        }

        function updateWeeklySummary() {
            const summaryContent = document.getElementById('summary-content');
            const summaryContainer = document.getElementById('weekly-summary');
            
            const activeDays = Object.entries(weeklyConfig).filter(([day, config]) => config.active);
            
            if (activeDays.length === 0) {
                summaryContainer.classList.add('hidden');
                return;
            }
            
            summaryContainer.classList.remove('hidden');
            
            const dayNames = {
                senin: 'Senin',
                selasa: 'Selasa',
                rabu: 'Rabu',
                kamis: 'Kamis',
                jumat: 'Jumat',
                sabtu: 'Sabtu',
                minggu: 'Minggu'
            };
            
            let html = '';
            activeDays.forEach(([day, config]) => {
                html += `
                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check-circle text-green-600"></i>
                            <span class="font-bold text-slate-700">${dayNames[day]}</span>
                        </div>
                        <div class="text-right text-[11px] text-slate-600">
                            <div>Threshold: ${config.threshold_on}%-${config.threshold_off}%</div>
                            <div>Pagi: ${config.jam_pagi} | Sore: ${config.jam_sore}</div>
                        </div>
                    </div>
                `;
            });
            
            summaryContent.innerHTML = html;
        }

        function selectSmartMode(mode) {
            // Reset all cards
            document.querySelectorAll('.mode-card').forEach(card => {
                card.classList.remove('border-green-500', 'border-blue-500', 'border-yellow-500', 'border-slate-500', 'bg-green-50', 'bg-blue-50', 'bg-yellow-50', 'bg-slate-50', 'ring-4', 'ring-green-200', 'ring-blue-200', 'ring-yellow-200', 'ring-slate-200');
            });
            
            // Highlight selected card
            const selectedCard = document.getElementById(`card-mode-${mode}`);
            if (mode === 2) {
                selectedCard.classList.add('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-200');
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
            if (mode === 2) {
                // Mode AI Fuzzy: Auto (no config needed)
                document.getElementById('msg-auto').classList.remove('hidden');
            } else if (mode === 4) {
                // Mode Manual: Weekly Loop System
                document.getElementById('input-manual').classList.remove('hidden');
                // Reset weekly config
                currentSelectedDay = null;
                document.getElementById('day-config-area').classList.add('hidden');
                document.getElementById('weekly-summary').classList.add('hidden');
                updateDayMarkers();
            }
        }

        async function saveSmartConfiguration() {
            const deviceId = document.getElementById('config-device-id').value;
            const mode = parseInt(document.getElementById('selected-mode').value);
            
            console.log('Saving config - Device ID:', deviceId, 'Mode:', mode);
            
            if (!deviceId) {
                alert('⚠️ Silakan pilih perangkat terlebih dahulu!');
                return;
            }
            
            // Build request data based on mode
            const requestData = { mode };
            
            // === KALIBRASI ADC (ALWAYS SEND) ===
            const adcMin = parseInt(document.getElementById('input-adc-min').value);
            const adcMax = parseInt(document.getElementById('input-adc-max').value);
            
            console.log('ADC Values - Min:', adcMin, 'Max:', adcMax);
            
            // Validation: ADC Min must be greater than ADC Max
            if (adcMin <= adcMax) {
                alert('⚠️ Nilai ADC Kering harus lebih besar dari ADC Basah!\n\nContoh: Kering=4095, Basah=1500');
                return;
            }
            
            requestData.sensor_min = adcMin;
            requestData.sensor_max = adcMax;
            
            if (mode === 2) {
                // Mode AI Fuzzy: No additional parameters (fully automatic)
                // Backend will handle fuzzy logic
            } else if (mode === 4) {
                // Mode Manual: Weekly Loop System
                // Send weekly configuration
                const activeDays = Object.entries(weeklyConfig).filter(([day, config]) => config.active);
                
                if (activeDays.length === 0) {
                    alert('⚠️ Silakan aktifkan minimal 1 hari untuk Mode Manual!');
                    return;
                }
                
                requestData.weekly_schedule = weeklyConfig;
            }
            
            console.log('Request data:', requestData);
            
            try {
                const response = await axios.post(`/api/devices/${deviceId}/mode`, requestData);
                
                console.log('Response:', response.data);
                
                if (response.data.success) {
                    // Show success message with mode name
                    const modeNames = {
                        2: '🤖 Mode AI (Fuzzy)',
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
                console.error('Error response:', error.response);
                alert('❌ Error: ' + (error.response?.data?.message || error.message || 'Network error'));
            }
        }



        // ==================== MINIMALIST SETTINGS FUNCTIONS ====================
        let minimalCurrentMode = 2; // Default to AI Fuzzy mode
        let minimalDeviceId = 'PICO_TEST_01'; // Device ID (string, bukan integer)
        let minimalSettings = {
            device_name: '',
            mode: 2,
            weekly_schedule: null // For Mode 4 (Manual - Weekly Loop)
        };

        // Load settings when switching to settings page
        async function loadMinimalSettings() {
            try {
                // Cari device pertama yang available
                const devicesRes = await axios.get('/api/devices');
                if (devicesRes.data.data && devicesRes.data.data.length > 0) {
                    const firstDevice = devicesRes.data.data[0];
                    minimalDeviceId = firstDevice.device_id;
                    
                    // Update display
                    if (document.getElementById('current-device-id')) {
                        document.getElementById('current-device-id').textContent = minimalDeviceId;
                    }
                }
                
                const response = await axios.get(`/api/devices/${minimalDeviceId}`);
                if (response.data.success) {
                    const data = response.data.data;
                    minimalSettings = {
                        device_name: data.device_name || '',
                        mode: data.mode || 2,
                        weekly_schedule: data.weekly_schedule || null
                    };
                    
                    // Update UI
                    document.getElementById('minimal-device-name').value = minimalSettings.device_name;
                    
                    // Update last update time
                    if (document.getElementById('settings-last-update')) {
                        document.getElementById('settings-last-update').textContent = 
                            new Date().toLocaleTimeString('id-ID');
                    }
                    
                    setMinimalMode(minimalSettings.mode);
                }
            } catch (error) {
                console.error('Error loading minimal settings:', error);
                // Tetap tampilkan form dengan nilai default jika gagal
                setMinimalMode(2);
            }
        }

        function setMinimalMode(mode) {
            minimalCurrentMode = mode;
            minimalSettings.mode = mode;
            
            // Update button styles dengan design yang konsisten
            const modes = [2, 4];
            modes.forEach(i => {
                const btn = document.getElementById(`minimal-mode-${i}`);
                if (btn) {
                    if (i === mode) {
                        // Active state dengan warna berbeda per mode
                        btn.classList.remove('border-slate-200', 'text-slate-600', 'bg-white');
                        if (mode === 2) {
                            btn.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700', 'shadow-md');
                        } else if (mode === 4) {
                            btn.classList.add('border-slate-500', 'bg-slate-50', 'text-slate-700', 'shadow-md');
                        }
                    } else {
                        // Inactive state
                        btn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700',
                                            'border-slate-500', 'bg-slate-50', 'text-slate-700', 'shadow-md');
                        btn.classList.add('border-slate-200', 'text-slate-600', 'bg-white');
                    }
                }
            });
            
            // Update mode display
            const modeNames = {
                2: '🤖 Fuzzy Logic AI',
                4: '🛠️ Manual Control'
            };
            if (document.getElementById('current-mode-display')) {
                document.getElementById('current-mode-display').textContent = modeNames[mode];
            }
            
            // Update dynamic settings area
            updateMinimalSettingsArea();
        }

        function updateMinimalSettingsArea() {
            const area = document.getElementById('minimal-settings-area');
            
            if (minimalCurrentMode === 2) {
                // Fuzzy AI: Info card dengan style dashboard
                area.innerHTML = `
                    <div class="text-center py-6">
                        <div class="inline-block p-4 bg-blue-50 rounded-2xl mb-3">
                            <i class="fa-solid fa-robot text-5xl text-blue-600"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-2">Mode Fuzzy Logic AI</h4>
                        <p class="text-sm text-slate-600 mb-4">
                            Sistem mengaktifkan pompa air melalui relay apabila sensor mendeteksi kelembapan tanah turun di bawah <strong>ambang batas (threshold) 35-45%</strong> (kering). Proses penyiraman berhenti otomatis saat kelembapan tanah kembali mencapai batas normal.
                        </p>
                        <div class="bg-white rounded-lg p-4 space-y-3 text-left border border-blue-100">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-green-500 mt-0.5"></i>
                                <div class="text-sm text-slate-700">
                                    <strong>Pompa ON:</strong> Saat kelembapan < 35-45% (tanah kering)
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-stop-circle text-red-500 mt-0.5"></i>
                                <div class="text-sm text-slate-700">
                                    <strong>Pompa OFF:</strong> Saat kelembapan kembali normal (otomatis)
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs text-blue-700">
                                    <i class="fa-solid fa-info-circle mr-1"></i>
                                    <strong>Mode Otomatis Penuh:</strong> Sistem memantau kelembapan tanah secara real-time dan mengontrol pompa tanpa intervensi manual.
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            } else if (minimalCurrentMode === 4) {
                // Manual: Weekly Loop System - Direct user to modal
                area.innerHTML = `
                    <div class="space-y-6">
                        <div class="text-center py-6">
                            <div class="inline-block p-4 bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl mb-3 border-2 border-purple-200">
                                <i class="fa-solid fa-calendar-week text-5xl text-purple-600"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 mb-2">� Mode Manual - Weekly Loop System</h4>
                            <p class="text-sm text-slate-600 mb-4">
                                Sistem penjadwalan mingguan yang memungkinkan Anda mengatur <strong>threshold kelembaban</strong> dan <strong>jam penyiraman</strong> berbeda untuk setiap hari (Senin-Minggu).
                            </p>
                            
                            <!-- Feature List -->
                            <div class="bg-white rounded-lg p-4 space-y-3 text-left border border-purple-100 mb-4">
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-calendar-check text-purple-500 mt-0.5"></i>
                                    <div class="text-sm text-slate-700">
                                        <strong>Konfigurasi Per Hari:</strong> Setiap hari (Senin-Minggu) dapat memiliki pengaturan threshold dan jadwal yang berbeda
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-droplet text-blue-500 mt-0.5"></i>
                                    <div class="text-sm text-slate-700">
                                        <strong>Threshold Adaptif:</strong> Atur batas kering (ON) dan batas basah (OFF) dengan slider persentase
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-clock text-green-500 mt-0.5"></i>
                                    <div class="text-sm text-slate-700">
                                        <strong>Jadwal Fleksibel:</strong> Set jam penyiraman pagi dan sore untuk setiap hari aktif
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-repeat text-amber-500 mt-0.5"></i>
                                    <div class="text-sm text-slate-700">
                                        <strong>Loop Otomatis:</strong> Sistem berjalan terus menerus mengikuti siklus mingguan
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configuration Button -->
                            <button onclick="openSmartConfigModal()" 
                                    class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white py-4 px-6 rounded-xl font-bold transition-all shadow-lg shadow-purple-500/30 flex items-center justify-center gap-3">
                                <i class="fa-solid fa-calendar-days text-xl"></i>
                                <div>
                                    <div>Buka Konfigurasi Weekly Loop</div>
                                    <div class="text-xs font-normal opacity-90">Atur penjadwalan mingguan</div>
                                </div>
                            </button>
                            
                            <!-- Info Box -->
                            <div class="mt-4 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-lg">
                                <p class="text-xs text-slate-700 flex items-start gap-2">
                                    <i class="fa-solid fa-info-circle text-amber-600 mt-0.5"></i>
                                    <span><strong>Catatan:</strong> Konfigurasi Weekly Loop sangat lengkap dengan 7 hari × 4 parameter (threshold ON, threshold OFF, jam pagi, jam sore). Silakan gunakan modal untuk pengaturan yang lebih mudah.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        async function saveMinimalSettings() {
            const btn = document.getElementById('minimal-save-btn');
            const notif = document.getElementById('minimal-notif');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...';
            
            try {
                // Collect data
                const data = {
                    mode: minimalCurrentMode
                };
                
                // Mode 2 (Fuzzy AI): No additional settings needed
                // Mode 4 (Manual): Weekly schedule configured via modal, no settings here
                
                console.log('Saving settings:', { device_id: minimalDeviceId, data });
                
                // Save mode settings
                const modeResponse = await axios.post(`/api/devices/${minimalDeviceId}/mode`, data);
                console.log('Mode saved successfully:', modeResponse.data);
                
                // Save device name if changed
                const deviceName = document.getElementById('minimal-device-name').value;
                if (deviceName) {
                    const nameResponse = await axios.put(`/api/devices/${minimalDeviceId}`, { device_name: deviceName });
                    console.log('Device name saved successfully:', nameResponse.data);
                }
                
                // Show success notification
                notif.textContent = '✅ Berhasil disimpan!';
                notif.className = 'text-center text-sm font-medium py-3 rounded-xl bg-green-50 text-green-700 border border-green-200';
                notif.classList.remove('hidden');
                
                setTimeout(() => {
                    notif.classList.add('hidden');
                }, 3000);
                
            } catch (error) {
                console.error('Error saving minimal settings:', error);
                console.error('Response data:', error.response?.data);
                console.error('Device ID:', minimalDeviceId);
                
                let errorMsg = '❌ Gagal menyimpan.';
                if (error.response?.data?.message) {
                    errorMsg += ' ' + error.response.data.message;
                } else if (error.response?.data?.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    errorMsg += ' ' + errors.join(', ');
                } else if (error.message) {
                    errorMsg += ' ' + error.message;
                }
                
                notif.textContent = errorMsg;
                notif.className = 'text-center text-sm font-medium py-3 rounded-xl bg-red-50 text-red-700 border border-red-200';
                notif.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-save"></i> Simpan Perubahan';
            }
        }

        // Override switchPage to load settings and control auto-refresh
        const originalSwitchPage = switchPage;
        switchPage = function(pageId) {
            // Panggil fungsi switchPage asli DULU (untuk switch halaman)
            originalSwitchPage(pageId);
            
            // Kemudian jalankan logic tambahan setelah halaman switch
            
            // Load settings jika pindah ke halaman settings
            if (pageId === 'settings') {
                loadMinimalSettings();
            }
            
            // Load devices jika pindah ke halaman devices
            if (pageId === 'devices') {
                loadDevices();
            }
            
            // Load logs jika pindah ke halaman logs
            if (pageId === 'logs') {
                loadLogs();
            }
            
            // Control auto-refresh: hanya aktif di dashboard
            if (pageId === 'dashboard') {
                startDashboardAutoRefresh();
            } else {
                stopDashboardAutoRefresh();
            }
        };

        // Fungsi refresh untuk masing-masing halaman
        function refreshDevices() {
            loadDevices();
        }

        // Quick Actions Functions
        async function testPump() {
            if (!confirm('Tes pompa akan menyalakan pompa selama 5 detik. Lanjutkan?')) {
                return;
            }
            
            try {
                const response = await axios.post('/api/monitoring/relay/toggle', {
                    status: true,
                    test_mode: true,
                    duration: 5
                });
                
                if (response.data.success) {
                    alert('✅ Pompa berhasil dinyalakan! Akan mati otomatis setelah 5 detik.');
                } else {
                    alert('❌ Gagal menyalakan pompa.');
                }
            } catch (error) {
                console.error('Error testing pump:', error);
                alert('❌ Error: Tidak dapat menghubungi server.');
            }
        }

        function refreshSettings() {
            loadMinimalSettings();
            alert('✅ Pengaturan berhasil dimuat ulang!');
        }

        // =============================================================================
        // AUTO-REFRESH DASHBOARD - Refresh data setiap 3 detik
        // =============================================================================
        let dashboardRefreshInterval = null;

        function startDashboardAutoRefresh() {
            if (dashboardRefreshInterval) {
                clearInterval(dashboardRefreshInterval);
            }
            
            fetchStats();
            fetchHistory();
            
            // Real-time refresh setiap 1 detik
            dashboardRefreshInterval = setInterval(() => {
                fetchStats();
                fetchHistory();
            }, 1000);
            
            console.log('✅ Real-time monitoring started (every 1 second)');
        }

        function stopDashboardAutoRefresh() {
            if (dashboardRefreshInterval) {
                clearInterval(dashboardRefreshInterval);
                dashboardRefreshInterval = null;
                console.log('⏸️ Real-time monitoring stopped');
            }
        }

        // Start auto-refresh when page loads
        window.addEventListener('DOMContentLoaded', () => {
            startDashboardAutoRefresh();
        });
    </script>
</body>
</html>
