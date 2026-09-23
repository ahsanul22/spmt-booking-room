# Frontend Skeleton â€” Tahap 3

Aturan frontend aktif ada di [FRONTEND_GUIDE.md](FRONTEND_GUIDE.md) dan `AGENTS.md`: gunakan Blade + Tailwind/Vite, palet proyek, dan Jadwal Ruangan sebagai acuan visual. Batasan HTML sederhana untuk testing pada Tahap 3 sudah digantikan. Bagian Tahap 3/4 di bawah disimpan sebagai riwayat; status backend terkini mengikuti `PROGRESS.md`.

## Referensi Desain Jadwal (Setelah 4E)

Pembaruan DEC-025: dashboard user kini memiliki pengantar besar dan katalog `RoomCatalog` Livewire (kartu database aktif, filter Semua/Selat Malaka/Ruang Rapat Lainnya, pagination). Pemetaan Selat Malaka sementara berdasarkan nama di config room-catalog, bukan kategori schema. Informasi ruangan sudah aktif pada dashboard; pengecekan ketersediaan dan proses booking tetap belum tersedia. Container user dan dashboard PIC/admin maksimum 1800 px.

Pembaruan DEC-024: role user memakai navbar `layouts/user.blade.php` pada dashboard, jadwal, dan halaman ruangan/booking pribadi. PIC/admin mempertahankan sidebar pada layout schedule. Dashboard menggunakan wrapper `dashboard.blade.php` dengan partial user/PIC terpisah: pegawai fokus pencarian dan booking pribadi; PIC fokus antrean approval, ruangan tanggung jawab, dan riwayat keputusan. Referensi visual tidak berarti isi atau navigasi semua role disamakan. Backend booking/approval tetap pratinjau.

Buka `/schedule` atau `/admin/schedule` setelah login untuk referensi desain Tailwind. File utama `resources/views/shared/schedule.blade.php`, layout `resources/views/layouts/schedule.blade.php`, ikon `components/schedule-icon.blade.php`, interaksi `resources/js/schedule.js`, dan palet `tailwind.config.js`. Jalankan `npm run dev` saat mengembangkan atau `npm run build` untuk aset build.

Desain mencakup sidebar, header akun, kalender bulanan interaktif, panel agenda kosong, dan versi mobile. Navigasi bulan/Hari Ini/pilihan tanggal aktif di browser; data booking serta filter ruangan/lantai menunggu backend, tanpa contoh booking hardcoded. Sesuai instruksi terbaru, desain ini menjadi acuan frontend tim; penerapan ke halaman lain dilakukan per task, bukan otomatis. Merek masih berupa teks sementara, bukan logo resmi.

## Pembaruan Tahap 4E

Kelola PIC dan Kelola Akses kini memakai record database melalui controller admin masing-masing. Dari detail ruangan, admin dapat memilih beberapa PIC role room_pic, melepas satu atau seluruh PIC, serta memilih unit untuk room restricted. Pilihan database, old input, validasi, CSRF, dan flash tersedia. Akun/unit nonaktif ditandai. Room all menampilkan daftar unit tersimpan sebagai tidak digunakan dan tidak menyediakan form simpan unit.

Route `admin.rooms.pics` dan `.access` (GET) kini aktif dengan binding numerik/404; endpoint tulis `.pics.update` (PUT), `.pics.destroy` (DELETE pivot), dan `.access.update` (PUT) dilindungi auth + access-admin. Tersisa 14 route skeleton. Test modul: `RoomAssignmentTest`. Aturan pewarisan akses saat booking tetap terbuka; daftar hanya menyimpan pilihan eksplisit. Catatan Tahap 3/4A-4D di bawah merupakan riwayat sebelum aktivasi ini.

## Pembaruan Tahap 4D

Room Management Super Admin aktif melalui `Admin/RoomController`, `SaveRoomRequest`, dan `RoomService`. Index menampilkan lantai, kapasitas, akses, approval, status operasional/aktif, serta fasilitas. Create/edit memuat pilihan lantai/fasilitas database dan menyimpan room beserta pivot secara atomik. Semua checkbox fasilitas dapat dikosongkan. Status operasional melalui edit; aktif/nonaktif melalui edit atau tombol index/detail. Form memakai CSRF, error, old input, dan flash.

