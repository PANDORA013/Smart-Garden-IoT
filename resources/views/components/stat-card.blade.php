@props([
    'title',
    'value',
    'unit' => '',
    'icon',
    'color' => 'blue',
    'subtitle' => null,
    'trend' => null,
    'loading' => false
])

@php
$colorClasses = [
    'blue' => 'bg-blue-50 text-blue-600',
    'green' => 'bg-green-50 text-green-600',
    'purple' => 'bg-purple-50 text-purple-600',
    'amber' => 'bg-amber-50 text-amber-600',
    'red' => 'bg-red-50 text-red-600',
    'cyan' => 'bg-cyan-50 text-cyan-600',
];

$iconColor = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group">
    <div class="flex justify-between items-start mb-4">
        <div class="p-3 {{ $iconColor }} rounded-xl group-hover:scale-110 transition-transform duration-300">
            <i class="fa-solid fa-{{ $icon }} text-xl"></i>
        </div>
        
        @if($trend)
            <div class="flex items-center gap-1 text-xs font-semibold {{ $trend > 0 ? 'text-green-600' : 'text-red-600' }}">
                <i class="fa-solid fa-{{ $trend > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                <span>{{ abs($trend) }}%</span>
            </div>
        @endif
    </div>
    
    <p class="text-slate-500 text-sm font-medium mb-2">{{ $title }}</p>
    
    @if($loading)
        <div class="flex items-center gap-2 h-10">
            <div class="spinner"></div>
            <span class="text-sm text-slate-400">Memuat...</span>
        </div>
    @else
        <h3 class="text-3xl font-bold text-slate-900 mb-1">
            {{ $value }}<span class="text-lg text-slate-500">{{ $unit }}</span>
        </h3>
    @endif
    
    @if($subtitle)
        <p class="text-xs text-slate-400 mt-2">{{ $subtitle }}</p>
    @endif
    
    {{ $slot }}
</div>
