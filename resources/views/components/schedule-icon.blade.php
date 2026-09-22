@props(['name' => 'calendar'])
<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('calendar')<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M16 3v4M8 3v4M3 11h18M8 15h2M14 15h2M8 18h2"/>@break
@case('arrow')<path d="m9 5 7 7-7 7"/>@break
@case('grid')<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>@break
@case('room')<path d="M3 21h18M6 21V4l12-1v18M10 12h1M18 6h3v15"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('logout')<path d="M9 4H4v16h5M9 12h12m-4-4 4 4-4 4"/>@break
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@endswitch
</svg>