Prefix `admin.rooms.`: GET index/create/show/edit, POST store, PUT/PATCH update, PATCH status (`/{room}/status`), semuanya auth + access-admin, tanpa DELETE. Dua route pics/access tetap skeleton; hanya tautan kembali diperbaiki ke index. Tersisa 16 route skeleton. Room user/PIC, assignment PIC, restricted unit access, Booking, dan Approval belum aktif. `RoomManagementTest` memverifikasi HTTP, database, rendering, validasi, pivot, status, CSRF, serta rollback transaksi.

## Pembaruan Tahap 4C

Lantai dan Fasilitas sudah aktif melalui `Admin/FloorController` dan `Admin/FacilityController`, dengan Form Request masing-masing. Index pagination 20 membaca PostgreSQL; tambah, detail, edit, dan status memakai record sebenarnya. Form memiliki CSRF, error, old input, dan flash lewat komponen `form-feedback`; tombol status tersedia di index/detail. Link preview dihapus untuk kedua modul.

Prefix route `admin.floors.` dan `admin.facilities.` masing-masing menyediakan GET index/create/show/edit, POST store, PUT/PATCH update, dan PATCH status (`/{floor|facility}/status`), seluruhnya auth + access-admin dan tanpa DELETE. Tersisa 20 route skeleton. Form Room tetap skeleton. `FloorFacilityManagementTest` memeriksa HTTP, penyimpanan PostgreSQL, rendering Blade, validasi, role, CSRF, dan keamanan relasi ruangan.

## Pembaruan Tahap 4B

User Management kini aktif melalui `Admin/UserController` dan `SaveUserRequest`. Index mengambil user beserta unit dari PostgreSQL (pagination 20); create/edit mengisi dropdown unit dari database, detail memuat data record sebenarnya. Unit nullable; unit nonaktif ditandai. Form create/update, tombol status index/detail, dan form reset password pada detail memakai CSRF, validasi, old input tanpa password, dan flash message. Tidak ada lagi link `preview` untuk user.

Route prefix `admin.users.`: GET index/create/show/edit, POST store, PUT/PATCH update, PATCH status (`/{user}/status`), PATCH reset-password (`/{user}/password`). Seluruhnya auth + access-admin, tanpa DELETE. Tersisa 28 route skeleton. `UserManagementTest` memverifikasi HTTP, penyimpanan PostgreSQL, rendering Blade, login password baru, role/status, dan CSRF.

## Pembaruan Tahap 4A

Organizational Unit sudah aktif memakai `Admin/OrganizationalUnitController`, bukan skeleton. Index membaca database dengan pagination; create/edit menyediakan parent dari database; detail menampilkan parent dan child. Form memiliki CSRF, validation error, old input, flash message, dan tombol submit aktif. Status dapat diubah dari index/detail atau form edit. ID `preview` tidak berlaku untuk modul ini.

Route berawalan `admin.organizational-units.`: GET index/create/show/edit, POST store, PUT/PATCH update, PATCH status (`/{unit}/status`). Seluruhnya memakai auth + access-admin. Tidak ada DELETE. Tersisa 32 route skeleton; empat halaman unit sekarang ditangani backend. Dokumentasi skeleton di bawah berlaku untuk modul lainnya dan merupakan catatan Tahap 3.

## Halaman dan Route

Seluruh route berikut adalah GET/HEAD dengan `auth` dan Gate Tahap 2B. Nama route existing dipertahankan. Ada 39 route halaman terlindungi: 3 dashboard dan 36 route skeleton (25 route tambahan dari Tahap 2B).

