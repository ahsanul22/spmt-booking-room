<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Pelindo Multi Terminal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="report-page bg-background font-sans text-text">
    <main id="content" class="mx-auto max-w-[1600px] p-5 sm:p-8">
        <p class="mb-5 font-bold text-primaryDark">PT Pelindo Multi Terminal · Booking Ruang Rapat</p>
        @yield('content')
    </main>
</body>
</html>
