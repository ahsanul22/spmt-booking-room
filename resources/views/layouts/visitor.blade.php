<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative isolate flex min-h-screen flex-col bg-background font-sans text-text antialiased">
    @hasSection('visitor-background')
        <div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10">
            <img src="{{ asset('images/guest-harbor-background.png') }}?v=noncontainer" alt="" class="h-full w-full object-cover opacity-60">
            <div class="absolute inset-0 bg-gradient-to-b from-background/85 via-background/65 to-secondaryLight/50"></div>
        </div>
    @endif
    <a href="#content" class="sr-only focus:not-sr-only focus:block focus:bg-surface focus:p-4">Langsung ke konten</a>
    <header class="bg-primaryDark px-5 py-6 text-white"><div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4"><a href="{{ route('home') }}" class="font-bold">Pelindo Multi Terminal</a><span>Layanan Tamu</span></div></header>
    <main id="content" class="mx-auto flex w-full max-w-7xl flex-1 flex-col p-5 lg:p-9">
        @hasSection('visitor-background')
            @yield('content')
        @else
            <h1 class="mb-4 text-3xl font-bold text-primaryDark">@yield('title')</h1>
            <x-form-feedback />
            @yield('content')
        @endif
    </main>
</body>
</html>
