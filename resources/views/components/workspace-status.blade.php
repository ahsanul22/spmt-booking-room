@props(['active'])
<span @class(['inline-flex rounded-md px-2.5 py-1 text-xs font-semibold', 'bg-primary/10 text-primaryDark' => $active, 'bg-background text-slate-600' => ! $active])>{{ $active ? 'Aktif' : 'Nonaktif' }}</span>
