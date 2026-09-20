<nav aria-label="Navigasi utama">
    <ul>
        @can('access-general')
            <li><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
            <li><a href="{{ route('rooms.index') }}">Daftar Ruangan</a></li>
            <li><a href="{{ route('schedule.index') }}">Jadwal Ruangan</a></li>
        @endcan
        @can('access-employee')
            <li><a href="{{ route('my-bookings.index') }}">My Booking</a></li>
        @endcan
        @can('access-pic')
            <li><a href="{{ route('pic.rooms.index') }}">Ruangan Saya</a></li>
            <li><a href="{{ route('pic.approvals.index') }}">Permintaan Approval</a></li>
        @endcan
        @can('access-admin')
            <li><a href="{{ route('admin.users.index') }}">Kelola User</a></li>
            <li><a href="{{ route('admin.organizational-units.index') }}">Unit Organisasi</a></li>
            <li><a href="{{ route('admin.floors.index') }}">Lantai</a></li>
            <li><a href="{{ route('admin.facilities.index') }}">Fasilitas</a></li>
            <li><a href="{{ route('admin.rooms.index') }}">Ruang Rapat</a></li>
            <li><a href="{{ route('admin.bookings.index') }}">Semua Booking</a></li>
        @endcan
    </ul>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</nav>
