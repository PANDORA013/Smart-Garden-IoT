@extends('layouts.app')

@section('title', 'Riwayat Log')

@section('page-title', 'Riwayat Aktivitas')

@section('breadcrumbs')
    <a href="{{ route('dashboard.index') }}" class="text-slate-400 hover:text-slate-600">Dashboard</a>
    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
    <span class="text-slate-700 font-medium">Log</span>
@endsection

@section('content')
<div x-data="logsPage()" x-init="init()" @alpine:destroyed="destroy()"

    <!-- Controls -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Level filter -->
            <div class="flex items-center bg-white border border-slate-200 rounded-xl overflow-hidden text-sm">
                <template x-for="lvl in levels" :key="lvl.value">
                    <button @click="filterLevel = lvl.value"
                            :class="filterLevel === lvl.value ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50'"
                            class="px-3 py-2 font-medium transition-colors" x-text="lvl.label"></button>
                </template>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <!-- Auto-refresh toggle -->
            <button @click="autoRefresh = !autoRefresh" 
                    :class="autoRefresh ? 'bg-green-50 border-green-200 text-green-700' : 'bg-white border-slate-200 text-slate-600'"
                    class="flex items-center gap-2 px-4 py-2 border rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-rotate" :class="autoRefresh && 'animate-spin'"></i>
                <span x-text="autoRefresh ? 'Auto-Refresh ON' : 'Auto-Refresh OFF'"></span>
            </button>
            <button @click="loadLogs()" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-arrows-rotate" :class="loading && 'animate-spin'"></i>
                Refresh
            </button>
        </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <template x-for="stat in logStats" :key="stat.level">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-3">
                <div class="p-2 rounded-xl shrink-0" :class="stat.bg">
                    <i :class="'fa-solid fa-' + stat.icon + ' ' + stat.color"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400" x-text="stat.label"></p>
                    <p class="text-xl font-bold text-slate-800" x-text="stat.count">0</p>
                </div>
            </div>
        </template>
    </div>

    <!-- Log feed -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Feed Aktivitas</h3>
            <span class="text-xs text-slate-400" x-text="filteredLogs.length + ' entri'"></span>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex items-center justify-center py-16">
            <div class="spinner"></div>
        </div>

        <!-- Log list -->
        <div x-show="!loading" class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto">
            <template x-if="filteredLogs.length === 0">
                <div class="py-16 text-center text-slate-400">
                    <i class="fa-solid fa-list-check text-4xl mb-3 block opacity-30"></i>
                    <p>Tidak ada log yang cocok</p>
                </div>
            </template>

            <template x-for="log in filteredLogs" :key="log.id">
                <div class="flex items-start gap-4 px-6 py-4 hover:bg-slate-50 transition-colors group">
                    <!-- Level indicator -->
                    <div class="shrink-0 mt-0.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             :class="levelStyle(log.level).bg">
                            <i :class="'fa-solid fa-' + levelStyle(log.level).icon + ' text-xs ' + levelStyle(log.level).color"></i>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                  :class="levelStyle(log.level).badge" x-text="log.level"></span>
                            <span class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded" x-text="log.device"></span>
                            <span class="text-xs text-slate-400" x-text="log.date + ' ' + log.time"></span>
                        </div>
                        <p class="text-sm font-medium text-slate-800" x-text="log.message"></p>
                        <p x-show="log.details" class="text-xs text-slate-500 mt-0.5" x-text="log.details"></p>
                    </div>

                    <!-- Sensor values -->
                    <div x-show="log.soil_moisture != null" class="shrink-0 text-right hidden sm:block">
                        <p class="text-xs font-semibold text-green-600" x-text="log.soil_moisture + '%'"></p>
                        <p class="text-xs text-slate-400" x-text="log.temperature + '°C'"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Relay Events Timeline -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Timeline Relay</h3>
            <p class="text-xs text-slate-400 mt-0.5">Riwayat perubahan status pompa</p>
        </div>
        <div class="p-6">
            <template x-if="relayEvents.length === 0">
                <p class="text-center text-slate-400 text-sm py-6">Belum ada perubahan relay</p>
            </template>
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200"></div>
                <div class="space-y-4">
                    <template x-for="event in relayEvents" :key="event.id">
                        <div class="flex items-start gap-4 pl-10 relative">
                            <div class="absolute left-0 top-1.5 w-8 h-8 rounded-full flex items-center justify-center border-2 border-white shadow-sm"
                                 :class="event.relay_status ? 'bg-amber-400' : 'bg-slate-200'">
                                <i :class="event.relay_status ? 'fa-solid fa-bolt text-white text-xs' : 'fa-solid fa-circle-xmark text-slate-500 text-xs'"></i>
                            </div>
                            <div class="flex-1 bg-slate-50 rounded-xl p-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-sm" :class="event.relay_status ? 'text-amber-700' : 'text-slate-600'"
                                          x-text="event.relay_status ? '🟢 Pompa NYALA' : '🔴 Pompa MATI'"></span>
                                    <span class="text-xs text-slate-400 font-mono" x-text="event.date + ' ' + event.time"></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1" x-text="'Soil: ' + event.soil_moisture + '% | Suhu: ' + event.temperature + '°C'"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function logsPage() {
    return {
        logs: [],
        loading: true,
        autoRefresh: true,
        filterLevel: 'ALL',
        refreshInterval: null,

        levels: [
            { value: 'ALL', label: 'Semua' },
            { value: 'SUCCESS', label: '✓ Sukses' },
            { value: 'WARN', label: '⚠ Warn' },
            { value: 'ERROR', label: '✕ Error' },
            { value: 'INFO', label: 'ℹ Info' },
        ],

        get filteredLogs() {
            if (this.filterLevel === 'ALL') return this.logs;
            return this.logs.filter(l => l.level === this.filterLevel);
        },

        get relayEvents() {
            // Show entries where relay_status is explicitly set (pump ON or OFF events)
            return this.logs
                .filter(l => l.relay_status !== null && l.relay_status !== undefined)
                .filter((l, i, arr) => {
                    // Keep only entries where relay changed from previous visible entry
                    const prev = arr[i + 1];
                    return !prev || l.relay_status !== prev.relay_status;
                })
                .slice(0, 15);
        },

        get logStats() {
            const counts = { SUCCESS: 0, WARN: 0, ERROR: 0, INFO: 0 };
            this.logs.forEach(l => { if (counts[l.level] !== undefined) counts[l.level]++; });
            return [
                { level: 'SUCCESS', label: 'Sukses', count: counts.SUCCESS, icon: 'circle-check', color: 'text-green-600', bg: 'bg-green-50', badge: 'bg-green-100 text-green-700' },
                { level: 'INFO', label: 'Info', count: counts.INFO, icon: 'circle-info', color: 'text-blue-600', bg: 'bg-blue-50', badge: 'bg-blue-100 text-blue-700' },
                { level: 'WARN', label: 'Peringatan', count: counts.WARN, icon: 'triangle-exclamation', color: 'text-amber-600', bg: 'bg-amber-50', badge: 'bg-amber-100 text-amber-700' },
                { level: 'ERROR', label: 'Error', count: counts.ERROR, icon: 'circle-xmark', color: 'text-red-600', bg: 'bg-red-50', badge: 'bg-red-100 text-red-700' },
            ];
        },

        levelStyle(level) {
            const styles = {
                SUCCESS: { icon: 'circle-check', color: 'text-green-600', bg: 'bg-green-50', badge: 'bg-green-100 text-green-700' },
                INFO: { icon: 'circle-info', color: 'text-blue-600', bg: 'bg-blue-50', badge: 'bg-blue-100 text-blue-700' },
                WARN: { icon: 'triangle-exclamation', color: 'text-amber-600', bg: 'bg-amber-50', badge: 'bg-amber-100 text-amber-700' },
                ERROR: { icon: 'circle-xmark', color: 'text-red-600', bg: 'bg-red-50', badge: 'bg-red-100 text-red-700' },
            };
            return styles[level] || styles.INFO;
        },

        async init() {
            await this.loadLogs();
            this.refreshInterval = setInterval(() => { if (this.autoRefresh) this.loadLogs(false); }, 5000);
        },

        destroy() {
            if (this.refreshInterval) clearInterval(this.refreshInterval);
        },

        async loadLogs(showLoader = true) {
            if (showLoader) this.loading = true;
            try {
                const res = await fetch('/api/monitoring/logs?limit=50');
                const json = await res.json();
                if (json.success) this.logs = json.data;
            } catch(e) { console.error(e); }
            finally { this.loading = false; }
        },
    };
}
</script>
@endpush
