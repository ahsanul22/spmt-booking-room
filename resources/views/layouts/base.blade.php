<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Ruang Rapat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-background font-sans text-text antialiased">
    <div class="flex min-h-screen flex-col">
    <header class="user-module p-5">
        <p>Booking Ruang Rapat PT Pelindo Multi Terminal</p>
        @auth
            <p>{{ auth()->user()->name }} — Role: {{ auth()->user()->roleLabel() }}</p>
            @include('layouts.navigation')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @endauth
    </header>
    <main id="content" class="user-module flex-1 p-5">
        @yield('content')
    </main>
    </div>
    @include('shared.footer')
</body>
</html>
