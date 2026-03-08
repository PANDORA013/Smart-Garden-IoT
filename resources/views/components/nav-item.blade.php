@props(['route', 'icon', 'label', 'active' => false, 'badge' => null])

<a 
    href="{{ Route::has($route) ? route($route) : '#' }}" 
    @class([
        'flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all group',
        'bg-blue-600 text-white shadow-lg shadow-blue-600/30' => $active,
        'text-slate-400 hover:bg-slate-800 hover:text-white' => !$active
    ])
>
    <i class="fa-solid fa-{{ $icon }} w-5 text-center transition-transform group-hover:scale-110"></i>
    <span class="flex-1">{{ $label }}</span>
    @if($badge)
        <span class="px-2 py-1 text-xs font-bold bg-red-500 text-white rounded-full">
            {{ $badge }}
        </span>
    @endif
</a>
