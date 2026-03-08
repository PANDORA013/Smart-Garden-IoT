<aside 
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-linear-to-b from-slate-900 to-slate-800 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static flex flex-col shadow-2xl"
>
    <!-- Logo & Brand -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700/50">
        <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fa-solid fa-leaf text-white text-lg"></i>
        </div>
        <div>
            <h1 class="font-bold text-lg text-white tracking-tight">Smart Garden</h1>
            <p class="text-xs text-slate-400">IoT Monitoring</p>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
        <x-nav-item 
            route="dashboard.index" 
            icon="gauge-high" 
            label="Dashboard"
            :active="request()->routeIs('dashboard.*')"
        />
        
        <x-nav-item 
            route="devices.index" 
            icon="microchip" 
            label="Perangkat"
            :active="request()->routeIs('devices.*')"
        />
        
        <x-nav-item 
            route="monitoring.index" 
            icon="chart-line" 
            label="Monitoring"
            :active="request()->routeIs('monitoring.*')"
        />
        
        <x-nav-item 
            route="logs.index" 
            icon="list-ul" 
            label="Riwayat Log"
            :active="request()->routeIs('logs.*')"
        />
        
        <div class="border-t border-slate-700/50 my-3"></div>
        
        <x-nav-item 
            route="settings.index" 
            icon="sliders" 
            label="Pengaturan"
            :active="request()->routeIs('settings.*')"
        />
        
        <x-nav-item 
            route="help.index" 
            icon="circle-question" 
            label="Bantuan"
            :active="request()->routeIs('help.*')"
        />
    </nav>
    
    <!-- User Profile Section -->
    <div class="px-3 py-4 border-t border-slate-700/50">
        <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-slate-800/50 hover:bg-slate-800 transition-colors cursor-pointer group">
            <div class="relative">
                <div class="w-10 h-10 rounded-full bg-linear-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
                    {{ substr(auth()->user()->name ?? 'Admin', 0, 1) }}
                </div>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-slate-900 rounded-full"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? 'admin@smartgarden.io' }}</p>
            </div>
            <i class="fa-solid fa-ellipsis-vertical text-slate-400 group-hover:text-white transition-colors"></i>
        </div>
    </div>
</aside>
