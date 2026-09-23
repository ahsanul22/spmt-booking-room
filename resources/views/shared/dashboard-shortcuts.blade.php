<div class="mt-5 grid gap-3 sm:grid-cols-2">
    @can('access-admin')
        <x-dashboard-shortcut :href="route('admin.dashboard')" title="Dashboard Admin">Kembali ke pengelolaan workspace.</x-dashboard-shortcut>
        <x-dashboard-shortcut :href="route('admin.schedule.index')" title="Jadwal Ruangan" icon="calendar">Buka pratinjau kalender ruang rapat.</x-dashboard-shortcut>
    @else
        @can('access-employee')
            <x-dashboard-shortcut :href="route('rooms.search')" title="Cari Ruangan" icon="room">Pratinjau pencarian berdasarkan waktu dan kebutuhan rapat.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('rooms.index')" title="Daftar Ruangan" icon="room">Pratinjau informasi kapasitas dan fasilitas ruangan.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('schedule.index')" title="Jadwal Ruangan" icon="calendar">Kenali kalender rapat; ketersediaan belum ditampilkan.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('my-bookings.index')" title="My Booking" icon="clock">Pratinjau daftar pengajuan dan status booking pribadi.</x-dashboard-shortcut>
        @endcan
    @endcan
</div>
@can('access-pic')
    @cannot('access-admin')
        <h3 class="mt-6 border-t border-slate-100 pt-5 text-sm font-semibold text-primaryDark">Area PIC Ruangan</h3>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <x-dashboard-shortcut :href="route('pic.rooms.index')" title="Ruangan Saya" icon="room">Pratinjau daftar ruangan yang menjadi tanggung jawab Anda.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('pic.approvals.index')" title="Permintaan Approval" icon="clock">Pratinjau permintaan booking; keputusan belum dapat diproses.</x-dashboard-shortcut>
            <x-dashboard-shortcut :href="route('pic.approvals.history')" title="Riwayat Approval" icon="calendar">Pratinjau riwayat keputusan atas pengajuan booking.</x-dashboard-shortcut>
        </div>
    @endcannot
@endcan
