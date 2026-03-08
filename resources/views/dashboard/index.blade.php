@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Monitoring Real-time')

@section('breadcrumbs')
    <span class="text-slate-400">Dashboard</span>
    <i class="fa-solid fa-chevron-right text-xs"></i>
    <span class="text-slate-700 font-medium">Monitoring</span>
@endsection

@section('content')
<div x-data="dashboardData()" x-init="init()" @alpine:destroyed="destroy()"
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Temperature Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-temperature-half text-xl"></i>
                </div>
            </div>
            <p class="text-slate-500 text-sm font-medium mb-2">Suhu Udara</p>
            <h3 class="text-3xl font-bold text-slate-900 mb-1">
                <span x-text="stats.temperature">--</span><span class="text-lg text-slate-500">°C</span>
            </h3>
            <p class="text-xs text-slate-400 mt-2">Sensor DHT22</p>
        </div>
        
        <!-- Soil Moisture Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-green-50 text-green-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-seedling text-xl"></i>
                </div>
            </div>
            <p class="text-slate-500 text-sm font-medium mb-2">Kelembaban Tanah</p>
            <h3 class="text-3xl font-bold text-slate-900 mb-1">
                <span x-text="stats.soilMoisture">--</span><span class="text-lg text-slate-500">%</span>
            </h3>
            <p class="text-xs text-slate-400 mt-2">Capacitive Sensor</p>
        </div>
        
        <!-- Soil Condition Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-chart-line text-xl"></i>
                </div>
            </div>
            <p class="text-slate-500 text-sm font-medium mb-2">Kondisi Tanah</p>
            <h3 class="text-2xl font-bold text-slate-900 mb-1" x-text="stats.soilCondition">Memuat...</h3>
            <p class="text-xs text-slate-400 mt-2" x-text="'ADC: ' + stats.rawAdc">ADC: --</p>
        </div>
        
        <!-- Relay Status Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <i class="fa-solid fa-lightbulb text-xl"></i>
                </div>
                
                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center" :class="togglingRelay ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                    <input 
                        type="checkbox"
                        id="relay-toggle"
                        name="relay_status"
                        class="sr-only peer" 
                        x-model="stats.relayStatus"
                        @change="toggleRelay()"
                        :disabled="togglingRelay"
                    >
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:bg-amber-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
            </div>
            
            <p class="text-slate-500 text-sm font-medium mb-2">Status Pompa</p>
            <h3 class="text-3xl font-bold text-slate-900 mb-1" x-text="stats.relayStatus ? 'ON' : 'OFF'">OFF</h3>
            <p class="text-xs text-slate-400 mt-2">Manual Control</p>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Main Chart -->
        <div class="xl:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Grafik Real-time</h3>
                    <p class="text-xs text-slate-500 mt-1">Monitoring suhu & kelembaban tanah</p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg font-semibold">
                        <i class="fa-solid fa-temperature-half"></i> Suhu
                    </span>
                    <span class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg font-semibold">
                        <i class="fa-solid fa-droplet"></i> Kelembaban
                    </span>
                </div>
            </div>
            
            <div class="relative h-80">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
        
        <!-- Device Status Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="font-bold text-lg text-slate-900 mb-4">Status Perangkat</h3>
            
            <div class="space-y-4">
                <!-- Device Item -->
                <template x-for="device in devices" :key="device.id">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-microchip text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900" x-text="device.name"></p>
                                <p class="text-xs text-slate-500" x-text="device.id"></p>
                            </div>
                        </div>
                        <span 
                            :class="device.online ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                            class="px-2 py-1 text-xs font-bold rounded-full"
                            x-text="device.online ? 'Online' : 'Offline'"
                        ></span>
                    </div>
                </template>
            </div>
            
            <button class="w-full mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Perangkat
            </button>
        </div>
    </div>
    
    <!-- ADC Reference Guide -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                <i class="fa-solid fa-info-circle text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg text-slate-900">Panduan Nilai ADC</h3>
                <p class="text-xs text-slate-500">Referensi kondisi kelembaban tanah berdasarkan nilai ADC sensor</p>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Nilai ADC</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Persentase</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-red-50 transition-colors">
                        <td class="px-4 py-3"><span class="px-2 py-1 bg-red-100 text-red-700 font-semibold rounded text-xs">0 – 500</span></td>
                        <td class="px-4 py-3 font-medium">0% – 12%</td>
                        <td class="px-4 py-3"><span class="flex items-center gap-2 text-red-700 font-medium"><i class="fa-solid fa-triangle-exclamation"></i> Sangat Kering</span></td>
                        <td class="px-4 py-3"><span class="text-red-600 font-bold">💧 HIDUPKAN</span> pompa segera</td>
                    </tr>
                    <tr class="hover:bg-orange-50 transition-colors">
                        <td class="px-4 py-3"><span class="px-2 py-1 bg-orange-100 text-orange-700 font-semibold rounded text-xs">501 – 1199</span></td>
                        <td class="px-4 py-3 font-medium">12% – 29%</td>
                        <td class="px-4 py-3"><span class="flex items-center gap-2 text-orange-700 font-medium"><i class="fa-solid fa-fire"></i> Kering</span></td>
                        <td class="px-4 py-3"><span class="text-orange-600 font-bold">💧 HIDUPKAN</span> pompa</td>
                    </tr>
                    <tr class="hover:bg-yellow-50 transition-colors">
                        <td class="px-4 py-3"><span class="px-2 py-1 bg-yellow-100 text-yellow-700 font-semibold rounded text-xs">1200 – 1800</span></td>
                        <td class="px-4 py-3 font-medium">29% – 44%</td>
                        <td class="px-4 py-3"><span class="flex items-center gap-2 text-yellow-700 font-medium"><i class="fa-solid fa-cloud"></i> Lembab</span></td>
                        <td class="px-4 py-3"><span class="text-green-600 font-bold">✓ MATIKAN</span> pompa</td>
                    </tr>
                    <tr class="hover:bg-green-50 transition-colors">
                        <td class="px-4 py-3"><span class="px-2 py-1 bg-green-100 text-green-700 font-semibold rounded text-xs">1801 – 2500</span></td>
                        <td class="px-4 py-3 font-medium">44% – 61%</td>
                        <td class="px-4 py-3"><span class="flex items-center gap-2 text-green-700 font-medium"><i class="fa-solid fa-seedling"></i> Ideal</span></td>
                        <td class="px-4 py-3"><span class="text-green-600 font-bold">✓ MATIKAN</span> (kondisi terbaik)</td>
                    </tr>
                    <tr class="hover:bg-cyan-50 transition-colors">
                        <td class="px-4 py-3"><span class="px-2 py-1 bg-cyan-100 text-cyan-700 font-semibold rounded text-xs">2501 – 3000</span></td>
                        <td class="px-4 py-3 font-medium">61% – 73%</td>
                        <td class="px-4 py-3"><span class="flex items-center gap-2 text-cyan-700 font-medium"><i class="fa-solid fa-droplet"></i> Basah</span></td>
                        <td class="px-4 py-3"><span class="text-slate-600 font-bold">✓ MATIKAN</span> jangan siram</td>
                    </tr>
                    <tr class="hover:bg-blue-50 transition-colors">
                        <td class="px-4 py-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 font-semibold rounded text-xs">3001 – 4095</span></td>
                        <td class="px-4 py-3 font-medium">73% – 100%</td>
                        <td class="px-4 py-3"><span class="flex items-center gap-2 text-blue-700 font-medium"><i class="fa-solid fa-water"></i> Sangat Basah</span></td>
                        <td class="px-4 py-3"><span class="text-red-600 font-bold">⚠ MATIKAN</span> risiko busuk akar</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardData() {
    return {
        stats: {
            temperature: '--',
            soilMoisture: '--',
            soilCondition: 'Memuat...',
            rawAdc: '--',
            relayStatus: false
        },
        togglingRelay: false, // Prevent double-click / in-flight toggle
        // Devices are loaded dynamically from /api/devices to prevent stale hardcoded IDs
        devices: [],
        chart: null,
        chartData: {
            labels: [],
            temperature: [],
            soilMoisture: []
        },
        // Store interval IDs so they can be cleared on component destroy
        _intervals: [],
        
        init() {
            // Use $nextTick to ensure the full DOM (including <canvas>) is rendered
            // before Chart.js tries to call getContext('2d') on it.
            this.$nextTick(() => {
                this.initChart();
                this.fetchDevices();
                this.fetchLatestData();

                this._intervals.push(setInterval(() => {
                    this.fetchLatestData();
                }, 2000));
                this._intervals.push(setInterval(() => {
                    this.fetchDevices();
                }, 30000));
            });
        },

        destroy() {
            this._intervals.forEach(id => clearInterval(id));
            this._intervals = [];
            if (this.chart) { this.chart.destroy(); this.chart = null; }
        },

        async fetchDevices() {
            try {
                const response = await axios.get('/api/devices');
                if (response.data.success) {
                    this.devices = response.data.data.map(d => ({
                        id: d.device_id,
                        name: d.device_name || d.device_id,
                        online: d.is_online ?? false,
                    }));
                }
            } catch (error) {
                console.error('Error fetching devices:', error);
            }
        },
        
        initChart() {
            const canvas = document.getElementById('mainChart');
            if (!canvas) {
                console.warn('mainChart canvas not found — chart init skipped');
                return;
            }
            const ctx = canvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.chartData.labels,
                    datasets: [
                        {
                            label: 'Suhu (°C)',
                            data: this.chartData.temperature,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Kelembaban Tanah (%)',
                            data: this.chartData.soilMoisture,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        },
        
        async fetchLatestData() {
            try {
                // Pass device_id of first online device for accurate per-device data
                const activeDevice = this.devices.find(d => d.online);
                const params = activeDevice ? { device_id: activeDevice.id } : {};
                const response = await axios.get('/api/monitoring/latest', { params });
                const data = response.data;
                
                if (data.success) {
                    this.stats.temperature = data.data.temperature?.toFixed(1) || '--';
                    this.stats.soilMoisture = data.data.soil_moisture?.toFixed(0) || '--';
                    this.stats.rawAdc = data.data.raw_adc || '--';
                    this.stats.relayStatus = data.data.relay_status || false;
                    this.stats.soilCondition = this.getSoilCondition(data.data.raw_adc);
                    
                    this.updateChart(data.data);
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        },
        
        getSoilCondition(adc) {
            if (!adc) return 'N/A';
            if (adc <= 500) return 'Sangat Kering';
            if (adc <= 1199) return 'Kering';
            if (adc <= 1800) return 'Lembab';
            if (adc <= 2500) return 'Ideal';
            if (adc <= 3000) return 'Basah';
            return 'Sangat Basah';
        },
        
        updateChart(data) {
            // Guard: chart may not be ready yet if initChart() ran before canvas was in DOM
            if (!this.chart) return;

            const time = new Date().toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            
            this.chartData.labels.push(time);
            this.chartData.temperature.push(data.temperature);
            this.chartData.soilMoisture.push(data.soil_moisture);
            
            // Keep only last 20 points
            if (this.chartData.labels.length > 20) {
                this.chartData.labels.shift();
                this.chartData.temperature.shift();
                this.chartData.soilMoisture.shift();
            }
            
            this.chart.update('none');
        },
        
        async toggleRelay() {
            if (this.togglingRelay) return; // Debounce: ignore if already in flight
            this.togglingRelay = true;
            try {
                // Use first online device, or fallback to PICO_CABAI_01
                const targetDevice = this.devices.find(d => d.online)?.id || 'PICO_CABAI_01';
                const response = await axios.post('/api/monitoring/relay/toggle', {
                    device_id: targetDevice,
                    relay_status: this.stats.relayStatus,
                    status: this.stats.relayStatus, // backward compat
                });
                
                if (response.data.success) {
                    showToast(
                        `Pompa berhasil ${this.stats.relayStatus ? 'dihidupkan' : 'dimatikan'}`, 
                        'success'
                    );
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                console.error('Error toggling relay:', error);
                this.stats.relayStatus = !this.stats.relayStatus; // Revert
                const msg = error.response?.data?.message || 'Gagal mengubah status pompa';
                showToast(msg, 'error');
            } finally {
                this.togglingRelay = false;
            }
        }
    }
}
</script>
@endpush
