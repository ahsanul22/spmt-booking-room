# Decisions

## DEC-025 - Katalog Ruangan Livewire pada Dashboard User

- Pengguna meminta pengantar sistem dominan, container lebar, tiga filter bergaris bawah, dan kartu ruangan yang langsung tampil melalui Livewire. Menggantikan prioritas layout user sebelumnya; PIC tetap berfokus pada approval.
- Menambah Livewire 3.8.9 melalui Composer pada Laravel 10 existing. Katalog membaca master ruangan secara read-only dan memakai server-render awal, filter, serta pagination Livewire. Ini aktivasi katalog khusus dashboard sesuai task, bukan backend booking/approval/ketersediaan.
- Karena schema belum punya kategori, asumsi sementara selama klarifikasi belum dijawab: filter Selat Malaka mencocokkan nama terkonfigurasi, dan Ruang Rapat Lainnya adalah komplemennya. Tidak menetapkan klasifikasi bisnis permanen atau menambah migration. Konfigurasi `room-catalog.featured_room_name` menjadi sumber pemetaan nama.
- Katalog menampilkan ruangan aktif kepada access-employee dengan label akses dan operasional; tidak menyatakan ketersediaan jadwal atau kelayakan booking user. Semua request komponen tetap diotorisasi. Ruangan restricted dapat dikenali sebagai informasi; enforcement booking dan inheritance tetap belum diputuskan.
- Integrasi aset menggunakan injeksi otomatis [Livewire 3](https://livewire.laravel.com/docs/3.x/installation), tanpa memasang Alpine terpisah. Dashboard memakai maksimum 1800 px; pengantar user minimum 58vh dengan tinggi mengikuti konten pada mobile.

## DEC-024 - Navigasi dan Prioritas Dashboard Mengikuti Role

- Arahan pengguna mengoreksi DEC-023: referensi dashboard admin dipakai untuk warna, tipografi, kartu, dan spacing; struktur informasi mengikuti tugas role. PIC mendahulukan approval, ruangan tanggung jawab/jadwal, dan riwayat keputusan. Booking pribadi sekunder. Pegawai mendahulukan pencarian ruangan serta pemantauan booking; panduan tidak mendominasi dashboard.
- Pegawai (`user`) memakai navbar `layouts.user`, termasuk ketika berpindah ke jadwal dan halaman ruangan/booking pribadi. PIC/admin mempertahankan navigasi existing; sidebar PIC mengutamakan tugas PIC. Navbar mobile membungkus tanpa menu tersembunyi sehingga tetap dapat dipakai tanpa JavaScript.
- Controller dashboard bersama dipertahankan; wrapper view memilih partial konten berdasarkan Gate dan layout berdasarkan role. Tidak mengubah izin akses, route, penugasan PIC, atau kebijakan booking/approval. Semua modul yang belum aktif tetap pratinjau tanpa data dummy.

## DEC-023 - Dashboard Pegawai/PIC dengan Panduan Penggunaan

- Dashboard pegawai dan PIC tetap memakai `DashboardController` serta view `dashboard` bersama, dengan konten sesuai Gate. Tampilan kini memakai `layouts.schedule` seperti dashboard admin; menu Dashboard aktif pada ketiga route dashboard. Route, judul role, dan authorization existing dipertahankan.
- Atas permintaan pengguna, pengantar sistem dan panduan booking tampil langsung pada dashboard, dengan penjelasan tugas tambahan untuk PIC. Kartu akses cepat menggunakan komponen dashboard admin existing. Booking, approval, dan data jadwal tetap pratinjau; panduan tidak berarti proses tersebut sudah aktif.

## DEC-022 - Return View Admin per Controller Modul

- Atas permintaan pengguna, seluruh route admin memakai controller dalam namespace Admin. Tiga route pratinjau yang tersisa dipindahkan dari FrontendSkeletonController: daftar/detail booking ke BookingController::index/show, jadwal ke ScheduleController::index.
- Perubahan hanya pemindahan return view dari default route ke action eksplisit. Booking dan jadwal tetap pratinjau tanpa query, model binding booking, atau proses baru. URL, nama route, middleware, dan view dipertahankan; controller master data aktif tidak diubah.
- FrontendSkeletonController masih digunakan area pegawai/PIC. Pengujian 14 halaman pratinjau tetap mencakup tiga route admin meskipun kini memakai controller khusus.

## DEC-021 - View Dashboard Admin dengan Layout Referensi

- Dashboard admin memakai `Admin/DashboardController::index` khusus sesuai arahan pengguna untuk memisahkan controller admin. Controller menetapkan view `admin.dashboard`, judul, dan data akun; route hanya memetakan URL ke action dengan middleware existing. Controller/view dashboard pegawai/PIC tetap existing. Ini memperbarui penggunaan satu controller/view dashboard pada DEC-012 untuk admin.
- Layout `layouts.schedule` digunakan bersama secara terbatas: title dan breadcrumb memakai section dengan default Jadwal Ruangan, menu aktif mengikuti route. Komponen kartu akses cepat terpisah, tanpa memigrasi halaman lain atau menambah dependency.
- Ringkasan dan aktivitas booking tetap pratinjau tanpa query/statistik hingga backend dashboard ditugaskan. Tautan pengelolaan menuju modul existing yang sudah aktif.

## DEC-020 - Aturan Frontend Tim dengan Referensi Jadwal

- Pengguna menetapkan Jadwal Ruangan dan palet existing sebagai acuan pengembangan frontend tim melalui CLI maupun integrasi HTML. Menggantikan batasan frontend sederhana untuk testing pada DEC-006/DEC-013 serta status kandidat saja pada DEC-019; catatan tahap sebelumnya tetap dipertahankan sebagai riwayat.
- Gunakan Blade + Tailwind/Vite existing, token tailwind.config.js, dan komponen/partial yang dapat dipakai ulang. Gaya layout, tipografi, card, tombol, dan mobile mengikuti referensi jadwal dengan penyesuaian konten per halaman.
- Scope tetap per halaman yang ditugaskan. Frontend boleh dikembangkan setelah 4E tanpa mengaktifkan backend tahap 5-7. Kontrak form, route, data, dan authorization existing wajib dipertahankan; modul tanpa backend menampilkan pratinjau/empty state tanpa data bisnis hardcoded.
- Panduan operasional berada di docs/FRONTEND_GUIDE.md dan dirujuk AGENTS.md, PROJECT_CONTEXT, ROADMAP, serta FRONTEND. Perubahan ini hanya dokumentasi; generalisasi layout dan penerapan visual dilakukan dalam task frontend berikutnya.

## DEC-019 - Referensi Desain Jadwal Ruangan

- Atas instruksi pengguna setelah 4E, satu halaman Jadwal Ruangan dibuat sebagai referensi visual untuk membandingkan desain tim. Ini pengecualian terarah dari frontend skeleton sederhana, bukan aktivasi backend Tahap 5/7.
- Memakai Tailwind 3 dan Vite yang sudah dipasang pengguna, tanpa dependency baru. Palet pengguna disimpan pada tailwind.config.js. Layout khusus layouts/schedule membatasi penerapan desain ke /schedule dan /admin/schedule; halaman lain tetap memakai layout existing.
- Kalender bulanan di browser mendukung navigasi bulan, Hari Ini, pemilihan tanggal, dan navigasi mobile. Tanggal mengikuti perangkat pengguna untuk pratinjau, bukan keputusan zona waktu bisnis. Tidak memasukkan booking/ruangan dummy atau menyatakan tanggal kosong sebagai tersedia; data agenda menunggu backend jadwal.
- Identitas akun dan navigasi berasal dari auth/Gate Laravel. Tampilan merek sementara berupa teks, bukan aset logo resmi. Desain belum ditetapkan sebagai standar final tim.

## DEC-018 - Assignment PIC dan Restricted Unit Access (Tahap 4E)

- Dua controller admin khusus, `RoomPicController` dan `RoomAccessController`, memakai Form Request dan `RoomAssignmentService`. GET/PUT pics, DELETE pics/{pic}, dan GET/PUT access memakai auth + access-admin, binding ID numerik, 404, dan CSRF. DELETE hanya melepas pivot pada room terkait, bukan menghapus user.
- Melanjutkan implementasi awal yang sudah ada di workspace: pilihan PIC dan `EligibleRoomPic` diselaraskan ke role `room_pic`, sesuai kontrak UI Tahap 3. Ini menggantikan cakupan rule fondasi yang menerima super_admin; akses area PIC bagi admin pada DEC-012 tetap berlaku dan tidak otomatis menjadi assignment.
- Beberapa PIC boleh dipilih; array harus berisi ID existing dan distinct. Service memeriksa ulang role dengan shared lock user, mengunci room, dan sync pivot dalam transaksi. Perubahan role user setelah assignment tidak otomatis melepas pivot; assignment lama tetap terlihat dan dapat dilepas. Akun nonaktif ditandai, tanpa aturan status tambahan. Minimum PIC belum ditetapkan, sehingga pilihan kosong diperbolehkan.
- Unit akses disimpan sebagai daftar ID existing dan distinct, termasuk unit nonaktif dengan label. Sync hanya boleh untuk access_type restricted dan memeriksa ulang tipe pada room yang dikunci. Pilihan kosong diperbolehkan; belum menetapkan minimum unit.
- Pada tipe all, daftar tersimpan ditampilkan sebagai tidak digunakan dan tidak dapat diedit melalui endpoint unit akses. Mengganti tipe akses melalui Room Management mempertahankan pivot existing. Penyimpanan hanya mencakup ID yang dipilih, tanpa menambah child otomatis; keputusan pewarisan akses dan penegakan saat booking tetap terbuka.
- Operasi assignment tidak mengubah field room, fasilitas, role user, unit organisasi, atau room lain. Tidak menambah migration/dependency. Room user/PIC, Booking, dan Approval belum diaktifkan.

Keputusan berikut telah ditentukan sebagai dasar pengembangan. Catat alasan jika suatu keputusan diubah dan tambahkan keputusan arsitektur atau bisnis baru ketika disepakati. Pertanyaan terbuka pada `PROJECT_CONTEXT.md` belum menjadi keputusan.

## DEC-001 â€” Role

Sistem menggunakan tiga role utama:

- User.
- Room PIC.
- Super Admin.

Alasan: menjaga authorization tetap sederhana dan tidak menjadikan jabatan organisasi sebagai role aplikasi.

## DEC-002 â€” Organizational Unit

Struktur organisasi nantinya menggunakan hierarchical organizational unit.

Alasan: Direktorat, Divisi, dan Departemen dapat disimpan dalam satu struktur yang fleksibel dan mudah menyesuaikan perubahan organisasi.

## DEC-003 â€” PIC Ruangan

PIC dikaitkan dengan ruangan, bukan otomatis dengan divisi. Satu ruang dapat mempunyai lebih dari satu PIC.

## DEC-004 â€” Room Access

Sistem mendukung `all` dan `restricted`. Default desain adalah ruang dapat digunakan seluruh pegawai kecuali terdapat aturan khusus.

## DEC-005 â€” Room Approval

Approval dikonfigurasi per ruangan menggunakan `requires_approval`. Tidak semua room wajib melalui approval.

## DEC-006 â€” Frontend

Frontend tahap development dibuat basic dan functional menggunakan Blade/HTML sederhana. Frontend final akan dikembangkan terpisah oleh anggota tim lain menggunakan HTML dan Tailwind CSS. Struktur Blade harus mudah diganti tanpa mengubah backend.

## DEC-007 â€” Booking Conflict

Booking berstatus Pending dan Approved dianggap memblokir slot waktu. Rejected dan Cancelled tidak memblokir slot waktu.

## DEC-008 â€” Scope

Sistem saat ini tidak mencakup catering, snack, konsumsi, rapat eksternal, pengelolaan tamu eksternal, WhatsApp, SMS, payment, atau fitur di luar kebutuhan booking ruang rapat. Notification tahap awal hanya internal website; email tidak diperlukan saat ini.

## DEC-009 â€” Fondasi PostgreSQL (Tahap 1)

- Database memakai PostgreSQL sesuai arahan pengguna, dengan migration Laravel 10. Tidak menambah package role.
- Satu user memiliki satu `role` (`user`, `room_pic`, `super_admin`) dan satu `organizational_unit_id` nullable sesuai prompt Tahap 1. Hak booking PIC/admin belum ditentukan.
- Role, status, tipe unit, dan tipe akses memakai enum schema Laravel (CHECK pada PostgreSQL). Kapasitas menggunakan CHECK eksplisit `>= 0` karena PostgreSQL tidak menerapkan unsigned integer.
- Penghapusan unit yang masih direferensikan parent/user dan lantai yang masih memiliki ruang dibatasi (restrict). Pivot memakai cascade untuk membersihkan hubungan saja, dengan primary key gabungan agar tidak duplikat.
- Kode ruang nullable dan unik; nomor lantai dan nama fasilitas juga unik agar master data tidak ambigu. Nama ruang/unit tidak harus unik secara global.
- `role`, `is_active`, dan `organizational_unit_id` pada User tidak dibuka untuk mass assignment. Modul admin nantinya harus mengatur field tersebut secara eksplisit setelah authorization.
- Validasi calon PIC disediakan lewat `EligibleRoomPic`. Belum ada workflow penugasan atau trigger lintas tabel; pemanggil relasi nanti wajib memvalidasi PIC.
- Data contoh hanya untuk local/testing. Test memakai schema PostgreSQL sementara agar constraint database asli diuji tanpa menghapus data development.

## DEC-010 â€” Penggabungan Migration User

Atas permintaan pengguna, seluruh kolom users disatukan dalam migration pembuatannya agar mudah dibaca. Migration organizational_units dijalankan lebih dulu karena users mereferensikannya. Perapian dilakukan saat fondasi masih development dan belum dipush; riwayat migration lokal diselaraskan tanpa reset data. Untuk database yang sudah digunakan bersama/produksi nanti, perubahan struktur berikutnya memakai migration baru.

## DEC-011 â€” Authentication Dasar (Tahap 2A)

- Laravel tetap 10.50.3. Menambah `laravel/breeze` 1.29.1 sebagai dependency development yang kompatibel Laravel 10; dependency existing tidak di-upgrade.
- Mengambil scaffolding controller session dan LoginRequest dari stub resmi Breeze Blade. Tidak menjalankan installer penuh karena turut memasang registrasi, reset password, profil, dan frontend di luar scope. View login/dashboard memakai Blade HTML sederhana tanpa ketergantungan build Vite atau tambahan package NPM.
- Memakai guard session `web`, hashing/Remember Me Laravel, middleware `auth`/`guest`, dan CSRF bawaan. Pembatasan percobaan login mengikuti Breeze (5 kegagalan per kombinasi email/IP sebelum dibatasi sementara).
- `attemptWhen` memeriksa `is_active` setelah password divalidasi Laravel dan sebelum session login dibuat. Pesan akun tidak aktif hanya diberikan jika kredensial benar. Middleware `EnsureAccountIsActive` juga mengakhiri session/Remember Me akun yang kemudian dinonaktifkan; ini bukan authorization role.
- Semua login berhasil diarahkan ke `/dashboard`; role hanya ditampilkan. Logout melalui POST, invalidate session, regenerate token CSRF, lalu redirect `/login`.
- Registrasi publik, reset password, email verification, dan profil tidak memiliki route pada tahap ini. Akun memakai tabel/seeder existing.
- Helper `Tests\PostgresTestCase` menyatukan mekanisme schema PostgreSQL sementara existing untuk test fondasi dan authentication. Tidak memakai RefreshDatabase yang berisiko mereset schema development.

## DEC-012 â€” Role dan Authorization (Tahap 2B)

- Menggunakan Gate Laravel terpusat di `AuthServiceProvider`, middleware bawaan `auth` + `can`, dan Blade `@can`. Tidak menambah package, role, atau middleware role custom karena mekanisme bawaan sudah cukup.
- `access-general`: ketiga role boleh melihat dashboard pegawai, daftar ruang, dan jadwal. `access-employee`: user/PIC boleh membuka My Booking. `access-pic`: PIC/admin boleh membuka placeholder PIC. `access-admin`: hanya admin boleh membuka administrasi. Semua Gate juga mensyaratkan akun aktif.
- Izin area PIC untuk admin hanya akses halaman, bukan penugasan sebagai PIC atau izin approval seluruh ruangan. Relasi `room_pics` tetap menentukan assignment ketika modul tersebut dibuat. Tidak menggunakan bypass global `Gate::before`.
- Admin belum mendapat My Booking karena hak booking pribadi admin belum ditentukan; admin mendapat placeholder Semua Booking dan informasi umum. PIC mendapat seluruh halaman pegawai sesuai instruksi Tahap 2B.
- Mapping dashboard tunggal di `User::dashboardRouteName()`: user ke `/dashboard`, PIC ke `/pic/dashboard`, admin ke `/admin/dashboard`. Digunakan saat login, redirect guest middleware, dan link Dashboard. Menggantikan redirect seragam Tahap 2A.
- Akses terlarang selalu HTTP 403 dengan halaman aman. Role tidak dikenal ditolak tanpa fallback; diuji dengan user dalam memori tanpa mengubah constraint PostgreSQL. Logout tetap dapat dipakai.
- Dashboard memakai satu controller/view/layout dengan judul berbeda; placeholder memakai satu view bersama. Tidak ada query booking, CRUD, approval, atau pembatasan berdasarkan organizational unit.

## DEC-013 â€” Frontend Skeleton Seluruh Role (Tahap 3)

- Sesuai instruksi pengguna, frontend skeleton menjadi Tahap 3; Master Data Backend pindah ke Tahap 4. Roadmap diperbarui sampai Tahap 9. Tidak melanjutkan implementasi backend modul.
- Mempertahankan Blade/HTML sederhana dan layout/dashboard bersama tanpa dependency, CSS custom, atau kebutuhan build tambahan. Jadwal user/admin, detail dasar ruangan, detail booking/approval, dan form tambah/edit dibagikan melalui partial/komponen sederhana.
- Mempertahankan Gate dan akses URL DEC-012. Menu admin berfokus pada administrasi/jadwal, meskipun akses URL umum/PIC tetap diizinkan oleh fondasi existing. Pencarian yang mengarah ke booking dan form booking memakai `access-employee`; hak booking pribadi admin tetap terbuka.
- Satu controller skeleton membuka view yang ditentukan default route, tanpa query modul. Route detail/edit memakai identifier untuk navigasi, tetapi belum melakukan model binding/resolution; tautan `preview` bukan record dummy. Backend berikutnya wajib mengotorisasi record sebelum memberi data kepada view.
- Klarifikasi setelah Tahap 3: `FrontendSkeletonController` bersifat sementara untuk ketiga role. Saat backend modul ditugaskan, pindahkan route ke controller sesuai modul/area sambil mempertahankan Blade yang tersedia. Hapus controller skeleton setelah tidak ada route yang memakainya; jangan menumpuk business logic semua role di controller ini.
- Seluruh form proses menggunakan tombol nonaktif dan pencegahan submit sederhana. POST ke URL halaman tidak memiliki endpoint tulis; tidak ada perubahan data jika handler JavaScript dilewati. Login/logout existing tetap aktif.
- Seluruh list/detail siap menerima variabel backend dengan empty/null fallback. Nama field booking adalah kontrak tampilan sementara, bukan penetapan schema. Tidak memutuskan inheritance akses unit, kebijakan approval, atau pembatalan.
- Rule `EligibleRoomPic` tetap utuh (menerima PIC/admin); UI menyediakan kontrak pilihan `room_pic` sesuai prompt, tanpa query atau penyimpanan. Penyelarasan opsi dan rule dilakukan saat backend ditugaskan.

## DEC-014 - Backend Organizational Unit (Tahap 4A)

- Controller admin khusus, Form Request untuk field dasar, dan service sederhana untuk validasi hierarchy serta penyimpanan. Memakai tabel/model existing tanpa migration atau dependency baru.
- Directorate tanpa parent; Division wajib parent Directorate; Department wajib parent Division. Parent sendiri/siklus ditolak; perubahan type ditolak jika child existing menjadi tidak valid, termasuk child nonaktif.
- Validasi hierarchy dan save berada dalam transaction PostgreSQL dengan `SHARE ROW EXCLUSIVE` table lock untuk menserialkan perubahan struktur concurrent. Lock hanya selama operasi simpan; pembacaan tetap berjalan.
- Status menggunakan `is_active`, tanpa endpoint delete. Status hanya mengubah unit yang dipilih, tidak cascade ke child/user dan tidak menghapus relasi. Parent nonaktif tetap dapat dipilih dengan label Nonaktif; status tidak mengubah validitas struktur. Pembatasan bisnis tambahan terkait status belum ditetapkan.
- Route memakai `auth` dan Gate `access-admin`, model binding ID numerik dengan 404 untuk record tidak ditemukan. Store/update/status menerima field tervalidasi saja; form memakai CSRF.
- Index dipaginasi 20 unit dan eager-load parent; detail memuat parent/child. Pilihan parent berasal dari database (Directorate/Division, mengecualikan unit sendiri); validasi server menjadi sumber kebenaran.
- Aktivasi modul ini tidak mengaktifkan dropdown atau penyimpanan User Management. Berhenti setelah Tahap 4A.

## DEC-015 - Backend User Management (Tahap 4B)

- `Admin/UserController` menangani delapan aksi user dengan `SaveUserRequest` untuk create/update; memakai model/tabel existing tanpa dependency/migration. Field role, unit, dan status tetap guarded pada model, diisi eksplisit setelah authorization. Tidak ada endpoint delete.
- Seluruh route dilindungi auth + access-admin, ID numerik memakai model binding dan 404. Role valid hanya user, room_pic, super_admin. Nama/email wajib maksimal 255 karakter, email valid dan unique (update mengabaikan record sendiri), status boolean, unit nullable sesuai DEC-009 dan harus ada di database bila dipilih.
- Dropdown memuat semua unit database termasuk nonaktif dengan label, agar unit existing tetap bisa ditampilkan/dipertahankan. Tidak menambahkan pembatasan status unit yang belum diminta.
- Password create wajib minimal 8 karakter dan confirmed. Edit kosong/tidak dikirim mempertahankan hash lama. Reset tersedia pada detail dengan konfirmasi, menggunakan `Hash::make`; password tidak ditampilkan atau diflash ke session. Perubahan password merotasi remember token. Reset mengganti kredensial login; tidak menambahkan mekanisme pemutusan seluruh session aktif.
- Status hanya mengubah is_active; login/middleware existing menolak akun nonaktif. Perubahan role berlaku pada request berikutnya. Admin yang mengubah role sendiri diarahkan ke dashboard role baru; penonaktifan diri mengakhiri session dan kembali ke login. Tidak menambahkan aturan larangan perubahan diri/akun admin terakhir yang belum diminta.
- Perubahan role tidak mengubah assignment room_pics otomatis. Modul Room/PIC dan penanganan assignment setelah perubahan role tetap di luar Tahap 4B.
- Index memakai pagination 20 dan eager-load unit; halaman/form user membaca database, memiliki CSRF, validation error, old input, flash message, serta tombol proses aktif. Berhenti setelah Tahap 4B.

## DEC-016 - Backend Floor & Facility Management (Tahap 4C)

- Dua controller admin khusus (`FloorController`, `FacilityController`) dan dua Form Request (`SaveFloorRequest`, `SaveFacilityRequest`) memakai model/tabel existing. Operasi sederhana tetap di controller; tidak menambah service, migration, atau dependency.
- Nama wajib string maksimal 255, deskripsi opsional string maksimal 5000, status wajib boolean. Nomor lantai wajib integer dalam rentang kolom PostgreSQL integer dan unique; nama fasilitas unique sesuai constraint existing. Update mengabaikan record sendiri pada pemeriksaan unique.
- Nomor lantai tidak dibatasi contoh development 1-8; nol/negatif tetap diperbolehkan sesuai kolom signed existing. Tidak menetapkan batas bisnis lantai baru di tahap ini.
- Status menggunakan is_active melalui form edit atau endpoint PATCH status; tidak ada route destroy/DELETE. Menonaktifkan atau mengedit master tidak mengubah record Room, floor_id, atau pivot facility_room. Tidak melakukan cascade status, detach, atau perubahan assignment.
- Semua route memakai auth + access-admin dan model binding ID numerik; record tidak ada menghasilkan 404. Index pagination 20 (lantai diurutkan nomor; fasilitas nama). Form memakai CSRF, validation error, old input, flash, dan submit aktif; tidak ada data bisnis hardcoded di Blade.
- Aktivasi Floor/Facility tidak mengaktifkan Room Management, dropdown pada form Room, PIC, restricted access, Booking, atau Approval. Berhenti setelah Tahap 4C.

## DEC-017 - Backend Room Management (Tahap 4D)

- `Admin/RoomController` mengelola halaman dan status; `SaveRoomRequest` memvalidasi field; `RoomService` menyimpan record dan `facility_room` dalam satu transaksi. Update mengunci record room sampai sync selesai. Kegagalan pivot membatalkan perubahan room dan pivot bersama.
- Nama wajib maksimal 255; kode nullable unique maksimal 255 sesuai constraint existing, update mengecualikan record sendiri; kapasitas integer 0 sampai 2147483647; deskripsi nullable maksimal 5000; lantai wajib ada; access_type hanya all/restricted; status hanya available/maintenance/unavailable; requires_approval dan is_active boolean.
- Fasilitas berupa array ID existing, integer, distinct. Pilihan kosong/checkbox tidak dikirim menjadi array kosong untuk menghapus seluruh fasilitas room. Error validasi mempertahankan pilihan kosong maupun pilihan sebelumnya dari input user, bukan mengembalikan pilihan database. Hanya pivot fasilitas yang disinkronkan.
- Pilihan lantai/fasilitas dari database termasuk record nonaktif dengan label, mengikuti pola master existing dan tanpa kebijakan status master tambahan. Tidak hardcode data ruangan atau daftar fasilitas di Blade.
- Status operasional diubah lewat form edit/update. Endpoint PATCH status hanya mengubah is_active. Tidak ada endpoint DELETE. Status aktif dan operasional merupakan field terpisah.
- Index pagination 20 dan eager-load lantai/fasilitas. Detail menampilkan semua field dan relasi existing. Tujuh route Room Management memakai auth + access-admin dan model binding numerik/404. Halaman user/PIC tetap skeleton.
- PIC dan restricted unit access belum diimplementasikan: pivot room_pics dan room_unit_access tetap utuh meskipun access_type/requires_approval berubah; belum ada validasi minimal PIC/unit. Dua halaman kelola terkait tetap skeleton. Tautan kembali diarahkan ke daftar room supaya tidak menuju detail preview yang sekarang 404. Berhenti setelah Tahap 4D.
