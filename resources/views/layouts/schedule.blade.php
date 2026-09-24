<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Jadwal Ruangan') · Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-sans text-text antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:bg-white focus:p-4">Langsung ke konten</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        <aside class="flex flex-col bg-primaryDark text-white lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
            <div class="flex items-center justify-between px-7 py-8">
                <div><div class="text-3xl font-bold italic tracking-tight">pelindo<span class="text-secondary">∿</span></div><div class="mt-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-secondaryLight">Multi Terminal</div></div>
                <button type="button" data-menu-toggle aria-expanded="false" aria-controls="schedule-navigation" class="rounded-lg p-2 hover:bg-white/10 lg:hidden" aria-label="Buka navigasi"><x-schedule-icon name="menu" /></button>
            </div>
            <div id="schedule-navigation" class="hidden flex-1 flex-col px-4 pb-5 lg:flex">
                <div class="mx-3 mb-8 border-t border-white/15 pt-5"><p class="text-sm font-semibold">Meeting Room</p><p class="mt-1 text-xs text-secondaryLight">Ruang untuk berkolaborasi.</p></div>
                <p class="px-3 pb-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-secondaryLight">Workspace</p>
                <nav aria-label="Navigasi utama" class="space-y-1">
                    <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('dashboard', 'pic.dashboard', 'admin.dashboard')]) @if(request()->routeIs('dashboard', 'pic.dashboard', 'admin.dashboard')) aria-current="page" @endif href="{{ route(auth()->user()->dashboardRouteName()) }}"><x-schedule-icon name="grid" />Dashboard</a>
                    @can('access-pic')
                        @cannot('access-admin')
                            <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('pic.approvals.index', 'pic.approvals.show')]) @if(request()->routeIs('pic.approvals.index', 'pic.approvals.show')) aria-current="page" @endif href="{{ route('pic.approvals.index') }}"><x-schedule-icon name="clock" />Permintaan Approval</a>
                            <a class="schedule-nav" href="{{ route('pic.rooms.index') }}"><x-schedule-icon name="room" />Ruangan Saya</a>
                            <a class="schedule-nav" href="{{ route('pic.approvals.history') }}"><x-schedule-icon name="calendar" />Riwayat Approval</a>
                        @endcannot
                    @endcan
                    <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('*.schedule.index', 'schedule.index')]) @if(request()->routeIs('*.schedule.index', 'schedule.index')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.schedule.index' : 'schedule.index') }}">
                        <x-schedule-icon />Jadwal Ruangan
                        @if(request()->routeIs('*.schedule.index', 'schedule.index'))
                            <span class="ml-auto h-1.5 w-1.5 rounded-full bg-secondary"></span>
                        @endif
                    </a>
                    <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('admin.rooms.*', 'rooms.index')]) @if(request()->routeIs('admin.rooms.*', 'rooms.index')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.rooms.index' : 'rooms.index') }}"><x-schedule-icon name="room" />Daftar Ruangan</a>
                    @can('access-employee')
                        <a class="schedule-nav" href="{{ route('my-bookings.index') }}"><x-schedule-icon name="clock" />My Booking</a>
                    @endcan
                    @can('access-admin')
                        <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('admin.bookings.*')]) @if(request()->routeIs('admin.bookings.*')) aria-current="page" @endif href="{{ route('admin.bookings.index') }}"><x-schedule-icon name="clock" />Semua Booking</a>
                    @endcan
                    @can('access-admin')
                        <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('pic.approvals.*')]) @if(request()->routeIs('pic.approvals.*')) aria-current="page" @endif href="{{ route('pic.approvals.index') }}"><x-schedule-icon name="grid" />Approval Ruangan</a>
                    @endcan
                </nav>
                @can('access-admin')
                    <p class="px-3 pb-3 pt-8 text-[10px] font-semibold uppercase tracking-[0.2em] text-secondaryLight">Administrasi</p>
                    <nav aria-label="Master data" class="space-y-1">
                        <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('admin.users.*')]) @if(request()->routeIs('admin.users.*')) aria-current="page" @endif href="{{ route('admin.users.index') }}"><x-schedule-icon name="grid" />Kelola User</a>
                        <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('admin.organizational-units.*')]) @if(request()->routeIs('admin.organizational-units.*')) aria-current="page" @endif href="{{ route('admin.organizational-units.index') }}"><x-schedule-icon name="grid" />Unit Organisasi</a>
                        <a @class(['schedule-nav', 'bg-white/10 text-white ring-1 ring-white/10' => request()->routeIs('admin.facilities.*')]) @if(request()->routeIs('admin.facilities.*')) aria-current="page" @endif href="{{ route('admin.facilities.index') }}"><x-schedule-icon name="grid" />Fasilitas</a>
                    </nav>
                @endcan
                <div class="mt-auto pt-10">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4"><p class="text-xs font-semibold text-secondaryLight">SATU RUANG, BANYAK IDE.</p><p class="mt-2 text-xs leading-5 text-white/70">Mulai kolaborasi yang baik dengan perencanaan yang tepat.</p></div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-4">@csrf<button class="schedule-nav w-full" type="submit"><x-schedule-icon name="logout" />Keluar</button></form>
                </div>
            </div>
        </aside>
        <div class="min-w-0">
            <header class="flex min-h-[80px] items-center justify-between gap-4 border-b border-slate-200/70 bg-surface px-5 lg:px-9">
                <div class="flex flex-wrap items-center gap-3 text-sm"><span class="text-slate-500">Workspace</span><span class="text-muted">/</span><span class="font-medium">@yield('breadcrumb', 'Jadwal Ruangan')</span></div>
                <div class="flex items-center gap-3"><div class="hidden text-right sm:block"><p class="text-sm font-semibold">{{ auth()->user()->name }}</p><p class="mt-0.5 text-xs capitalize text-slate-500">{{ str_replace('_', ' ', auth()->user()->role) }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-full bg-secondaryLight/40 text-sm font-bold text-primaryDark">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span></div>
            </header>
            <main id="content" @class(['mx-auto p-5 lg:p-9', 'max-w-[1800px]' => request()->routeIs('dashboard', '*.dashboard'), 'max-w-[1600px]' => ! request()->routeIs('dashboard', '*.dashboard')])>@yield('content')</main>
            <footer class="flex flex-wrap justify-between gap-2 px-5 pb-6 text-xs text-slate-500 lg:px-9"><span>PT Pelindo Multi Terminal</span><span>Meeting Room · Internal Workspace</span></footer>
        </div>
    </div>
</body>
</html>
