@props(['state', 'label'])
<span @class(['inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs font-semibold',
    'border-warning/30 bg-warning/10 text-warningDark' => $state === 'pending',
    'border-danger/20 bg-danger/10 text-danger' => $state === 'ongoing',
    'border-secondaryLight bg-primary/10 text-primaryDark' => $state === 'scheduled',
    'border-slate-200 bg-background text-slate-600' => $state === 'elapsed'])><span aria-hidden="true" class="h-1.5 w-1.5 shrink-0 rounded-full bg-current"></span>{{ $label }}</span>
