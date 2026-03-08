@extends('layouts.app')

@section('title', 'Pengaturan')

@section('page-title', 'Pengaturan Sistem')

@section('breadcrumbs')
    <a href="{{ route('dashboard.index') }}" class="text-slate-400 hover:text-slate-600">Dashboard</a>
    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
    <span class="text-slate-700 font-medium">Pengaturan</span>
@endsection

@section('content')
<div x-data="settingsPage()" x-init="init()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-6">
                <div class="px-4 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900">Navigasi</h3>
                </div>
                <nav class="p-3 space-y-1">
                    <template x-for="tab in tabs" :key="tab.id">
                        <button @click="activeTab = tab.id"
                                :class="activeTab === tab.id ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-50'"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition-colors text-left">
                            <i :class="'fa-solid fa-' + tab.icon + ' w-4 text-center'"></i>
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </nav>
            </div>
        </div>

        <!-- Content Panel -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Device Settings Tab -->
            <div x-show="activeTab === 'device'" x-transition>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                        <div>
                            <h3 class="font-bold text-slate-900">Konfigurasi Perangkat</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Atur parameter operasional perangkat IoT</p>
                        </div>
                        <!-- Device selector -->
                        <select id="settings-device-selector" name="device_id" x-model="selectedDeviceId" @change="loadDeviceSettings()"
                                class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Perangkat</option>
                            <template x-for="d in devices" :key="d.device_id">
                                <option :value="d.device_id" x-text="d.device_name || d.device_id"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="!selectedDeviceId" class="py-16 text-center text-slate-400">
                        <i class="fa-solid fa-microchip text-4xl mb-3 block opacity-30"></i>
                        <p>Pilih perangkat untuk mengubah pengaturan</p>
                    </div>

                    <div x-show="selectedDeviceId && deviceSettings">
                        <!-- Mode Selection -->
                        <div class="p-6 border-b border-slate-100">
                            <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-robot text-purple-500"></i> Mode Operasi
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <template x-for="m in operationModes" :key="m.value">
                                    <button @click="deviceForm.mode = m.value"
                                            :class="deviceForm.mode == m.value ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'"
                                            class="flex items-start gap-3 p-3 border-2 rounded-xl text-left transition-all">
                                        <div class="p-2 rounded-lg mt-0.5 shrink-0" :class="m.bg">
                                            <i :class="'fa-solid fa-' + m.icon + ' text-sm ' + m.color"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800" x-text="m.label"></p>
                                            <p class="text-xs text-slate-400 mt-0.5" x-text="m.desc"></p>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Threshold Settings (Mode 1, 2 & 4) -->
                        <div class="p-6 border-b border-slate-100" x-show="[1,2,4].includes(parseInt(deviceForm.mode))">
                            <h4 class="font-semibold text-slate-800 mb-1 flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-green-500"></i> Threshold Kelembaban
                            </h4>
                            <p class="text-xs text-slate-400 mb-4" x-show="parseInt(deviceForm.mode) === 2">
                                Mode Fuzzy AI menggunakan nilai ini sebagai batas bawah/atas logika cerdas
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Batas Siram (Kering) <span class="text-blue-500">%</span>
                                    </label>
                                    <input type="range" id="settings-batas-siram" name="batas_siram" x-model="deviceForm.batas_siram" min="10" max="50" step="5"
                                           class="w-full accent-blue-600">
                                    <div class="flex justify-between text-xs text-slate-400 mt-1">
                                        <span>10%</span>
                                        <span class="font-bold text-blue-600" x-text="deviceForm.batas_siram + '%'"></span>
                                        <span>50%</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        Batas Stop (Basah) <span class="text-green-500">%</span>
                                    </label>
                                    <input type="range" id="settings-batas-stop" name="batas_stop" x-model="deviceForm.batas_stop" min="30" max="80" step="5"
                                           class="w-full accent-green-600">
                                    <div class="flex justify-between text-xs text-slate-400 mt-1">
                                        <span>30%</span>
                                        <span class="font-bold text-green-600" x-text="deviceForm.batas_stop + '%'"></span>
                                        <span>80%</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Visual hysteresis band -->
                            <div class="mt-4 bg-slate-50 rounded-xl p-3">
                                <p class="text-xs text-slate-500 mb-2">Visualisasi Zona Hysteresis</p>
                                <div class="relative h-6 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="absolute h-full bg-red-200 rounded-l-full" :style="'width: ' + deviceForm.batas_siram + '%'"></div>
                                    <div class="absolute h-full bg-amber-200" :style="'left: ' + deviceForm.batas_siram + '%; width: ' + (deviceForm.batas_stop - deviceForm.batas_siram) + '%'"></div>
                                    <div class="absolute h-full bg-green-200 rounded-r-full" :style="'left: ' + deviceForm.batas_stop + '%; right: 0'"></div>
                                </div>
                                <div class="flex justify-between text-xs mt-1">
                                    <span class="text-red-500">🌵 Kering → Siram</span>
                                    <span class="text-amber-500">⚖ Hysteresis</span>
                                    <span class="text-green-500">💧 Basah → Stop</span>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule (Mode 3) -->
                        <div class="p-6 border-b border-slate-100" x-show="parseInt(deviceForm.mode) === 3">
                            <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-clock text-amber-500"></i> Jadwal Penyiraman
                            </h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Jam Pagi</label>
                                    <input type="time" id="settings-jam-pagi" name="jam_pagi" x-model="deviceForm.jam_pagi"
                                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Jam Sore</label>
                                    <input type="time" id="settings-jam-sore" name="jam_sore" x-model="deviceForm.jam_sore"
                                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Durasi (menit)</label>
                                    <input type="number" id="settings-durasi-siram" name="durasi_siram" x-model="deviceForm.durasi_siram" min="1" max="60"
                                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Calibration -->
                        <div class="p-6 border-b border-slate-100">
                            <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-ruler text-indigo-500"></i> Kalibrasi Sensor ADC
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">ADC Kering (Maks)</label>
                                    <input type="number" id="settings-sensor-min" name="sensor_min" x-model="deviceForm.sensor_min" min="0" max="4095"
                                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="text-xs text-slate-400 mt-1">Nilai ADC saat tanah kering</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">ADC Basah (Min)</label>
                                    <input type="number" id="settings-sensor-max" name="sensor_max" x-model="deviceForm.sensor_max" min="0" max="4095"
                                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="text-xs text-slate-400 mt-1">Nilai ADC saat tanah basah</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-3">
                                <button @click="resetCalibration()" class="flex items-center gap-2 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-sm font-medium transition-colors">
                                    <i class="fa-solid fa-rotate"></i> Reset & Auto-Kalibrasi
                                </button>
                                <p class="text-xs text-slate-400">Akan mengumpulkan 30 sample untuk kalibrasi otomatis</p>
                            </div>
                        </div>

                        <!-- Preset -->
                        <div class="p-6 border-b border-slate-100">
                            <h4 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-wand-magic-sparkles text-pink-500"></i> Terapkan Preset
                            </h4>
                            <div class="flex gap-3">
                                <button @click="applyPreset('cabai')" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-sm font-medium transition-colors">
                                    🌶️ Preset Cabai
                                </button>
                                <button @click="applyPreset('tomat')" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-orange-50 hover:bg-orange-100 border border-orange-200 text-orange-700 rounded-xl text-sm font-medium transition-colors">
                                    🍅 Preset Tomat
                                </button>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="p-6">
                            <button @click="saveDeviceSettings()" :disabled="saving"
                                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk" x-show="!saving"></i>
                                <i class="fa-solid fa-spinner animate-spin" x-show="saving"></i>
                                <span x-text="saving ? 'Menyimpan...' : 'Simpan Pengaturan'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Info Tab -->
            <div x-show="activeTab === 'system'" x-transition>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900">Informasi Sistem</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Detail teknis aplikasi Smart Garden</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <template x-for="info in systemInfo" :key="info.key">
                            <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
                                <span class="text-sm text-slate-500 flex items-center gap-2">
                                    <i :class="'fa-solid fa-' + info.icon + ' text-slate-400 w-4'"></i>
                                    <span x-text="info.key"></span>
                                </span>
                                <span class="text-sm font-semibold text-slate-800" x-text="info.value"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Database Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mt-4">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900">Manajemen Data</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Bersihkan atau ekspor data dari database</p>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between p-4 bg-amber-50 border border-amber-200 rounded-xl">
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Bersihkan Data Lama (7 hari)</p>
                                <p class="text-xs text-amber-600 mt-0.5">Hapus monitoring yang lebih dari 7 hari</p>
                            </div>
                            <button @click="cleanupData(7)" :disabled="saving"
                                    class="px-4 py-2 bg-amber-100 hover:bg-amber-200 text-amber-700 border border-amber-300 rounded-xl text-sm font-medium transition-colors disabled:opacity-50">
                                Bersihkan
                            </button>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-red-50 border border-red-200 rounded-xl">
                            <div>
                                <p class="text-sm font-semibold text-red-800">Bersihkan Semua Data</p>
                                <p class="text-xs text-red-600 mt-0.5">Hapus seluruh riwayat monitoring</p>
                            </div>
                            <button @click="cleanupData(0)" :disabled="saving"
                                    class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 border border-red-300 rounded-xl text-sm font-medium transition-colors disabled:opacity-50">
                                Hapus Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- About Tab -->
            <div x-show="activeTab === 'about'" x-transition>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900">Tentang Aplikasi</h3>
                    </div>
                    <div class="p-8 text-center">
                        <div class="w-20 h-20 bg-linear-to-br from-green-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fa-solid fa-leaf text-3xl text-white"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900">Smart Garden IoT</h2>
                        <p class="text-slate-500 mt-1">Sistem Monitoring & Kontrol Tanaman Otomatis</p>
                        <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-xl">
                            <i class="fa-solid fa-code-branch text-blue-600"></i>
                            <span class="text-sm font-medium text-blue-700">Versi 2.0.0</span>
                        </div>
                        <div class="mt-8 grid grid-cols-3 gap-4 text-sm">
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <i class="fa-brands fa-laravel text-2xl text-red-500 mb-2 block"></i>
                                <p class="font-semibold text-slate-700">Laravel 12</p>
                                <p class="text-xs text-slate-400">Backend Framework</p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <i class="fa-brands fa-raspberry-pi text-2xl text-red-600 mb-2 block"></i>
                                <p class="font-semibold text-slate-700">Pico W</p>
                                <p class="text-xs text-slate-400">IoT Hardware</p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl">
                                <i class="fa-solid fa-database text-2xl text-blue-500 mb-2 block"></i>
                                <p class="font-semibold text-slate-700">MySQL</p>
                                <p class="text-xs text-slate-400">Database</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function settingsPage() {
    return {
        activeTab: 'device',
        devices: [],
        selectedDeviceId: '',
        deviceSettings: null,
        saving: false,
        deviceForm: {},

        tabs: [
            { id: 'device', label: 'Perangkat', icon: 'microchip' },
            { id: 'system', label: 'Sistem', icon: 'server' },
            { id: 'about', label: 'Tentang', icon: 'circle-info' },
        ],

        operationModes: [
            { value: 1, label: 'Basic', desc: 'Threshold otomatis', icon: 'seedling', color: 'text-green-600', bg: 'bg-green-50' },
            { value: 2, label: 'Fuzzy AI', desc: 'Logika cerdas', icon: 'brain', color: 'text-purple-600', bg: 'bg-purple-50' },
            { value: 3, label: 'Terjadwal', desc: 'Pagi & sore', icon: 'clock', color: 'text-amber-600', bg: 'bg-amber-50' },
            { value: 4, label: 'Manual', desc: 'Kontrol penuh', icon: 'hand', color: 'text-blue-600', bg: 'bg-blue-50' },
        ],

        systemInfo: [
            { key: 'Aplikasi', value: 'Smart Garden IoT v2.0', icon: 'leaf' },
            { key: 'Framework', value: 'Laravel 12.x', icon: 'code' },
            { key: 'PHP', value: '8.2', icon: 'server' },
            { key: 'Database', value: 'MySQL — smart_garden', icon: 'database' },
            { key: 'Cache', value: 'File', icon: 'bolt' },
            { key: 'Environment', value: 'local', icon: 'tag' },
        ],

        async init() {
            await this.loadDevices();
        },

        async loadDevices() {
            try {
                const res = await fetch('/api/devices');
                const json = await res.json();
                if (json.success) this.devices = json.data;
                else window.showToast('Gagal memuat daftar perangkat', 'error');
            } catch(e) {
                window.showToast('Gagal terhubung ke server: ' + (e.message || 'Error'), 'error');
            }
        },

        async loadDeviceSettings() {
            if (!this.selectedDeviceId) return;
            try {
                const res = await fetch(`/api/devices/${this.selectedDeviceId}`);
                const json = await res.json();
                if (json.success) {
                    this.deviceSettings = json.data;
                    this.deviceForm = {
                        mode: json.data.mode,
                        batas_siram: json.data.batas_siram,
                        batas_stop: json.data.batas_stop,
                        jam_pagi: json.data.jam_pagi?.slice(0, 5) || '07:00',
                        jam_sore: json.data.jam_sore?.slice(0, 5) || '17:00',
                        durasi_siram: json.data.durasi_siram,
                        sensor_min: json.data.sensor_min,
                        sensor_max: json.data.sensor_max,
                    };
                } else {
                    window.showToast('Gagal memuat pengaturan perangkat', 'error');
                }
            } catch(e) {
                window.showToast('Gagal memuat pengaturan: ' + (e.message || 'Error'), 'error');
            }
        },

        async saveDeviceSettings() {
            if (!this.selectedDeviceId) {
                window.showToast('Pilih perangkat terlebih dahulu', 'warning');
                return;
            }
            // Validate threshold: batas_stop must be > batas_siram
            if ([1, 4].includes(parseInt(this.deviceForm.mode))) {
                if (parseInt(this.deviceForm.batas_stop) <= parseInt(this.deviceForm.batas_siram)) {
                    window.showToast('Batas Stop harus lebih besar dari Batas Siram', 'error');
                    return;
                }
            }
            this.saving = true;
            try {
                // Single unified request via POST /api/devices/{id}/mode
                // This endpoint handles mode + all thresholds + schedule + calibration atomically
                const modePayload = {
                    mode: parseInt(this.deviceForm.mode),
                    batas_siram: parseInt(this.deviceForm.batas_siram),
                    batas_stop: parseInt(this.deviceForm.batas_stop),
                    jam_pagi: this.deviceForm.jam_pagi,
                    jam_sore: this.deviceForm.jam_sore,
                    durasi_siram: parseInt(this.deviceForm.durasi_siram),
                    sensor_min: parseInt(this.deviceForm.sensor_min),
                    sensor_max: parseInt(this.deviceForm.sensor_max),
                };
                const res = await fetch(`/api/devices/${this.selectedDeviceId}/mode`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(modePayload)
                });
                const json = await res.json();
                if (json.success) {
                    window.showToast('Pengaturan berhasil disimpan', 'success');
                    await this.loadDeviceSettings();
                } else {
                    window.showToast(json.message || 'Gagal menyimpan pengaturan', 'error');
                }
            } catch(e) {
                window.showToast(e.message || 'Terjadi kesalahan saat menyimpan', 'error');
            } finally {
                this.saving = false;
            }
        },

        async resetCalibration() {
            if (!this.selectedDeviceId) return;
            this.saving = true;
            try {
                const res = await fetch(`/api/devices/${this.selectedDeviceId}/calibrate/reset`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const json = await res.json();
                if (json.success) {
                    window.showToast('Kalibrasi direset. Auto-kalibrasi akan dimulai.', 'success');
                    await this.loadDeviceSettings();
                }
            } catch(e) { window.showToast('Terjadi kesalahan', 'error'); }
            finally { this.saving = false; }
        },

        async applyPreset(preset) {
            if (!this.selectedDeviceId) return;
            if (!confirm(`Terapkan preset ${preset}? Pengaturan saat ini akan diganti.`)) return;
            this.saving = true;
            try {
                const res = await fetch(`/api/devices/${this.selectedDeviceId}/preset`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ preset })
                });
                const json = await res.json();
                if (json.success) {
                    window.showToast(`Preset ${preset} berhasil diterapkan`, 'success');
                    await this.loadDeviceSettings();
                }
            } catch(e) { window.showToast('Terjadi kesalahan', 'error'); }
            finally { this.saving = false; }
        },

        async cleanupData(days) {
            const msg = days === 0
                ? 'PERHATIAN! Ini akan menghapus SELURUH data monitoring secara permanen.\n\nKetik "HAPUS" untuk konfirmasi:'
                : `Hapus data monitoring lebih dari ${days} hari? Tindakan ini tidak dapat dibatalkan.`;
            
            if (days === 0) {
                const confirm1 = prompt(msg);
                if (confirm1 !== 'HAPUS') {
                    if (confirm1 !== null) window.showToast('Hapus dibatalkan — konfirmasi tidak sesuai', 'warning');
                    return;
                }
            } else {
                if (!confirm(msg)) return;
            }
            
            this.saving = true;
            try {
                const url = days === 0
                    ? `/api/monitoring/cleanup?days=0`
                    : `/api/monitoring/cleanup?days=${days}`;
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const json = await res.json();
                if (json.success) window.showToast(json.message, 'success');
                else window.showToast(json.message || 'Gagal menghapus data', 'error');
            } catch(e) { window.showToast('Terjadi kesalahan', 'error'); }
            finally { this.saving = false; }
        },
    };
}
</script>
@endpush
