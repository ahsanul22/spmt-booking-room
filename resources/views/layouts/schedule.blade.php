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
    <div class="flex min-h-screen flex-col lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        <aside class="relative flex flex-col bg-[#0A234E] text-white lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
            <!-- Sidebar Port Graphic Background -->
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none">
                <img src="{{ asset('images/sidebar-port.jpg') }}" alt="" class="h-full w-full object-cover object-top opacity-85">
                <div class="absolute inset-0 bg-gradient-to-b from-[#0A234E]/25 via-transparent to-[#0A234E]/50"></div>
            </div>

            <div class="relative z-10 flex items-center justify-between px-7 py-8">
                <a href="{{ route(auth()->user()->dashboardRouteName()) }}" aria-label="Pelindo Multi Terminal, Dashboard" class="inline-block focus:outline-none">
                    <x-application-logo variant="dark" class="h-8 sm:h-9 w-auto max-w-[160px] sm:max-w-[175px] object-contain drop-shadow-sm" />
                </a>
                <button type="button" data-menu-toggle aria-expanded="false" aria-controls="schedule-navigation" class="rounded-lg p-3 hover:bg-white/10 focus-visible:outline-white lg:hidden" aria-label="Buka navigasi"><x-schedule-icon name="menu" /></button>
            </div>
            <div id="schedule-navigation" class="relative z-10 hidden flex-1 flex-col px-4 pb-5 lg:flex">
                <div class="mx-3 mb-8 border-t border-white/15 pt-5"><p class="text-base font-semibold tracking-wide text-white">Meeting Room</p><p class="mt-1 text-sm text-secondaryLight">Ruang untuk berkolaborasi.</p></div>
                <p class="px-3 pb-3 text-sm font-semibold uppercase tracking-[0.2em] text-secondaryLight">Workspace</p>
                <nav aria-label="Navigasi utama" class="space-y-1">
                    <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('dashboard', 'pic.dashboard', 'admin.dashboard')]) @if(request()->routeIs('dashboard', 'pic.dashboard', 'admin.dashboard')) aria-current="page" @endif href="{{ route(auth()->user()->dashboardRouteName()) }}"><x-schedule-icon name="grid" />Dashboard</a>
                    @can('access-pic')
                        @cannot('access-admin')
                            <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('pic.approvals.index', 'pic.approvals.show')]) @if(request()->routeIs('pic.approvals.index', 'pic.approvals.show')) aria-current="page" @endif href="{{ route('pic.approvals.index') }}"><x-schedule-icon name="clock" />Permintaan Approval</a>
                            <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('pic.rooms.*')]) @if(request()->routeIs('pic.rooms.*')) aria-current="page" @endif href="{{ route('pic.rooms.index') }}"><x-schedule-icon name="room" />Ruangan Saya</a>
                            <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('pic.approvals.history')]) @if(request()->routeIs('pic.approvals.history')) aria-current="page" @endif href="{{ route('pic.approvals.history') }}"><x-schedule-icon name="calendar" />Riwayat Approval</a>
                        @endcannot
                    @endcan
                    <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('*.schedule.index', 'schedule.index')]) @if(request()->routeIs('*.schedule.index', 'schedule.index')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.schedule.index' : 'schedule.index') }}">
                        <x-schedule-icon />Jadwal Ruangan
                        @if(request()->routeIs('*.schedule.index', 'schedule.index'))
                            <span class="ml-auto h-1.5 w-1.5 rounded-full bg-secondary"></span>
                        @endif
                    </a>
                    @if(auth()->user()->role !== \App\Models\User::ROLE_ROOM_PIC)
                    <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.rooms.*', 'rooms.index', 'my-bookings.create')]) @if(request()->routeIs('admin.rooms.*', 'rooms.index', 'my-bookings.create')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.rooms.index' : 'rooms.index') }}"><x-schedule-icon name="room" />{{ auth()->user()->can('access-admin') ? 'Daftar Ruangan' : 'Booking Ruangan' }}</a>
                    @can('access-employee')
                        <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('my-bookings.index', 'my-bookings.show')]) @if(request()->routeIs('my-bookings.index', 'my-bookings.show')) aria-current="page" @endif href="{{ route('my-bookings.index') }}"><x-schedule-icon name="clock" />My Booking</a>
                    @endcan
                    @endif
                    @can('access-admin')
                        <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.bookings.*')]) @if(request()->routeIs('admin.bookings.*')) aria-current="page" @endif href="{{ route('admin.bookings.index') }}"><x-schedule-icon name="clock" />Semua Booking</a>
                    @endcan
                    @can('access-admin')
                        <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('pic.approvals.*')]) @if(request()->routeIs('pic.approvals.*')) aria-current="page" @endif href="{{ route('pic.approvals.index') }}"><x-schedule-icon name="grid" />Approval Ruangan</a>
                    @endcan
                    @can('access-pic')
                        <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('*.reports.*')]) @if(request()->routeIs('*.reports.*')) aria-current="page" @endif href="{{ route(auth()->user()->can('access-admin') ? 'admin.reports.index' : 'pic.reports.index') }}"><x-schedule-icon name="calendar" />Laporan Bulanan</a>
                    @endcan
                </nav>
                @can('access-admin')
                    <p class="px-3 pb-3 pt-8 text-sm font-semibold uppercase tracking-[0.2em] text-secondaryLight">Administrasi</p>
                    <nav aria-label="Master data" class="space-y-1">
                        <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.users.*')]) @if(request()->routeIs('admin.users.*')) aria-current="page" @endif href="{{ route('admin.users.index') }}"><x-schedule-icon name="grid" />Kelola User</a>
                        <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.organizational-units.*')]) @if(request()->routeIs('admin.organizational-units.*')) aria-current="page" @endif href="{{ route('admin.organizational-units.index') }}"><x-schedule-icon name="grid" />Unit Organisasi</a>
                        <a @class(['schedule-nav', 'schedule-nav-active' => request()->routeIs('admin.facilities.*')]) @if(request()->routeIs('admin.facilities.*')) aria-current="page" @endif href="{{ route('admin.facilities.index') }}"><x-schedule-icon name="grid" />Fasilitas</a>
                    </nav>
                @endcan
                <div class="mt-auto pt-10">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur-md">
                        <p class="text-sm font-semibold tracking-wider text-secondaryLight uppercase">SATU RUANG, BANYAK IDE.</p>
                        <p class="mt-2 text-sm leading-5 text-white/75">Mulai kolaborasi yang baik dengan perencanaan yang tepat.</p>
                    </div>

                </div>
            </div>
        </aside>
        <div class="min-w-0 flex-1">
            <div class="flex min-h-screen flex-col">
            <header class="flex min-h-[80px] items-center justify-between gap-4 border-b border-slate-200/70 bg-surface px-5 lg:px-9">
                <div class="flex flex-wrap items-center gap-3 text-base"><span class="text-slate-500">Workspace</span><span class="text-muted">/</span><span class="font-medium">@yield('breadcrumb', 'Jadwal Ruangan')</span></div>
                <div class="flex items-center gap-3"><div class="hidden text-right sm:block"><p class="text-base font-semibold">{{ auth()->user()->name }}</p><p class="mt-0.5 text-sm capitalize text-slate-500">{{ auth()->user()->roleLabel() }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-full bg-secondaryLight/40 text-base font-bold text-primaryDark">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" aria-label="Keluar" class="inline-flex min-h-[44px] items-center gap-2 rounded-lg px-3 text-primaryDark hover:bg-background"><x-schedule-icon name="logout" class="h-5 w-5" /><span class="hidden sm:inline">Keluar</span></button></form></div>
            </header>
            <main id="content" @class(['mx-auto w-full flex-1 p-5 lg:p-9', 'max-w-[1800px]' => request()->routeIs('dashboard', '*.dashboard'), 'max-w-[1600px]' => ! request()->routeIs('dashboard', '*.dashboard')])>@yield('content')</main>
            </div>
            @include('shared.footer')
        </div>
    </div>
</body>
</html>
