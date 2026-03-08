@extends('layouts.app')

@section('title', 'Monitoring')

@section('page-title', 'Data Monitoring')

@section('breadcrumbs')
    <a href="{{ route('dashboard.index') }}" class="text-slate-400 hover:text-slate-600">Dashboard</a>
    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
    <span class="text-slate-700 font-medium">Monitoring</span>
@endsection

@section('content')
<div x-data="monitoringPage()" x-init="init()">

    <!-- Filter & Control Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Device filter -->
            <select id="monitoring-device-filter" name="device_id" x-model="selectedDevice" @change="loadHistory()"
                    class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Perangkat</option>
                <template x-for="d in deviceList" :key="d.device_id">
                    <option :value="d.device_id" x-text="d.device_name || d.device_id"></option>
                </template>
            </select>

            <!-- Period filter -->
            <div class="flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden">
                <template x-for="p in periods" :key="p.value">
                    <button @click="selectedPeriod = p.value; loadHistory()"
                            :class="selectedPeriod === p.value ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50'"
                            class="px-4 py-2 text-sm font-medium transition-colors" x-text="p.label"></button>
                </template>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Live indicator -->
            <div x-show="isLive" class="flex items-center gap-2 text-green-600 text-sm font-medium">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Live
            </div>
            <button @click="exportData()" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-download"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-400 mb-1">Rata-rata Suhu</p>
            <p class="text-2xl font-bold text-blue-600" x-text="avgTemp !== null ? avgTemp + '°C' : '--'">--</p>
            <p class="text-xs text-slate-400 mt-1" x-text="'dari ' + history.length + ' data'"></p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-400 mb-1">Rata-rata Kelembaban</p>
            <p class="text-2xl font-bold text-green-600" x-text="avgSoil !== null ? avgSoil + '%' : '--'">--</p>
            <p class="text-xs text-slate-400 mt-1">Tanah</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-400 mb-1">Pompa Nyala</p>
            <p class="text-2xl font-bold text-amber-600" x-text="pumpOnCount">0</p>
            <p class="text-xs text-slate-400 mt-1">dari total data</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-400 mb-1">Total Record</p>
            <p class="text-2xl font-bold text-slate-700" x-text="history.length">0</p>
            <p class="text-xs text-slate-400 mt-1" x-text="periodLabel">7 hari terakhir</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <!-- Temperature Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900">Suhu Udara</h3>
                    <p class="text-xs text-slate-400 mt-0.5">°C — Sensor DHT22</p>
                </div>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fa-solid fa-temperature-half"></i>
                </div>
            </div>
            <div class="relative h-56">
                <canvas id="tempChart"></canvas>
                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-white/80">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- Soil Moisture Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900">Kelembaban Tanah</h3>
                    <p class="text-xs text-slate-400 mt-0.5">% — Sensor Capacitive</p>
                </div>
                <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                    <i class="fa-solid fa-droplet"></i>
                </div>
            </div>
            <div class="relative h-56">
                <canvas id="soilChart"></canvas>
                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-white/80">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Tabel Data</h3>
            <span class="text-xs text-slate-400" x-text="'Menampilkan ' + history.length + ' baris'"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Perangkat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Suhu</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Kelembaban</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">ADC Raw</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Pompa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Loading -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <div class="spinner mx-auto mb-3"></div>
                                <p>Memuat data...</p>
                            </td>
                        </tr>
                    </template>
                    <!-- Rows -->
                    <template x-if="!loading">
                        <template x-for="row in pagedHistory" :key="row.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    <p class="font-medium" x-text="formatDate(row.created_at)"></p>
                                    <p class="text-xs text-slate-400" x-text="formatTime(row.created_at)"></p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded" x-text="row.device_id"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold" :class="tempColor(row.temperature)" x-text="row.temperature != null ? row.temperature + '°C' : '--'"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full transition-all"
                                                 :class="soilBarColor(row.soil_moisture)"
                                                 :style="'width: ' + (row.soil_moisture || 0) + '%'"></div>
                                        </div>
                                        <span class="font-semibold text-slate-700 w-10 text-right" x-text="row.soil_moisture != null ? row.soil_moisture + '%' : '--'"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center font-mono text-xs text-slate-500" x-text="row.raw_adc ?? '--'"></td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="row.relay_status ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'"
                                          class="px-2.5 py-1 text-xs font-bold rounded-full"
                                          x-text="row.relay_status ? 'ON' : 'OFF'"></span>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <!-- Empty -->
                    <template x-if="!loading && history.length === 0">
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-slate-400">
                                <i class="fa-solid fa-chart-line text-4xl mb-3 block opacity-30"></i>
                                <p>Belum ada data monitoring</p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div x-show="totalPages > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-100">
            <p class="text-sm text-slate-500" x-text="'Halaman ' + currentPage + ' dari ' + totalPages"></p>
            <div class="flex items-center gap-2">
                <button @click="currentPage--" :disabled="currentPage <= 1"
                        class="px-3 py-1.5 text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg disabled:opacity-40 transition-colors">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button @click="currentPage++" :disabled="currentPage >= totalPages"
                        class="px-3 py-1.5 text-sm bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg disabled:opacity-40 transition-colors">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function monitoringPage() {
    return {
        history: [],
        deviceList: [],
        loading: true,
        selectedDevice: '',
        selectedPeriod: 50,
        currentPage: 1,
        perPage: 20,
        isLive: true,
        tempChart: null,
        soilChart: null,
        refreshInterval: null,

        periods: [
            { label: '50', value: 50 },
            { label: '100', value: 100 },
            { label: '200', value: 200 },
            { label: '500', value: 500 },
        ],

        get periodLabel() { return `${this.selectedPeriod} data terakhir`; },
        get avgTemp() {
            const vals = this.history.filter(r => r.temperature != null).map(r => parseFloat(r.temperature));
            return vals.length ? (vals.reduce((a,b) => a+b, 0) / vals.length).toFixed(1) : null;
        },
        get avgSoil() {
            const vals = this.history.filter(r => r.soil_moisture != null).map(r => parseFloat(r.soil_moisture));
            return vals.length ? (vals.reduce((a,b) => a+b, 0) / vals.length).toFixed(1) : null;
        },
        get pumpOnCount() { return this.history.filter(r => r.relay_status).length; },
        get totalPages() { return Math.ceil(this.history.length / this.perPage); },
        get pagedHistory() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.history.slice(start, start + this.perPage);
        },

        async init() {
            await Promise.all([this.loadDevices(), this.loadHistory()]);
            this.refreshInterval = setInterval(() => { if (this.isLive) this.loadHistory(false); }, 10000);
        },

        async loadDevices() {
            try {
                const res = await fetch('/api/devices');
                const json = await res.json();
                if (json.success) this.deviceList = json.data;
            } catch(e) {}
        },

        async loadHistory(showLoader = true) {
            if (showLoader) this.loading = true;
            try {
                const url = `/api/monitoring/history?limit=${this.selectedPeriod}`;
                const res = await fetch(url);
                const json = await res.json();
                if (json.success) {
                    // API returns latest-first; reverse so oldest→newest for charts/table
                    let data = [...json.data].reverse();
                    if (this.selectedDevice) data = data.filter(r => r.device_id === this.selectedDevice);
                    this.history = data;
                    this.currentPage = 1;
                    this.updateCharts();
                }
            } catch(e) { console.error(e); }
            finally { this.loading = false; }
        },

        updateCharts() {
            // history is already oldest→newest, take last 60 points for charts
            const displayData = this.history.slice(-60);
            const labels = displayData.map(r => this.formatTime(r.created_at));
            const temps = displayData.map(r => r.temperature);
            const soils = displayData.map(r => r.soil_moisture);

            const chartDefaults = {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8, font: { size: 11 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
                },
                elements: { point: { radius: 2 }, line: { tension: 0.4 } }
            };

            if (this.tempChart) this.tempChart.destroy();
            const tc = document.getElementById('tempChart');
            if (tc) {
                this.tempChart = new Chart(tc, {
                    type: 'line', data: {
                        labels,
                        datasets: [{ label: 'Suhu (°C)', data: temps, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', fill: true, borderWidth: 2 }]
                    },
                    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, min: 20, max: 45 } } }
                });
            }

            if (this.soilChart) this.soilChart.destroy();
            const sc = document.getElementById('soilChart');
            if (sc) {
                this.soilChart = new Chart(sc, {
                    type: 'line', data: {
                        labels,
                        datasets: [{ label: 'Kelembaban (%)', data: soils, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.08)', fill: true, borderWidth: 2 }]
                    },
                    options: { ...chartDefaults, scales: { ...chartDefaults.scales, y: { ...chartDefaults.scales.y, min: 0, max: 100 } } }
                });
            }
        },

        formatDate(ts) {
            if (!ts) return '--';
            return new Date(ts).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        },
        formatTime(ts) {
            if (!ts) return '--';
            return new Date(ts).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },
        tempColor(t) {
            if (t == null) return 'text-slate-400';
            if (t > 35) return 'text-red-600';
            if (t > 30) return 'text-amber-600';
            return 'text-blue-600';
        },
        soilBarColor(s) {
            if (s == null) return 'bg-slate-300';
            if (s < 20) return 'bg-red-400';
            if (s < 40) return 'bg-amber-400';
            return 'bg-green-400';
        },

        exportData() {
            if (!this.history.length) return;
            const headers = ['Waktu', 'Device', 'Suhu (°C)', 'Kelembaban (%)', 'ADC Raw', 'Pompa'];
            const rows = this.history.map(r => [
                r.created_at, r.device_id, r.temperature ?? '', r.soil_moisture ?? '', r.raw_adc ?? '', r.relay_status ? 'ON' : 'OFF'
            ]);
            const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = `monitoring_${new Date().toISOString().slice(0,10)}.csv`;
            a.click(); URL.revokeObjectURL(url);
        },
    };
}
</script>
@endpush
