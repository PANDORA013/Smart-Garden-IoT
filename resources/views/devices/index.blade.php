@extends('layouts.app')

@section('title', 'Perangkat')

@section('page-title', 'Manajemen Perangkat')

@section('breadcrumbs')
    <a href="{{ route('dashboard.index') }}" class="text-slate-400 hover:text-slate-600">Dashboard</a>
    <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
    <span class="text-slate-700 font-medium">Perangkat</span>
@endsection

@section('content')
<div x-data="devicesPage()" x-init="init()">

    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <p class="text-slate-500 text-sm mt-1">Kelola semua perangkat IoT yang terhubung ke sistem.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Online Badge -->
            <div class="flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-200 rounded-xl">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-green-700 text-sm font-medium" x-text="onlineCount + ' Online'">0 Online</span>
            </div>
            <!-- Refresh Button -->
            <button @click="loadDevices()" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                <i class="fa-solid fa-rotate" :class="loading && 'animate-spin'"></i>
                Refresh
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                    <i class="fa-solid fa-microchip text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Total Perangkat</p>
                    <p class="text-2xl font-bold text-slate-900" x-text="devices.length">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-green-50 text-green-600 rounded-xl">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Online</p>
                    <p class="text-2xl font-bold text-green-600" x-text="onlineCount">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-red-50 text-red-600 rounded-xl">
                    <i class="fa-solid fa-circle-xmark text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Offline</p>
                    <p class="text-2xl font-bold text-red-500" x-text="devices.length - onlineCount">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl">
                    <i class="fa-solid fa-water text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Pompa Aktif</p>
                    <p class="text-2xl font-bold text-amber-600" x-text="pumpActiveCount">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">

        <!-- Loading skeleton -->
        <template x-if="loading">
            <template x-for="i in 3" :key="i">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 animate-pulse">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-slate-200 rounded-xl"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-slate-200 rounded w-3/4 mb-2"></div>
                            <div class="h-3 bg-slate-200 rounded w-1/2"></div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="h-3 bg-slate-200 rounded"></div>
                        <div class="h-3 bg-slate-200 rounded w-5/6"></div>
                    </div>
                </div>
            </template>
        </template>

        <!-- Device cards -->
        <template x-if="!loading">
            <template x-for="device in devices" :key="device.id">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <!-- Card Header -->
                    <div class="p-5 pb-4 border-b border-slate-100">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div :class="device.status === 'online' ? 'bg-green-50 text-green-600' : 'bg-slate-100 text-slate-400'"
                                     class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                                    <i class="fa-solid fa-microchip text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-slate-900 truncate" x-text="device.device_name || device.device_id"></h3>
                                    <p class="text-xs text-slate-400 font-mono truncate" x-text="device.device_id"></p>
                                </div>
                            </div>
                            <!-- Status badge -->
                            <div>
                                <span :class="{
                                    'bg-green-100 text-green-700 border-green-200': device.status === 'online',
                                    'bg-yellow-100 text-yellow-700 border-yellow-200': device.status === 'idle',
                                    'bg-red-100 text-red-700 border-red-200': device.status === 'offline',
                                    'bg-slate-100 text-slate-500 border-slate-200': device.status === 'never_connected',
                                }" class="px-2.5 py-1 text-xs font-bold rounded-full border">
                                    <span x-text="statusLabel(device.status)"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 space-y-4">
                        <!-- Sensor readings row -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="text-center p-3 bg-blue-50 rounded-xl">
                                <p class="text-lg font-bold text-blue-700" x-text="device.last_temperature != null ? device.last_temperature + '°' : '--'">--</p>
                                <p class="text-xs text-blue-500 mt-0.5">Suhu</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-xl">
                                <p class="text-lg font-bold text-green-700" x-text="device.last_soil != null ? device.last_soil + '%' : '--'">--</p>
                                <p class="text-xs text-green-500 mt-0.5">Tanah</p>
                            </div>
                            <div class="text-center p-3 rounded-xl" :class="device.relay_status ? 'bg-amber-50' : 'bg-slate-50'">
                                <p class="text-lg font-bold" :class="device.relay_status ? 'text-amber-700' : 'text-slate-400'" x-text="device.relay_status ? 'ON' : 'OFF'">OFF</p>
                                <p class="text-xs mt-0.5" :class="device.relay_status ? 'text-amber-500' : 'text-slate-400'">Pompa</p>
                            </div>
                        </div>

                        <!-- Device details -->
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-leaf text-green-500 w-4"></i>Tanaman
                                </span>
                                <span class="font-medium text-slate-700 capitalize" x-text="device.plant_type || 'Umum'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-robot text-purple-500 w-4"></i>Mode
                                </span>
                                <span class="font-medium text-slate-700" x-text="modeName(device.mode)"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-wifi text-blue-400 w-4"></i>IP
                                </span>
                                <span class="font-mono text-xs text-slate-600" x-text="device.ip_address || 'N/A'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-clock text-slate-400 w-4"></i>Terakhir
                                </span>
                                <span class="text-xs text-slate-500" x-text="formatTime(device.last_seen)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-5 pb-5 flex gap-2">
                        <button @click="openEditModal(device)" 
                                class="flex-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-colors flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                        <button @click="openModeModal(device)"
                                class="flex-1 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-sm font-medium transition-colors flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-sliders"></i> Mode
                        </button>
                        <button @click="confirmDelete(device)"
                                class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-sm font-medium transition-colors">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </template>
        </template>

        <!-- Empty state -->
        <template x-if="!loading && devices.length === 0">
            <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fa-solid fa-microchip text-4xl text-slate-300"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700">Belum Ada Perangkat</h3>
                <p class="text-slate-400 text-sm mt-1">Perangkat akan muncul otomatis saat terhubung.</p>
            </div>
        </template>
    </div>

    <!-- Edit Device Modal -->
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showEditModal = false">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md" x-show="showEditModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Edit Perangkat</h3>
                <button @click="showEditModal = false" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fa-solid fa-xmark text-slate-500"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="edit-device-name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Perangkat</label>
                    <input type="text" id="edit-device-name" name="device_name" x-model="editForm.device_name" 
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label for="edit-plant-type" class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Tanaman</label>
                    <select id="edit-plant-type" name="plant_type" x-model="editForm.plant_type" 
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="cabai">Cabai</option>
                        <option value="tomat">Tomat</option>
                        <option value="sayur">Sayuran</option>
                        <option value="buah">Buah-buahan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="edit-batas-siram" class="block text-sm font-semibold text-slate-700 mb-1.5">Batas Siram (%)</label>
                        <input type="number" id="edit-batas-siram" name="batas_siram" x-model="editForm.batas_siram" min="0" max="100"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="edit-batas-stop" class="block text-sm font-semibold text-slate-700 mb-1.5">Batas Stop (%)</label>
                        <input type="number" id="edit-batas-stop" name="batas_stop" x-model="editForm.batas_stop" min="0" max="100"
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label for="edit-notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
                    <textarea id="edit-notes" name="notes" x-model="editForm.notes" rows="2"
                              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                              placeholder="Catatan opsional..."></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button @click="showEditModal = false" 
                        class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                    Batal
                </button>
                <button @click="saveDevice()" :disabled="saving"
                        class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
                    <span x-show="!saving">Simpan</span>
                    <span x-show="saving"><i class="fa-solid fa-spinner animate-spin mr-1"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mode Modal -->
    <div x-show="showModeModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showModeModal = false">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModeModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm" x-show="showModeModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Ubah Mode Operasi</h3>
                <button @click="showModeModal = false" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fa-solid fa-xmark text-slate-500"></i>
                </button>
            </div>
            <div class="p-6 space-y-3">
                <template x-for="m in modes" :key="m.value">
                    <button @click="modeForm.mode = m.value"
                            :class="modeForm.mode == m.value ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 hover:border-slate-300 text-slate-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 border-2 rounded-xl text-sm font-medium transition-all text-left">
                        <i :class="'fa-solid fa-' + m.icon + ' w-5 text-center'"></i>
                        <div>
                            <p class="font-semibold" x-text="m.label"></p>
                            <p class="text-xs font-normal opacity-70" x-text="m.desc"></p>
                        </div>
                        <i x-show="modeForm.mode == m.value" class="fa-solid fa-check ml-auto text-blue-600"></i>
                    </button>
                </template>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button @click="showModeModal = false"
                        class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                    Batal
                </button>
                <button @click="saveMode()" :disabled="saving"
                        class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
                    <span x-show="!saving">Terapkan</span>
                    <span x-show="saving"><i class="fa-solid fa-spinner animate-spin mr-1"></i> Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showDeleteModal = false">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center" x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash text-3xl text-red-500"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-2">Hapus Perangkat?</h3>
            <p class="text-slate-500 text-sm mb-6">Perangkat <strong x-text="selectedDevice?.device_name || selectedDevice?.device_id"></strong> dan semua datanya akan dihapus permanen.</p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-colors">
                    Batal
                </button>
                <button @click="deleteDevice()" :disabled="saving"
                        class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
                    <span x-show="!saving">Hapus</span>
                    <span x-show="saving"><i class="fa-solid fa-spinner animate-spin mr-1"></i></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function devicesPage() {
    return {
        devices: [],
        loading: true,
        saving: false,
        showEditModal: false,
        showModeModal: false,
        showDeleteModal: false,
        selectedDevice: null,
        editForm: {},
        modeForm: { mode: 1 },
        refreshInterval: null,
        modes: [
            { value: 1, label: 'Mode Pemula (Basic)', icon: 'seedling', desc: 'Otomatis berdasarkan threshold kelembaban' },
            { value: 2, label: 'Mode AI (Fuzzy Logic)', icon: 'brain', desc: 'Cerdas dengan logika fuzzy adaptif' },
            { value: 3, label: 'Mode Terjadwal', icon: 'clock', desc: 'Siram sesuai jadwal pagi dan sore' },
            { value: 4, label: 'Mode Manual', icon: 'hand', desc: 'Kontrol manual penuh oleh pengguna' },
        ],

        get onlineCount() {
            return this.devices.filter(d => d.status === 'online').length;
        },
        get pumpActiveCount() {
            return this.devices.filter(d => d.relay_status).length;
        },

        async init() {
            await this.loadDevices();
            this.refreshInterval = setInterval(() => this.loadDevices(), 5000);
        },

        async loadDevices() {
            try {
                const res = await fetch('/api/devices');
                const json = await res.json();
                if (json.success) {
                    this.devices = json.data.map(d => ({
                        ...d,
                        last_temperature: d.last_temperature ?? null,
                        last_soil: d.last_soil ?? null,
                    }));
                }
            } catch(e) {
                console.error('Failed to load devices:', e);
            } finally {
                this.loading = false;
            }
        },

        statusLabel(status) {
            const map = { online: 'Online', idle: 'Idle', offline: 'Offline', never_connected: 'Baru' };
            return map[status] || status;
        },

        modeName(mode) {
            const map = { 1: 'Basic', 2: 'Fuzzy AI', 3: 'Terjadwal', 4: 'Manual' };
            return map[mode] || 'Unknown';
        },

        formatTime(ts) {
            if (!ts) return 'Belum pernah';
            const d = new Date(ts);
            const diff = Math.floor((Date.now() - d) / 1000);
            if (diff < 60) return diff + ' dtk lalu';
            if (diff < 3600) return Math.floor(diff/60) + ' mnt lalu';
            if (diff < 86400) return Math.floor(diff/3600) + ' jam lalu';
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        },

        openEditModal(device) {
            this.selectedDevice = device;
            this.editForm = {
                device_name: device.device_name,
                plant_type: device.plant_type || 'cabai',
                batas_siram: device.batas_siram,
                batas_stop: device.batas_stop,
                notes: device.notes || '',
            };
            this.showEditModal = true;
        },

        openModeModal(device) {
            this.selectedDevice = device;
            this.modeForm = { mode: device.mode };
            this.showModeModal = true;
        },

        confirmDelete(device) {
            this.selectedDevice = device;
            this.showDeleteModal = true;
        },

        async saveDevice() {
            if (!this.selectedDevice) return;
            this.saving = true;
            try {
                const res = await fetch(`/api/devices/${this.selectedDevice.device_id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(this.editForm)
                });
                const json = await res.json();
                if (json.success) {
                    window.showToast('Perangkat berhasil diupdate', 'success');
                    this.showEditModal = false;
                    await this.loadDevices();
                } else {
                    window.showToast('Gagal mengupdate perangkat', 'error');
                }
            } catch(e) {
                window.showToast('Terjadi kesalahan', 'error');
            } finally {
                this.saving = false;
            }
        },

        async saveMode() {
            if (!this.selectedDevice) return;
            this.saving = true;
            try {
                const res = await fetch(`/api/devices/${this.selectedDevice.device_id}/mode`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(this.modeForm)
                });
                const json = await res.json();
                if (json.success) {
                    window.showToast('Mode berhasil diubah', 'success');
                    this.showModeModal = false;
                    await this.loadDevices();
                } else {
                    window.showToast(json.message || 'Gagal mengubah mode', 'error');
                }
            } catch(e) {
                window.showToast('Terjadi kesalahan', 'error');
            } finally {
                this.saving = false;
            }
        },

        async deleteDevice() {
            if (!this.selectedDevice) return;
            this.saving = true;
            try {
                const res = await fetch(`/api/devices/${this.selectedDevice.device_id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const json = await res.json();
                if (json.success) {
                    window.showToast('Perangkat berhasil dihapus', 'success');
                    this.showDeleteModal = false;
                    await this.loadDevices();
                } else {
                    window.showToast('Gagal menghapus perangkat', 'error');
                }
            } catch(e) {
                window.showToast('Terjadi kesalahan', 'error');
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
