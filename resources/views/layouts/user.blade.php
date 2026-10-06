<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Room') Â· Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-sans text-text antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:bg-surface focus:p-4">Langsung ke konten</a>
    <div class="flex min-h-screen flex-col">
        <header data-employee-navbar class="sticky top-0 z-30 max-h-[100dvh] overflow-y-auto border-b border-slate-200/80 bg-surface transition-colors duration-200 data-[scrolled=true]:bg-surface/90 data-[scrolled=true]:backdrop-blur-sm motion-reduce:transition-none">
            <div class="mx-auto flex min-h-[88px] max-w-[1800px] flex-wrap items-center justify-between gap-x-6 gap-y-4 px-5 py-4 sm:px-8 xl:gap-x-10">
                <a href="{{ route('dashboard') }}" aria-label="Pelindo Multi Terminal, Dashboard" class="shrink-0 rounded-lg">
                    <x-application-logo variant="light" class="h-8 sm:h-9 w-auto max-w-[165px] sm:max-w-[185px] object-contain" />
                </a>
                <button type="button" data-menu-toggle aria-expanded="false" aria-controls="employee-navigation" aria-label="Buka navigasi" class="rounded-xl border border-secondaryLight/50 bg-background p-3 text-primaryDark transition hover:bg-secondaryLight/30 focus-visible:outline-primary lg:hidden"><x-schedule-icon name="menu" /></button>
                <div id="employee-navigation" class="hidden w-full flex-col gap-5 border-t border-secondaryLight/40 pt-4 lg:flex lg:w-auto lg:min-w-0 lg:flex-1 lg:flex-row lg:items-center lg:gap-5 lg:border-0 lg:pt-0">
                    <nav aria-label="Navigasi utama" class="flex flex-col gap-1 lg:shrink-0 lg:flex-row lg:gap-3">
                        @foreach (['dashboard' => 'Dashboard', 'rooms.index' => 'Booking Ruangan', 'my-bookings.index' => 'My Booking', 'schedule.index' => 'Jadwal Ruangan'] as $routeName => $label)
                            @php
                                $active = match ($routeName) {
                                    'my-bookings.index' => request()->routeIs('my-bookings.index', 'my-bookings.show'),
                                    'rooms.index' => request()->routeIs('rooms.index', 'rooms.show', 'my-bookings.create'),
                                    default => request()->routeIs($routeName),
                                };
                            @endphp
                            <a href="{{ route($routeName) }}" @if($active) aria-current="page" @endif @class(['flex min-h-[44px] items-center whitespace-nowrap border-b-2 px-3 py-2.5 text-base font-semibold transition-colors duration-200 xl:px-4', 'border-primary text-primaryDark' => $active, 'border-transparent text-slate-600 hover:border-secondaryLight hover:text-primaryDark' => ! $active])>

                                {{ $label }}
                            </a>
                        @endforeach
                    </nav>
                    <div class="flex min-w-0 items-center justify-between gap-4 border-t border-secondaryLight/40 pt-4 lg:ml-auto lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-secondaryLight/40 text-base font-bold text-primaryDark">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <div class="min-w-0 lg:hidden xl:block">
                                <p class="truncate text-base font-semibold text-primaryDark xl:max-w-[160px] 2xl:max-w-xs">{{ auth()->user()->name }}</p>
                            </div>
                        </div>
                        <form class="shrink-0" method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl px-3 py-2.5 text-base font-semibold text-primaryDark transition hover:bg-background"><x-schedule-icon name="logout" class="h-4 w-4" />Keluar</button></form>
                    </div>
                </div>
            </div>
        </header>
        <main id="content" class="mx-auto w-full max-w-[1800px] flex-1 p-5 sm:p-8">
            @if(request()->routeIs('dashboard', 'schedule.index', 'my-bookings.create', 'rooms.index', 'rooms.show', 'my-bookings.index', 'my-bookings.show'))
                @yield('content')
            @else
                <div class="user-module min-w-0 rounded-2xl border border-slate-200/80 bg-surface p-5 shadow-sm sm:p-6">@yield('content')</div>
            @endif
        </main>
    </div>
    @include('shared.footer')
</body>
</html>
