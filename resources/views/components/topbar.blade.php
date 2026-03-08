<div
    x-data="topbarNotifications()"
    x-init="init()"
    @alpine:destroyed="destroy()"
>
<header class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-30">
    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
        <!-- Mobile Menu Button -->
        <button 
            @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
        >
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
        
        <!-- Page Title & Breadcrumbs -->
        <div class="flex-1 min-w-0 ml-4 lg:ml-0">
            <h2 class="text-lg font-bold text-slate-900 truncate">
                @yield('page-title', 'Dashboard')
            </h2>
            <nav class="hidden sm:flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                @yield('breadcrumbs')
            </nav>
        </div>
        
        <!-- Right Actions -->
        <div class="flex items-center gap-3">
            <!-- Connection Status — live check via /api/monitoring/latest -->
            <div 
                :class="isOnline ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
                class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full border transition-colors"
            >
                <span class="relative flex h-2.5 w-2.5">
                    <span 
                        x-show="isOnline"
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"
                    ></span>
                    <span 
                        :class="isOnline ? 'bg-green-500' : 'bg-red-500'"
                        class="relative inline-flex rounded-full h-2.5 w-2.5"
                    ></span>
                </span>
                <span 
                    :class="isOnline ? 'text-green-700' : 'text-red-700'"
                    class="text-xs font-semibold"
                    x-text="isOnline ? 'Online' : 'Offline'"
                ></span>
            </div>
            
            <!-- Notification Bell -->
            <div class="relative">
                <button 
                    class="relative p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                    @click="notifOpen = !notifOpen"
                >
                    <i class="fa-solid fa-bell text-lg"></i>
                    <!-- Unread badge — only shown when there are unread notifications -->
                    <span 
                        x-show="unreadCount > 0"
                        x-cloak
                        class="absolute top-1 right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white flex items-center justify-center"
                    >
                        <span class="text-white text-[8px] font-bold" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                    </span>
                </button>

                <!-- Notification Panel -->
                <div 
                    x-show="notifOpen"
                    x-cloak
                    @click.outside="notifOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                    class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden origin-top-right"
                >
                    <!-- Panel Header -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm">Notifikasi</h3>
                        <button 
                            @click="markAllRead()"
                            x-show="unreadCount > 0"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                        >Tandai semua dibaca</button>
                    </div>

                    <!-- Notification List -->
                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                        <template x-if="notifications.length === 0">
                            <div class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-bell-slash text-3xl mb-2 block opacity-30"></i>
                                <p class="text-sm">Tidak ada notifikasi</p>
                            </div>
                        </template>
                        <template x-for="notif in notifications" :key="notif.id">
                            <div 
                                @click="readNotif(notif.id)"
                                :class="notif.read ? 'bg-white' : 'bg-blue-50'"
                                class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors"
                            >
                                <div 
                                    :class="{
                                        'bg-red-100 text-red-600':   notif.type === 'error',
                                        'bg-yellow-100 text-yellow-600': notif.type === 'warning',
                                        'bg-green-100 text-green-600': notif.type === 'success',
                                        'bg-blue-100 text-blue-600':  notif.type === 'info',
                                    }"
                                    class="mt-0.5 shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                                >
                                    <i 
                                        :class="{
                                            'fa-times-circle':          notif.type === 'error',
                                            'fa-exclamation-triangle':  notif.type === 'warning',
                                            'fa-check-circle':          notif.type === 'success',
                                            'fa-info-circle':           notif.type === 'info',
                                        }"
                                        class="fa-solid text-sm"
                                    ></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800" x-text="notif.title"></p>
                                    <p class="text-xs text-slate-500 mt-0.5 truncate" x-text="notif.message"></p>
                                    <p class="text-xs text-slate-400 mt-1" x-text="notif.time"></p>
                                </div>
                                <span x-show="!notif.read" class="mt-2 shrink-0 w-2 h-2 rounded-full bg-blue-500"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Panel Footer -->
                    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
                        <a href="{{ route('logs.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-list-ul"></i> Lihat semua log aktivitas
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Settings (link to settings page) -->
            <a 
                href="{{ route('settings.index') }}"
                class="hidden md:block p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                title="Pengaturan"
            >
                <i class="fa-solid fa-gear text-lg"></i>
            </a>
        </div>
    </div>
</header>
</div>

<script>
function topbarNotifications() {
    return {
        isOnline: true,
        notifOpen: false,
        notifications: [],
        _interval: null,
        _prevOnline: true,

        get unreadCount() {
            return this.notifications.filter(n => !n.read).length;
        },

        init() {
            this.checkStatus();
            this._interval = setInterval(() => this.checkStatus(), 10000);
        },

        destroy() {
            if (this._interval) { clearInterval(this._interval); this._interval = null; }
        },

        async checkStatus() {
            try {
                const res = await fetch('/api/monitoring/latest');
                const json = await res.json();
                const online = json.success && json.data?.is_online === true;
                
                // Trigger notification on state change
                if (online !== this._prevOnline) {
                    if (!online) {
                        this.addNotif('Perangkat Offline', 'Tidak ada data dari perangkat dalam 30 detik terakhir.', 'error');
                        window.showToast('⚠️ Perangkat IoT offline!', 'error', 5000);
                    } else {
                        this.addNotif('Perangkat Online', 'Koneksi perangkat IoT pulih kembali.', 'success');
                        window.showToast('✅ Perangkat IoT kembali online', 'success');
                    }
                    this._prevOnline = online;
                }

                // Check sensor alerts from latest data
                if (online && json.data) {
                    const soil = parseFloat(json.data.soil_moisture);
                    const temp = parseFloat(json.data.temperature);
                    if (!isNaN(soil) && soil < 15) {
                        this.addNotifThrottled('soil_dry', 'Tanah Sangat Kering', `Kelembaban tanah hanya ${soil.toFixed(0)}% — pompa perlu dinyalakan!`, 'warning');
                    }
                    if (!isNaN(temp) && temp > 38) {
                        this.addNotifThrottled('temp_high', 'Suhu Terlalu Tinggi', `Suhu udara mencapai ${temp.toFixed(1)}°C — periksa lingkungan tanaman.`, 'error');
                    }
                }

                this.isOnline = online;
            } catch(e) {
                // Network error = treat as offline
                if (this._prevOnline) {
                    this.isOnline = false;
                    this._prevOnline = false;
                    this.addNotif('Koneksi Terputus', 'Tidak dapat menjangkau server API.', 'error');
                }
            }
        },

        addNotif(title, message, type = 'info') {
            const id = Date.now() + Math.random();
            const now = new Date();
            const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            this.notifications.unshift({ id, title, message, type, time, read: false });
            // Keep max 20 notifications
            if (this.notifications.length > 20) this.notifications.pop();
        },

        // Throttle: only add once per key per 5 minutes
        _throttleMap: {},
        addNotifThrottled(key, title, message, type) {
            const now = Date.now();
            if (this._throttleMap[key] && (now - this._throttleMap[key]) < 5 * 60 * 1000) return;
            this._throttleMap[key] = now;
            this.addNotif(title, message, type);
        },

        readNotif(id) {
            const n = this.notifications.find(n => n.id === id);
            if (n) n.read = true;
        },

        markAllRead() {
            this.notifications.forEach(n => n.read = true);
        },
    };
}
</script>
