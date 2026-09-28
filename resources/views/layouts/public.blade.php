<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Room') &middot; Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-sans text-text antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:bg-surface focus:p-4">Langsung ke konten</a>
    <header class="border-b border-slate-200 bg-surface">
        <nav aria-label="Navigasi utama" class="mx-auto flex max-w-[1800px] flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8">
            <a href="{{ route('home') }}" class="text-primaryDark" aria-label="Pelindo Multi Terminal, Beranda"><span class="block text-2xl font-bold italic">pelindo</span><span class="text-[9px] font-semibold uppercase tracking-[0.22em]">Multi Terminal</span></a>
            <div class="flex items-center gap-5"><a href="{{ route('login') }}" class="workspace-button">Login</a></div>
        </nav>
    </header>
    <main id="content" class="mx-auto max-w-[1800px] p-5 sm:p-8">@yield('content')</main>
    <footer class="mx-auto flex max-w-[1800px] flex-wrap justify-between gap-2 px-5 py-6 text-xs text-slate-600 sm:px-8"><span>PT Pelindo Multi Terminal</span><span>Booking Room &middot; Untuk pegawai internal</span></footer>
</body>
</html>