| Area | URL | Nama route | Gate |
| --- | --- | --- | --- |
| Dashboard pegawai | `/dashboard` | `dashboard` | `access-general` |
| Dashboard PIC | `/pic/dashboard` | `pic.dashboard` | `access-pic` |
| Dashboard admin | `/admin/dashboard` | `admin.dashboard` | `access-admin` |
| Cari Ruangan | `/rooms/search` | `rooms.search` | `access-employee` |
| Daftar/detail ruangan | `/rooms`, `/rooms/{room}` | `rooms.index`, `rooms.show` | `access-general` |
| Jadwal umum | `/schedule` | `schedule.index` | `access-general` |
| My Booking, form, detail | `/my-bookings`, `/my-bookings/create`, `/my-bookings/{booking}` | `my-bookings.index`, `.create`, `.show` | `access-employee` |
| Ruangan Saya | `/pic/rooms` | `pic.rooms.index` | `access-pic` |
| Approval, riwayat, detail | `/pic/approvals`, `/pic/approvals/history`, `/pic/approvals/{approval}` | `pic.approvals.index`, `.history`, `.show` | `access-pic` |
| Master data admin | `/admin/{module}`, `/admin/{module}/create`, `/admin/{module}/{record}`, `/admin/{module}/{record}/edit` | `admin.{module}.index`, `.create`, `.show`, `.edit` | `access-admin` |
| Kelola PIC | `/admin/rooms/{record}/pics` | `admin.rooms.pics` | `access-admin` |
| Kelola Akses | `/admin/rooms/{record}/access` | `admin.rooms.access` | `access-admin` |
| Semua/detail booking | `/admin/bookings`, `/admin/bookings/{booking}` | `admin.bookings.index`, `.show` | `access-admin` |
| Jadwal admin | `/admin/schedule` | `admin.schedule.index` | `access-admin` |

`{module}` di tabel adalah singkatan dokumentasi untuk lima kelompok route eksplisit: `users`, `organizational-units`, `floors`, `facilities`, `rooms`; bukan dynamic module routing.

Tautan **Pratinjau Detail/Edit** menggunakan identifier `preview`. Controller belum mencari record, sehingga identifier lain juga membuka skeleton kosong. Ini bukan bukti record tersebut ada atau boleh diakses. Backend nanti harus menambahkan resolution, 404, dan authorization record sebelum memasok data. Route pencarian, create, dan history didefinisikan sebelum route parameter agar tidak tertangkap sebagai ID.

## Controller dan Authorization

- `DashboardController` existing tetap memasok authenticated user dan unit kerja.
- `FrontendSkeletonController` hanya membuka view yang ditetapkan melalui default route; nama view tidak berasal dari query input. Identifier URL tidak diberikan sebagai objek data view.
- Seluruh Gate, middleware account aktif, mapping dashboard, dan aturan authorization Tahap 2B dipertahankan.
- PIC mendapat seluruh halaman pegawai. Menu admin hanya administrasi dan jadwal; akses URL umum/PIC untuk admin tetap mengikuti DEC-012. Ini tidak memberi assignment atau kewenangan approval record.
- Cari Ruangan dan form booking memakai `access-employee`. Hak booking pribadi admin tetap belum diputuskan.

## Struktur Blade

```text
resources/views/
â”œâ”€â”€ layouts/                 # base, navigation, header, identitas, logout
â”œâ”€â”€ components/              # field, select, textarea, table, skeleton-form,
â”‚                            # skeleton-notice, pending-action
â”œâ”€â”€ dashboard.blade.php      # dashboard bersama dengan identitas existing
â”œâ”€â”€ shared/                  # dashboard-shortcuts, schedule, room-details,
â”‚                            # booking-details
â”œâ”€â”€ user/
â”‚   â”œâ”€â”€ rooms/               # index, search, show
â”‚   â””â”€â”€ bookings/            # index, create, show
â”œâ”€â”€ pic/
â”‚   â”œâ”€â”€ rooms/               # index
â”‚   â””â”€â”€ approvals/           # index, history, show
â””â”€â”€ admin/
    â”œâ”€â”€ users/               # index, create, edit, show, _form
    â”œâ”€â”€ organizational-units/# index, create, edit, show, _form
    â”œâ”€â”€ floors/              # index, create, edit, show, _form
    â”œâ”€â”€ facilities/          # index, create, edit, show, _form
    â”œâ”€â”€ rooms/               # index, create, edit, show, _form, pics, access
    â””â”€â”€ bookings/            # index, show
```

Jadwal umum/admin memakai view yang sama. Detail booking dipakai user/admin/approval. Informasi dasar ruangan dipakai detail umum/admin. Form tambah/edit setiap master data memakai partial bersama. Menu memakai `@can`; keamanan URL tetap middleware `can`.

