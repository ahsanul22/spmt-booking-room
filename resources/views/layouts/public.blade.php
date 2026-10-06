<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Room') &middot; Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-background font-sans text-text antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:bg-surface focus:p-4">Langsung ke konten</a>
    <div class="flex min-h-screen flex-col">
    <header class="border-b border-slate-200 bg-surface">
        <nav aria-label="Navigasi utama" class="mx-auto flex max-w-[1800px] flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8">
            <a href="{{ route('home') }}" class="text-primaryDark" aria-label="Pelindo Multi Terminal, Beranda"><x-application-logo variant="light" class="h-8 sm:h-9 w-auto max-w-[165px] sm:max-w-[185px] object-contain" /></a>
            <div class="flex items-center gap-5"><a href="{{ route('login') }}" class="workspace-button">Login</a></div>
        </nav>
    </header>
    <main id="content" class="mx-auto w-full max-w-[1800px] flex-1 p-5 sm:p-8">@yield('content')</main>
    </div>
    @include('shared.footer')
</body>
</html>
