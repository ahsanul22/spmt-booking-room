<nav aria-label="Navigasi utama">
<ul>
@can('access-general')
    <li><a href="{{ route(auth()->user()->dashboardRouteName()) }}">Dashboard</a></li>
@endcan
@can('access-admin')
<li><a href="{{ route('admin.users.index') }}">Kelola User</a></li>
<li><a href="{{ route('admin.organizational-units.index') }}">Unit Organisasi</a></li>
<li><a href="{{ route('admin.facilities.index') }}">Fasilitas</a></li>
<li><a href="{{ route('admin.rooms.index') }}">Ruangan</a></li>
<li><a href="{{ route('admin.bookings.index') }}">Semua Booking</a></li>
<li><a href="{{ route('admin.schedule.index') }}">Jadwal</a></li>
@else
@can('access-general')
<li><a href="{{ route('rooms.index') }}">Daftar Ruangan</a></li>
<li><a href="{{ route('schedule.index') }}">Jadwal Ruangan</a></li>
@endcan
@can('access-employee')
<li><a href="{{ route('rooms.search') }}">Cari Ruangan</a></li>
<li><a href="{{ route('my-bookings.index') }}">My Booking</a></li>
@endcan
@can('access-pic')
<li><a href="{{ route('pic.rooms.index') }}">Ruangan Saya</a></li>
<li><a href="{{ route('pic.approvals.index') }}">Permintaan Approval</a></li>
<li><a href="{{ route('pic.approvals.history') }}">Riwayat Approval</a></li>
@endcan
@endcan
</ul>
</nav>