## Kontrak Data untuk Integrasi Berikutnya

Tidak ada query modul di controller atau Blade dan tidak ada data dummy permanen. Semua list memakai `@forelse($variable ?? [] ...)`; detail memakai null fallback. Nama field booking di sini merupakan kontrak presentasi sementara, bukan keputusan schema booking.

| View | Variabel yang disiapkan |
| --- | --- |
| Daftar/pencarian/ruangan PIC | `$rooms`; relasi `floor`, `facilities`; pencarian dapat menerima `availability_label` dari backend |
| Detail/form ruangan | `$room`, `$floors`, `$facilities`, `$selectedFacilityIds` |
| Kelola PIC | `$room` dengan relasi `pics`, `$eligiblePics`, `$selectedPicIds` |
| Kelola Akses | `$room`, `$units`, `$selectedUnitIds` |
| User | `$users` / `$userRecord`, `$units`; relasi `organizationalUnit` |
| Unit organisasi | `$units` / `$unit`; relasi `parent`, `children` |
| Lantai/fasilitas | `$floors` / `$floor`, `$facilities` / `$facility` |
| Booking | `$bookings` / `$booking`, `$rooms`, `$units` |
| Approval | `$approvals` / `$approval` |
| Jadwal | `$schedules`, `$rooms`, `$floors` |

Objek booking/approval untuk presentasi menyediakan `id`, `applicant.name`, `organizationalUnit.name`, `room.name`, `agenda`, `date`, `start_time`, `end_time`, `participant_count`, `notes`, `status`, `rejection_reason`; riwayat approval juga `decided_at`. Backend nanti wajib memasok record yang sudah diotorisasi serta eager-load relasi yang ditampilkan. Data pilihan dan ID terpilih berasal dari backend, bukan aturan bisnis di view.

## Form dan Placeholder

- Field dapat diisi, tetapi tombol proses memakai `type="button" disabled` dan keterangan belum tersedia. Tautan Detail, Booking Ruangan, Tambah, Edit, dan Kembali tetap aktif untuk navigasi.
- `skeleton-form` menggunakan satu handler `event.preventDefault()` untuk mencegah implicit submit lewat Enter. Form menggunakan POST ke URL saat ini dengan CSRF; tidak ada endpoint tulis modul. Jika JavaScript dilewati, POST tetap tidak bisa melakukan perubahan (405 atau penolakan middleware).
- Password tidak pernah diisi ulang. Password dan konfirmasi diwajibkan di form tambah, opsional di edit. Ini hanya struktur HTML; validasi backend belum dibuat.
- Cari Ruangan, availability, filter jadwal/status, CRUD, penugasan PIC, restricted access, pengajuan/cancel booking, dan approval belum menjalankan logic.
- Tidak ada statistik, kalender library, notification, scheduler, query booking, atau data booking dummy.
- Pilihan unit restricted tidak mewariskan pilihan ke children. Aturan inheritance tetap terbuka.
- UI pilihan PIC disiapkan untuk `$eligiblePics` dengan role `room_pic` sesuai instruksi. Rule existing `EligibleRoomPic` ternyata menerima `room_pic` **dan** `super_admin`; rule tidak diubah. Saat backend dibuat, selaraskan cakupan opsi berdasarkan instruksi tahap tersebut dan tetap gunakan rule existing.

## Verifikasi

`FrontendSkeletonTest` memeriksa semua 14 route yang masih skeleton: redirect guest, matriks akses ketiga role, rendering kosong, tautan halaman yang diizinkan, penolakan metode tulis, field form, password tambah/edit, serta rendering/escaping data ruangan yang dipasok dari test. Test existing memeriksa dashboard, authentication, authorization, dan database. Semua test database menggunakan schema PostgreSQL sementara melalui `PostgresTestCase`.

Hasil final dicatat di `PROGRESS.md`. Tidak ada snapshot atau test styling. Tahap 4A diuji melalui `OrganizationalUnitTest` dan Tahap 4B melalui `UserManagementTest`; Tahap 4C diuji melalui `FloorFacilityManagementTest`; Tahap 4D diuji melalui `RoomManagementTest`; Tahap 4E diuji melalui `RoomAssignmentTest`.
