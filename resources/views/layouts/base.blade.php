<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Ruang Rapat')</title>
</head>
<body>
    <header>
        <p>Booking Ruang Rapat PT Pelindo Multi Terminal</p>
        @auth
            <p>{{ auth()->user()->name }} — Role: {{ auth()->user()->role }}</p>
            @include('layouts.navigation')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @endauth
    </header>
    <main id="content">
        @yield('content')
    </main>
</body>
</html>
