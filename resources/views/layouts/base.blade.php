<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Booking Ruang Rapat')</title>
</head>
<body>
    @auth
        @include('layouts.navigation')
    @endauth
    <main>
        @yield('content')
    </main>
</body>
</html>
