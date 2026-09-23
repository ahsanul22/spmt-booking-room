<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Room') · Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-sans text-text antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:bg-surface focus:p-4">Langsung ke konten</a>
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-slate-200/80 bg-surface">
            <div class="mx-auto flex max-w-[1800px] flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8">
                <a href="{{ route('dashboard') }}" aria-label="Pelindo Multi Terminal, Dashboard" class="shrink-0 text-primaryDark"><span class="block text-2xl font-bold italic tracking-tight">pelindo<span class="text-primary" aria-hidden="true">∿</span></span><span class="block text-[9px] font-semibold uppercase tracking-[0.22em]">Multi Terminal</span></a>
                <div class="flex min-w-0 items-center gap-3">
                    <span class="hidden max-w-xs truncate text-sm font-semibold text-primaryDark sm:block">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-semibold text-primaryDark hover:bg-background">Keluar</button></form>
                </div>
            </div>
            <nav aria-label="Navigasi utama" class="mx-auto flex max-w-[1800px] flex-wrap gap-1 px-5 pb-4 sm:px-8">
                @foreach (['dashboard' => 'Dashboard', 'rooms.search' => 'Cari Ruangan', 'my-bookings.index' => 'My Booking', 'schedule.index' => 'Jadwal Ruangan', 'rooms.index' => 'Daftar Ruangan'] as $routeName => $label)
                    @php
                        $active = match ($routeName) {
                            'my-bookings.index' => request()->routeIs('my-bookings.*'),
                            'rooms.index' => request()->routeIs('rooms.index', 'rooms.show'),
                            default => request()->routeIs($routeName),
                        };
                    @endphp
                    <a href="{{ route($routeName) }}" @if($active) aria-current="page" @endif @class(['rounded-lg px-3 py-3 text-sm font-semibold transition', 'bg-primaryDark text-white' => $active, 'text-slate-600 hover:bg-secondaryLight/20 hover:text-primaryDark' => ! $active])>{{ $label }}</a>
                @endforeach
            </nav>
        </header>
        <main id="content" class="mx-auto w-full max-w-[1800px] flex-1 p-5 sm:p-8">
            @if(request()->routeIs('dashboard', 'schedule.index'))
                @yield('content')
            @else
                <div class="user-module min-w-0 rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm sm:p-6">@yield('content')</div>
            @endif
        </main>
        <footer class="mx-auto flex w-full max-w-[1800px] flex-wrap justify-between gap-2 px-5 pb-6 text-xs text-slate-500 sm:px-8"><span>PT Pelindo Multi Terminal</span><span>Booking Room · Ruang untuk berkolaborasi</span></footer>
    </div>
</body>
</html>
