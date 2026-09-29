<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Ruang Rapat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background font-sans text-text antialiased">
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
    <main id="content" class="user-module p-5">
        @yield('content')
    </main>
    @include('shared.footer')
</body>
</html>
