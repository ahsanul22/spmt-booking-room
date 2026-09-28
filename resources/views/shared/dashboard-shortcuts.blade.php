<div class="mt-5 grid gap-3 sm:grid-cols-2">
    @can('access-admin')
        <x-dashboard-shortcut :href="route('admin.dashboard')" title="Dashboard Admin">Kembali ke pengelolaan workspace.</x-dashboard-shortcut>
        <x-dashboard-shortcut :href="route('admin.schedule.index')" title="Jadwal Ruangan" icon="calendar">Lihat kalender penggunaan ruang rapat.</x-dashboard-shortcut>
    @else
        @if(auth()->user()->role === \App\Models\User::ROLE_USER)
            <x-dashboard-shortcut :href="route('rooms.index')" title="Booking Ruangan" icon="room">Siapkan ruangan, jadwal, dan agenda melalui form booking.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Periksa tanggal dan jam penggunaan ruangan.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('my-bookings.index')" title="My Booking" icon="clock">Daftar pengajuan dan status booking pribadi.</x-dashboard-shortcut>
        @else
            <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Periksa tanggal dan jam penggunaan ruangan.</x-dashboard-shortcut>
        @endif
    @endcan
</div>
@can('access-pic')
    @cannot('access-admin')
        <h3 class="mt-6 border-t border-slate-100 pt-5 text-sm font-semibold text-primaryDark">Area PIC Ruangan</h3>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <x-dashboard-shortcut :href="route('pic.rooms.index')" title="Ruangan Saya" icon="room">Lihat informasi dan jadwal ruangan yang menjadi tanggung jawab Anda.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('pic.approvals.index')" title="Permintaan Approval" icon="clock">Tinjau dan putuskan permintaan booking ruangan Anda.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('pic.approvals.history')" title="Riwayat Approval" icon="calendar">Riwayat keputusan atas pengajuan booking.</x-dashboard-shortcut>
        </div>
    @endcannot
@endcan
