# Progress

## Last Update

2026-09-28 - BookingRoomsSeeder didaftarkan ke DatabaseSeeder.

- Seeder utama menjalankan FoundationSeeder lalu BookingRoomsSeeder agar PIC awal tersedia sebelum katalog ruangan diisi. Perintah db:seed kini mencakup keduanya.
- Fixture dasar test tetap memanggil FoundationSeeder secara eksplisit; test katalog menjalankan DatabaseSeeder dua kali untuk memeriksa urutan dan idempotensi.
- Verifikasi akhir: `php artisan test --compact --filter="DatabaseFoundationTest|BookingWorkflowTest"` berhasil, **24 passed, 253 assertions**. Run awal memiliki satu kegagalan pada fixture rollback sebelum penyesuaian fixture dimuat ulang. Database aplikasi tidak di-seed dalam pekerjaan ini.

## Riwayat sebelum pendaftaran seeder ruangan

2026-09-28 - Verifikasi untuk publikasi perubahan booking, jadwal, dan tampilan PIC/admin.

- Seluruh suite terakhir: **143 passed, 2550 assertions** (`php artisan test --compact`). Build Vite dan kompilasi Blade terakhir berhasil pada verifikasi perubahan UI. Staged diff check bersih.
- Publikasi ke origin/main atas permintaan pengguna. File .env, dependency, dan hasil build tidak disertakan. Perubahan existing berupa baris kosong akhir migration lantai tetap lokal.

## Riwayat sebelum verifikasi publikasi

2026-09-28 - Kapasitas dihapus dari UI dan label administratif menjadi Admin (DEC-033).

- Kapasitas dihapus dari katalog/kartu/detail ruangan, form tambah/edit, ringkasan booking dan panduan dashboard. Form simpan menerima tanpa kapasitas: create default 0, edit mempertahankan nilai lama; kolom database tidak dihapus.
- roleLabel memetakan identifier super_admin menjadi Admin pada header, ringkasan akun, tabel/detail user dan PIC. Judul dashboard, opsi role, nama akun demo untuk seeding baru, serta penyebutan dokumentasi menggunakan Admin. Nama akun existing dan identifier database tetap dipertahankan.
- Run suite awal: 140 passed, 2 failed karena assertion label role lama. Assertion disesuaikan ke label tampilan dan record fresh setelah perubahan role. Verifikasi ulang seluruh kelompok yang berubah terakhir: **51 passed, 1164 assertions** (AuthenticationTest, UserManagementTest, RoomManagementTest, AuthorizationTest, FrontendSkeletonTest). Test tambahan membuktikan create/edit tanpa kapasitas berhasil dan nilai lama terjaga.
- Build Vite, view:cache/view:clear dan diff check berhasil. Pencarian kode view tidak menemukan field kapasitas atau label Super Admin. Browser visual desktop/mobile belum diuji langsung karena tool browser tidak tersedia.

## Riwayat sebelum penghapusan kapasitas

2026-09-28 - Tampilan Kelola PIC admin di `/admin/rooms/{id}/pics`.

- Mengganti layout base dengan layout schedule, heading/breadcrumb dan ringkasan room. Panel pilihan checkbox menggantikan multi-select agar penugasan beberapa PIC mudah digunakan di desktop/mobile; panel PIC tersimpan menampilkan nama/email/role/status serta form Lepas PIC. Ada empty state, bantuan penugasan, Simpan, Batal, dan Kembali ke Detail Ruangan.
- Route, field pic_ids[], CSRF, PUT/DELETE, old input (termasuk kosong), validasi/flash dan backend assignment tetap dipertahankan. Error grup terhubung ke fieldset; akun nonaktif diberi label. Test selected option disesuaikan menjadi checked checkbox.
- Verifikasi aktual: **24 passed, 581 assertions** pada RoomAssignmentTest, AuthorizationTest, FrontendSkeletonTest. Build Vite, view:cache/view:clear, dan diff check berhasil. Responsivitas, label, fokus keyboard, error dan empty state ditinjau dari kode; visual desktop/mobile serta interaksi keyboard browser belum diuji karena tool browser tidak tersedia.

## Riwayat sebelum tampilan Kelola PIC

2026-09-28 - Tampilan Ruangan Saya PIC aktif di `/pic/rooms`.

- Mengganti tabel skeleton dengan layout schedule, judul/breadcrumb, menu aktif, ringkasan jumlah penugasan, kartu responsif, empty state, pagination 9 room, serta tautan Permintaan Approval dan jadwal per room. Memakai partial kartu existing tanpa aksi booking pribadi.
- Pic/RoomController membaca managedRooms milik akun login dengan eager loading lantai/fasilitas. Room nonaktif/perawatan tetap tampil dengan status jelas. Tidak mengubah penugasan/master data atau Gate; admin yang membuka URL ini juga hanya melihat penugasan miliknya.
- Kartu menampilkan nama, lantai, kapasitas, deskripsi, fasilitas, akses dan aturan approval. Pintasan dashboard terkait tidak lagi berlabel pratinjau. Tersisa satu route skeleton: detail room umum.
- Verifikasi aktual: **40 passed, 641 assertions** pada PicRoomsTest, AuthorizationTest, FrontendSkeletonTest, RoomCatalogTest, HomeAndScheduleTest. Mencakup scope penugasan, pencabutan assignment, escaping, room nonaktif/perawatan, pagination, role, empty state, dan regresi kartu/jadwal.
- Build Vite berhasil; view:cache/view:clear dan diff check berhasil. Layout grid, wrapping teks, fokus tautan, label status dan empty state diperiksa dari kode. Visual desktop/mobile dan keyboard browser belum diuji langsung karena tool browser tidak tersedia.

## Riwayat sebelum tampilan Ruangan Saya

2026-09-28 - Menu booking pribadi di tampilan PIC dihapus sesuai permintaan pengguna.

- Menghapus Booking Ruangan/My Booking dari sidebar PIC, navigasi fallback, pintasan bersama, dan panel booking pribadi dashboard PIC. Tombol Booking Ruangan pada jadwal hanya tampil untuk pegawai. Dashboard, Approval, Ruangan Saya, Riwayat Approval, dan Jadwal PIC tetap tersedia.
- Perubahan hanya presentasi navigasi; route/Gate akses pegawai yang diwarisi PIC tetap sesuai keputusan existing. Menu pegawai dan administrasi ruangan admin tetap tersedia.
- Verifikasi akhir: **15 passed, 439 assertions** (AuthorizationTest dan FrontendSkeletonTest), termasuk ketiadaan kedua menu pada dashboard/ruangan/approval/riwayat/jadwal PIC. Run pertama menemukan pembungkus kondisi terlalu luas yang menyembunyikan menu Dashboard; sudah diperbaiki dan semua test ulang lulus.
- Build Vite, view:cache/view:clear, dan diff check berhasil. Desktop/mobile memakai sidebar bersama; visual dan keyboard browser belum diuji langsung karena tool browser tidak tersedia.

## Riwayat sebelum perubahan menu PIC

2026-09-28 - Jadwal Ruangan pada beranda sebelum login (DEC-031).

- Bagian Kenali pilihan ruangan/kartu di `/` diganti kalender dan jadwal harian yang memakai view pegawai. Navigasi/CTA menjadi Jadwal Ruangan; booking tetap melalui login.
- PublicRoomSchedule memakai ulang navigasi bulan, tanggal, filter, warna status, dan refresh RoomSchedule. Pilihan dan booking dibatasi room aktif akses all pada setiap query. Query katalog HomeController dihapus; Gate kalender internal tetap berlaku.
- Verifikasi aktual: **33 passed, 596 assertions** pada HomeAndScheduleTest, FrontendSkeletonTest, AuthorizationTest, dan ExampleTest. Mencakup privasi, escaping, empty state, navigasi/filter tamu, penolakan filter restricted, dan perubahan akses/nonaktif saat refresh.
- `npm run build` berhasil setelah eskalasi esbuild spawn EPERM; `php artisan view:cache`, `view:clear`, dan diff check file task berhasil. Layout responsif, kalender scroll lokal, label, tombol keyboard, loading/offline/error memakai view bersama yang ditinjau dari kode. Visual desktop/mobile dan keyboard di browser belum diuji karena tool browser tidak tersedia.

## Riwayat beranda sebelumnya

2026-09-27 - Beranda tamu dan kalender/jadwal room terhubung ke booking.

## Beranda Publik dan Jadwal Nyata

- Review tamu di `/`: layout/gaya dashboard pegawai, Login, pengantar, kartu room publik dari database dengan pagination, empty state. Tombol Login untuk Booking membawa pilihan room melalui session; login user/PIC melanjutkan form, admin ke dashboard role. Tamu tidak melihat jadwal/agenda/pemohon. Kartu bersama diekstrak menjadi partial; komponen katalog pegawai tetap dilindungi Gate.
- Review `/schedule` atau `/admin/schedule`: kalender server-rendered Livewire, navigasi bulan/Hari Ini/lompat tanggal, filter room, total booking dan warna per tanggal. Daftar harian menampilkan nama room dan jam mulai-selesai WIB, dengan label Pending/terjadwal/berlangsung/lewat. Pending menahan slot; Rejected/Cancelled dikecualikan. Auto refresh 30 detik, manual refresh, offline note, serta kondisi room nonoperasional ditampilkan.
- Di form booking, panel Jadwal Ruangan Ini berada di atas Detail Pertemuan. Field tanggal bersama memakai Livewire dan memperbarui daftar jam room terpilih. Kalender lengkap terbuka tab baru; detail booking mempunyai tautan ke kalender pada tanggal/room terkait. Tidak membuka identitas/agenda/catatan rapat lain. Tidak mengubah validasi dua jam, otorisasi, idempotensi, atau transaksi konflik saat submit.
- Verifikasi aktual: **78 passed, 1037 assertions** pada AuthenticationTest, AuthorizationTest, BookingPreparationTest, BookingWorkflowTest, FrontendSkeletonTest, HomeAndScheduleTest, RoomCatalogTest, serta ExampleTest. Termasuk 15 test baru: home/privasi/escaping, login continuation/gagal/admin/room berubah, query invalid, scope/filter kalender, tahun baru/tahun kabisat, WIB dan batas tepat mulai/akhir, pembaruan status, authorization saat refresh, dan pergantian tanggal pada form. Test skeleton disesuaikan karena jadwal sudah aktif (tersisa dua skeleton). Run awal gagal pada hitungan skeleton lama dan satu assertion jam yang juga muncul sebagai batas dua jam; coverage dipindah ke isi daySlots dan seluruh test terakhir lulus.
- Build Vite berhasil setelah eskalasi esbuild spawn EPERM. `php artisan view:cache`, `view:clear`, dan diff check file task berhasil. Responsivitas, labels/error, keyboard links/buttons, kontras token, empty/loading/offline state ditinjau dari kode; visual browser desktop/mobile serta interaksi keyboard aktual belum diuji karena tool browser tidak tersedia.
- Verifikasi read-only pada database lokal: **1 booking tersimpan pada 2026-09-29**, dan BookingSchedule membaca **1 slot** pada tanggal tersebut. Tidak membuat/menghapus/mengubah booking atau data room lokal.
- DEC-030 dan panduan diperbarui. Calendar backend aktif; notifikasi, statistik dashboard, pembatalan/reschedule, dan lifecycle otomatis tetap di luar task. Perubahan migration create_floors_table existing tidak disentuh.

## Riwayat sebelum beranda dan kalender aktif

2026-09-27 - Sepuluh ruangan lokal dan booking/pengajuan serta keputusan PIC aktif.

## Booking Aktif dan Data Ruangan

- Pengguna meminta room menurut lantai dan booking/pengajuan berfungsi. Rincian 1+2+2+2+3 menghasilkan 10; klarifikasi angka 9/nama/PIC belum dijawab, maka mengikuti rincian dengan asumsi disampaikan. Nama sementara: Ruang Rapat Lantai 2 - 1; Lantai 3 - 1/2; Lantai 4 - 1/2; Lantai 6 - 1/2; Selat Malaka I/II/III di lantai 7. Kapasitas/fasilitas belum diisi. Tujuh room terbuka tanpa approval; tiga Selat Malaka terbuka untuk pengajuan dengan approval PIC existing Room PIC Demo.
- Migration bookings dan BookingRoomsSeeder **sudah dijalankan pada database lokal**, tanpa fresh/reset. Verifikasi database: 10 room aktif, pembagian lantai sesuai rincian, tujuh requires_approval=false dan tiga true, semua access_type=all. Empat room demo lama nonaktif tetapi tidak dihapus; role/password/akun existing tidak diubah. Migration create_floors_table milik pengguna tidak disentuh.
- Tombol form Livewire aktif: Booking Sekarang langsung Approved atau Ajukan Booking menjadi Pending. Pemeriksaan dan submit memvalidasi server minimal dua jam WIB, urutan waktu, akses/kondisi room, serta bentrok Pending/Approved. Pengajuan berhasil menuju detail record nyata. Lock room/transaksi dan token idempotensi mencegah overlap serta retry ganda. Unit disimpan sebagai snapshot nama.
- My Booking hanya milik pemohon; admin melihat Semua Booking; PIC melihat pengajuan room penugasannya dan dapat approve/reject dengan alasan wajib untuk penolakan. Admin tidak diberi override. Keputusan kedua ditolak, penolakan melepas slot, keputusan dicatat beserta PIC/waktunya. List memakai eager loading/pagination dan shared table/detail/status.
- Verifikasi aktual: **123 passed, 2448 assertions** (`php artisan test`). Termasuk 13 BookingWorkflowTest: submit Livewire/redirect/detail nyata, dua jam saat submit, overlap/bersebelahan, Pending/reject/approve, retry idempotent, privasi, assignment PIC, CSRF approval/Livewire, snapshot unit, seeder berulang/distribusi/booking tiap room, dan dua proses bersamaan dengan schema PostgreSQL terisolasi. Tidak meninggalkan booking test pada database lokal.
- Build Vite berhasil setelah eskalasi karena esbuild spawn EPERM. `php artisan view:cache` dan `view:clear` berhasil. Review responsivitas, label/error, status, empty state/loading dilakukan dari kode; visual browser/keyboard aktual belum dilakukan karena tool browser tidak tersedia.
- Review aplikasi: `/rooms`, klik kartu, isi waktu minimal dua jam ke depan; hasil di `/my-bookings`. Ruang Selat Malaka masuk `/pic/approvals` bagi PIC penugasan; history di `/pic/approvals/history`. Admin memantau `/admin/bookings`.
- Batas: belum ada pembatalan/reschedule, Pending expiry/auto Completed, notifikasi, kalender nyata, atau statistik dashboard. Kartu status operasional tidak berarti slot waktu tersedia. Seluruh keputusan implementasi dan asumsi data ada pada DEC-029.

## Riwayat sebelum aktivasi booking

2026-09-27 - Perbaikan UX pilih kartu ruangan dan pemeriksaan rencana minimal dua jam.

## Alur Katalog ke Form Booking (Koreksi Pengguna)

- Review: `/dashboard` berisi tiga kartu ringkasan; `/rooms` katalog lengkap/filter/pagination dengan satu menu Booking Ruangan; klik kartu yang dapat dipilih menuju `/my-bookings/create?room_id={id}`. Seluruh area kartu dapat diklik dengan tautan keyboard berlabel. Form menampilkan identitas/fasilitas ruangan, tanpa dropdown. `/my-bookings` khusus pengajuan/riwayat dengan empty state dan tautan booking baru.
- Menambah controller persiapan, komponen Livewire BookingPreparationForm, service BookingPreparation, dan config booking. RoomCatalog dipakai ulang untuk dashboard/katalog. Tidak menambah dependency, migration, query di Blade, penyimpanan booking, atau backend approval/kalender.
- Periksa Rencana aktif untuk validasi server: minimal dua jam dari waktu pemeriksaan, tanggal/jam valid, akhir setelah awal pada hari yang sama, agenda wajib/batas panjang. WIB ditampilkan konsisten; batas presisi menit dibulatkan naik. ID ruangan dikunci; status/akses terkini diperiksa ulang. Isian bertahan ketika validasi gagal; pesan pemeriksaan lama disembunyikan saat input berubah. Pengajuan tetap nonaktif dan tidak memberi kesan ruangan telah dipesan.
- Room nonaktif/perawatan/unit tidak berhak tidak dapat dipilih; akses langsung menampilkan alasan. Query ID invalid/array/terlalu besar dan ID tidak ditemukan menghasilkan 404; tanpa ID kembali ke katalog. PIC mengikuti akses pegawai; admin hanya membaca katalog tanpa hak pengajuan pribadi.
- Verifikasi aktual: **32 passed, 944 assertions** (BookingPreparationTest, FrontendSkeletonTest, AuthorizationTest, RoomCatalogTest). Termasuk tepat/kurang dua jam, detik, tanggal lampau/tidak valid, pergantian hari WIB, form lama, perubahan kondisi/akses room, pemalsuan ID Livewire, dan pemisahan katalog/My Booking. Run awal menemukan satu tautan preview menuju form tanpa ID (302); tautan diperbaiki dan seluruh test ulang lulus.
- `npm run build` berhasil setelah eskalasi esbuild spawn EPERM; `php artisan view:cache` dan `view:clear` berhasil. Responsivitas grid, label/error, keyboard link/focus, offline/loading, dan empty state ditinjau dari kode. Browser visual desktop/mobile dan interaksi keyboard aktual belum diuji karena tool browser tidak tersedia.
- Risiko yang belum selesai dicatat dalam DEC-028: bentrok dan transaksi booking bersamaan, pengiriman ulang, perubahan izin/status setelah booking. Harus ditangani saat backend submit diaktifkan; pemeriksaan rencana saat ini tidak menjamin ketersediaan.
- Perubahan existing migration create_floors_table tetap tidak disentuh.

## Riwayat perubahan sebelumnya

2026-09-27 - Penyederhanaan booking user: tanpa jumlah peserta dan halaman Cari Ruangan.

## Booking Ruangan User

- Menghapus halaman/route Cari Ruangan beserta seluruh tautannya; URL lama `/rooms/search` menghasilkan 404 dan tidak ditangkap detail ruangan. Navigasi, shortcut PIC, dan tombol jadwal menuju `/my-bookings/create`.
- Menu Booking Ruangan tersedia di navbar user dan tombol dashboard. Form mengikuti layout user/schedule, card workspace, grid responsif, label/error/old input, CSRF, dan penanda pratinjau. Menu aktif dibedakan dari My Booking. Submit tetap nonaktif karena backend tahap 5 belum tersedia.
- Jumlah peserta dihapus dari form, detail bersama, tabel approval admin/PIC, dan panduan dashboard. Kapasitas master ruangan tetap. Konteks dan DEC-027 diperbarui.
- Verifikasi aktual: FrontendSkeletonTest, AuthorizationTest, RoomCatalogTest **21 passed, 935 assertions**. Build Vite berhasil setelah eskalasi karena sandbox memblokir esbuild (spawn EPERM). `php artisan view:cache` dan `view:clear` berhasil. Desktop/mobile, label/error, fokus, dan empty state ditinjau dari kode; pengujian visual browser/keyboard aktual belum dilakukan karena tool browser tidak tersedia.
- Diff check file task berhasil; diff check global masih melaporkan blank line EOF pada perubahan existing migration create_floors_table yang tidak disentuh.

## Pembaruan sebelumnya

2026-09-24 - Verifikasi publikasi frontend admin dalam commit terpisah ke origin/main.

## Verifikasi Publikasi Frontend Admin

- Permintaan pengguna: push ke GitHub dengan commit terpisah. Pembagian: komponen/CSS dan navigasi bersama; kartu daftar ruangan; detail/edit ruangan; pratinjau booking/approval; Kelola User; Unit Organisasi; Fasilitas; perubahan Dashboard PIC; dokumentasi. Perubahan Dashboard PIC yang sudah ada berupa komentar Blade untuk menyembunyikan tombol approval pada banner, disertakan tersendiri tanpa mengubah isinya.
- `git fetch origin` berhasil setelah eskalasi izin metadata .git. Sebelum commit, main lokal sama dengan origin/main (0 ahead, 0 behind), tidak ada staged change dari pengguna. `git diff --check` berhasil. Pengujian lengkap `php artisan test`: **99 passed, 2620 assertions**.
- Build Vite dan view:cache/view:clear sudah berhasil pada verifikasi terakhir Detail/Edit Ruangan; tidak diulang karena persiapan publikasi hanya dokumentasi dan pengelompokan commit. Tidak memasukkan .env, vendor, node_modules, atau public/build. Target publikasi: origin/main pada repository ahsanul22/spmt-booking-room; push biasa tanpa force.

## Frontend Detail dan Edit Ruangan Admin

- Review melalui tombol Detail/Edit pada `/admin/rooms`, atau `/admin/rooms/{id}` dan `/admin/rooms/{id}/edit` dengan ID existing. Keduanya memakai layout schedule, breadcrumb sesuai halaman, heading, dan komponen workspace dari administrasi.
- Detail menampilkan ringkasan identitas/kode, lantai, kapasitas, kondisi operasional, deskripsi, seluruh fasilitas, PIC, serta unit akses. Badge aktif/nonaktif tersedia untuk record terkait. Akses all tetap menjelaskan bahwa unit tersimpan tidak dipakai; daftar restricted hanya pilihan eksplisit. Edit, Kelola PIC/Akses, Kembali, jadwal pratinjau, dan form status existing tetap aktif.
- Edit memakai partial `_edit-form.blade.php` khusus halaman yang ditugaskan: Informasi Dasar, checkbox Fasilitas, serta Akses dan Operasional. Label/pesan error memakai workspace-field; error array fasilitas ditampilkan dan dihubungkan ke fieldset. Seluruh nama/value field, old input, required/batas input, method PUT, CSRF, dan submit tetap. Pilihan fasilitas kosong saat validasi tidak mengembalikan pilihan lama. Form tambah dan shared room-details untuk role lain tetap existing.
- Tidak mengubah controller, query, service, authorization, schema, atau dependency. Tidak mengaktifkan backend booking/approval/jadwal. Status operasional tidak dinyatakan sebagai ketersediaan jadwal.
- Verifikasi aktual: RoomManagementTest + RoomAssignmentTest + FrontendSkeletonTest **24 passed, 1117 assertions**. `npm run build` berhasil setelah eskalasi karena esbuild diblokir sandbox (spawn EPERM); `php artisan view:cache`, `view:clear`, dan `git diff --check` berhasil. Layout desktop/mobile, label/fokus, status, empty state, dan error ditinjau dari kode. Visual browser, keyboard aktual, serta pengukuran kontras belum diperiksa karena tool browser tidak tersedia. Tidak ada keputusan arsitektur/bisnis baru.

## Penyempurnaan Kartu Daftar Ruangan Admin

- Review di `/admin/rooms`. Kartu dipisahkan ke `admin/rooms/_card.blade.php`: header beraksen biru lembut, identitas/kode ruangan, badge aktif, lantai, angka kapasitas yang dominan, label kondisi operasional, akses, dan kebutuhan approval. Kartu putih langsung di atas background halaman dengan border, shadow, dan grid 1/2/3 kolom sesuai lebar layar.
- Fasilitas berasal dari relasi existing, tiga ditampilkan langsung dan sisanya melalui disclosure native `details/summary` dengan fokus keyboard. Nama panjang membungkus. Tidak memakai gambar ruangan dummy, data hardcoded, query Blade, atau perubahan controller/dependency.
- Detail menjadi aksi utama dan Edit menjadi aksi sekunder; footer menampung Kelola PIC, Kelola Akses, dan aktivasi/nonaktivasi. Form status mempertahankan POST/PATCH, CSRF, dan nilai boolean, dengan tambahan label aksesibel berisi nama ruangan. Pagination, flash/error, dan seluruh route tetap. Status operasional tetap tidak dianggap sebagai ketersediaan jadwal.
- Verifikasi aktual: RoomManagementTest + RoomAssignmentTest + FrontendSkeletonTest **24 passed, 1117 assertions**. `npm run build` berhasil setelah eskalasi karena sandbox menolak esbuild (spawn EPERM). `php artisan view:cache`, `view:clear`, dan `git diff --check` berhasil. Responsivitas, fokus/label, disclosure fasilitas, empty state, dan status ditinjau dari kode; visual browser desktop/mobile dan keyboard aktual belum diuji karena tool browser tidak tersedia. Tidak ada keputusan arsitektur/bisnis baru.

## Frontend Administrasi User, Unit Organisasi, dan Fasilitas

- Review sebagai Admin di `/admin/users`, `/admin/organizational-units`, dan `/admin/facilities`, termasuk halaman tambah, edit, dan detail masing-masing. Mengikuti layout Jadwal Ruangan/dashboard dengan sidebar aktif, breadcrumb, heading, card putih, tabel yang dapat digeser lokal, empty state, dan pagination. Jumlah record berasal dari paginator Laravel.
- Form aktif mempertahankan action, method, CSRF, nama field, pilihan database, old input, dan aturan validasi. Detail User menyertakan reset password; detail Unit menampilkan unit induk dan turunan; detail Fasilitas menampilkan deskripsi serta aksi status. Password tidak diisi ulang. Tidak mengubah controller, service, route, schema, dependency, atau otorisasi.
- Menambah komponen `workspace-field`, `workspace-panel`, dan `workspace-status`, memakai heading/empty state existing. Input/select/textarea memiliki label, penanda wajib, aria-invalid, pesan error per field yang terhubung melalui aria-describedby, serta fokus keyboard. Partial feedback User/Unit memakai `form-feedback` existing. CSS baru dibatasi class workspace; komponen field/select/textarea lama untuk modul lain tetap.
- Permintaan penghapusan Lantai diterapkan pada menu administrasi di layout schedule dan navigation lama, serta shortcut dashboard admin. Karena konteks task frontend dan floor_id masih dibutuhkan ruangan, asumsi yang disampaikan selama klarifikasi belum dijawab adalah menghapus navigasi saja. Data, model, route/backend Lantai, dan atribut lantai pada ruangan tetap; tidak menghapus data atau mengubah schema. Cakupan ini dicatat di DEC-026.
- Verifikasi aktual: UserManagementTest + OrganizationalUnitTest + FloorFacilityManagementTest + FrontendSkeletonTest + AuthorizationTest **46 passed, 1835 assertions**. Mencakup penyimpanan, validasi/old input, reset password/login, status, hierarki unit, relasi ruangan, CSRF, escaping, dan matriks akses. `npm run build` berhasil setelah eskalasi karena esbuild ditolak sandbox (spawn EPERM). `php artisan view:cache`, `view:clear`, dan `git diff --check` berhasil.
- Responsivitas layout/form, label input, fokus, empty state, dan error diperiksa dari kode. Visual desktop/mobile, interaksi keyboard aktual, serta pengukuran kontras browser belum diuji karena tool browser tidak tersedia. Perubahan existing Dashboard PIC tidak disentuh. Backend Booking/Approval/Kalender tetap pada tahap sebelumnya.

## Frontend Daftar Ruangan, Semua Booking, dan Approval Admin

- Review sebagai Admin di `/admin/rooms`, `/admin/bookings`, dan `/pic/approvals` (URL existing menu Approval Ruangan). Mengikuti layout Jadwal Ruangan/dashboard: sidebar, breadcrumb dan menu aktif, heading, card putih, token warna, serta mobile navigation existing.
- Daftar Ruangan memakai kartu responsif berisi data Laravel: nama/kode, lantai, kapasitas, fasilitas, akses, kebutuhan approval, status operasional dan aktif. Navigasi tambah/detail/edit/PIC/akses, form status POST/PATCH + CSRF, flash/error, serta pagination tetap aktif. Status operasional tidak menyatakan ketersediaan jadwal. Halaman form/detail di luar tiga halaman daftar tetap existing.
- Semua Booking memakai panel filter berlabel, aksi filter nonaktif, tautan pratinjau detail, dan tabel saat data tersedia. Approval admin memakai partial terpisah dengan daftar permintaan, empty state, dan panduan alur. Cabang PIC tetap memakai tampilan existing; akses admin ke route PIC tidak menambah kewenangan override. Tidak menambah backend Booking/Approval, route, dependency, query Blade, atau data dummy.
- Komponen `workspace-heading` dan `workspace-empty` dipakai ulang. CSS form/tabel/feedback dibatasi class workspace; layout schedule hanya disesuaikan penanda menu aktif. Empty state berada di luar tabel agar terbaca utuh pada mobile; tabel berisi data dapat digeser lokal dengan fokus keyboard. Perubahan Dashboard PIC yang sudah ada di workspace tidak disentuh.
- Verifikasi aktual: RoomManagementTest + RoomAssignmentTest + FrontendSkeletonTest + AuthorizationTest **33 passed, 1395 assertions**. Sesudah perapian empty state mobile, satu run menemukan tiga kegagalan rendering akibat pasangan directive Blade; diperbaiki, lalu RoomManagementTest + FrontendSkeletonTest + AuthorizationTest **24 passed, 1216 assertions**. Tidak ada kegagalan tersisa pada run terakhir.
- `npm run build` berhasil setelah eskalasi karena sandbox menolak esbuild dengan spawn EPERM. `php artisan view:cache` dan `view:clear` berhasil, termasuk setelah perbaikan Blade; `git diff --check` berhasil. Perubahan terakhir tidak menambah utility CSS. Responsivitas, label, fokus, empty state, dan error ditinjau dari kode; visual desktop/mobile, keyboard aktual, dan pengukuran kontras browser belum diuji karena tool browser tidak tersedia. Tidak ada keputusan arsitektur/bisnis baru.

## Verifikasi Sebelum Push

- Permintaan pengguna: push perubahan ke repository dengan commit terpisah. Kelompok commit: dashboard/controller admin dan layout bersama; desain login; komponen/dependency katalog Livewire; dashboard user/PIC dan navbar beserta pengujian; dokumentasi/panduan frontend.
- Review menemukan typo existing pada constraint route lantai (`flyangoor`), menyebabkan `/admin/floors/preview` menghasilkan 500. Dikembalikan ke `floor`; test FloorFacilityManagementTest **12 passed, 365 assertions**. Pengujian lengkap sesudah perbaikan: **99 passed, 2631 assertions**. Pengujian lengkap awal gagal pada satu kasus route tersebut; tidak ada kegagalan tersisa pada pengujian ulang.
- `git fetch origin` berhasil; sebelum commit, main lokal sama dengan origin/main. `git diff --check` berhasil. Build terakhir dan kompilasi Blade berhasil pada verifikasi UI sebelumnya; tidak diulang karena perubahan publikasi hanya perbaikan route dan dokumentasi. Tidak menyertakan .env, node_modules, vendor, atau public/build. Publikasi ditujukan ke origin/main sesuai instruksi pengguna.

## Dashboard User dan Katalog Livewire

- Atas koreksi pengguna, ketiga tombol filter dikembalikan rata kiri dengan menghapus `justify-center`. RoomCatalogTest **6 passed, 42 assertions**; diff check file berhasil. Build tidak diulang karena hanya menghapus class alignment; seluruh utility tersisa sudah tersedia pada build sebelumnya.

- Penyesuaian lanjutan: ketiga tombol filter dipusatkan dengan `justify-center`, tetap membungkus pada mobile dan mempertahankan garis bawah aktif. Build berhasil (eskalasi esbuild EPERM), RoomCatalogTest **6 passed, 42 assertions**, serta view:cache/view:clear berhasil. Visual browser belum diperiksa karena tool browser tidak tersedia.

- Container layout user diperlebar dari 1280 menjadi maksimum 1800 px; dashboard PIC/admin pada layout schedule juga maksimum 1800 px, sementara jadwal PIC/admin tetap 1600 px. Navbar, main, dan footer user sejajar. Pengantar user memakai minimum 58vh, headline besar, penjelasan sistem, serta tiga langkah penggunaan; katalog langsung berada setelah pengantar.
- Atas permintaan eksplisit pengguna, menambah `livewire/livewire` **3.8.9** (constraint `~3.6`, kompatibel Laravel 10) lewat Composer. Lock hanya menambah satu paket; dependency existing tidak di-update. Instalasi memerlukan eskalasi karena koneksi Composer diblokir sandbox. Aset Livewire menggunakan injeksi otomatis resmi, bukan CDN/Alpine tambahan.
- `app/Livewire/RoomCatalog.php` dan `resources/views/livewire/room-catalog.blade.php` membaca ruangan aktif beserta lantai/fasilitas dari database saat halaman pertama dibuka, tanpa lazy loading. Tiga tombol filter tanpa latar warna memakai garis bawah dan aria-pressed; filter/pagination memperbarui kartu melalui Livewire. Sembilan kartu per halaman, state loading/offline/empty, focus keyboard, serta tampilan 1/2/3 kolom tersedia.
- Belum ada kategori pada schema. Pertanyaan klarifikasi dikirim; selama belum ada jawaban, asumsi sementara adalah Selat Malaka dicocokkan dengan nama (case-insensitive, trim), Ruang Rapat Lainnya adalah sisanya. Nama khusus disimpan pada `config/room-catalog.php`; tidak hardcode ID, data kartu, atau membuat kategori baru. Jika nama ruangan berubah, konfigurasi perlu disesuaikan.
- Katalog hanya informasi: ruangan nonaktif disembunyikan; maintenance/unavailable tetap diberi label operasional yang benar. Akses restricted dilabeli Unit tertentu, bukan klaim user berhak booking. Pengecekan jadwal, inheritance akses, pengajuan, dan approval belum diimplementasikan; tombol booking nonaktif dan penjelasan terlihat. Tidak mengubah migration, seeder, atau data development.
- Gate access-employee diperiksa setiap request komponen, properti filter Locked, pilihan action whitelist, relasi eager-loaded, output Blade escaped. Menambah enam RoomCatalogTest: data awal/escaping/nonaktif, ketiga filter dan empty, pagination/reset, input invalid, properti terkunci, authorization awal/lanjutan.
- Hasil aktual: RoomCatalogTest + AuthenticationTest + AuthorizationTest + FrontendSkeletonTest **37 passed, 1153 assertions**. `npm run build` berhasil setelah eskalasi esbuild EPERM; `view:cache`, `view:clear`, dan Pint empat file PHP terkait berhasil. Browser visual/interaksi desktop-mobile belum diuji karena tool browser tidak tersedia; responsivitas dan keyboard ditinjau pada kode, filter diuji melalui Livewire test.
- `composer audit --locked`: tiga entri advisory pada laravel/framework existing (signed URL dan aturan validasi email), tidak ada advisory Livewire pada hasil audit. Tidak menaikkan Laravel di task UI ini; pembaruan framework memerlukan pekerjaan terpisah. Review halaman di `/dashboard` sebagai pegawai.

## Revisi Alur Dashboard dan Navbar Pegawai

- Mengoreksi desain sebelumnya sesuai arahan pengguna: referensi dashboard admin berlaku untuk gaya visual, bukan penyamaan susunan konten dan navigasi seluruh role. Dashboard PIC mendahulukan Permintaan Approval/antrean tinjauan, Ruangan Saya dan jadwal, lalu Riwayat Approval. Booking pribadi menjadi akses sekunder. Dashboard pegawai mendahulukan Cari Ruangan dan Booking Saya; penjelasan sistem ringkas dan panduan rinci dapat dibuka melalui disclosure native.
- `dashboard.blade.php` menjadi wrapper bersama yang memilih konten `user/dashboard.blade.php` atau `pic/dashboard.blade.php` sesuai Gate. Identitas akun dipakai ulang melalui `shared/dashboard-account.blade.php`. Controller, route, autentikasi, dan authorization tetap; akses admin ke URL umum/PIC sesuai Gate existing tidak diubah.
- Role user memakai `layouts/user.blade.php`: navbar horizontal yang membungkus pada layar kecil, menu aktif, identitas, skip-link, dan logout POST/CSRF; tanpa sidebar atau ketergantungan JavaScript untuk membuka menu. Layout ini juga dipakai role user pada jadwal, pencarian/daftar/detail ruangan, serta My Booking/form/detail agar navigasi konsisten. Konten modul di luar dashboard tetap existing; CSS `.user-module` menjaga form dan tabel tetap terbaca setelah Tailwind reset, dengan scroll tabel lokal.
- PIC/admin tetap memakai sidebar. Menu PIC pada layout schedule kini mendahulukan permintaan, ruangan tanggung jawab, dan riwayat; menu admin tetap. Statistik, antrean approval, booking pribadi, dan penugasan ruangan pada dashboard tidak dipalsukan. Penanda pratinjau menjelaskan data/proses yang belum tersedia; belum mengaktifkan backend tahap 5-7.
- Hasil aktual: AuthenticationTest + AuthorizationTest + FrontendSkeletonTest **31 passed, 1113 assertions**. Assertion tambahan memeriksa konten sesuai role dan ketiadaan sidebar pada dashboard/jadwal user. `npm run build` berhasil setelah eskalasi spawn EPERM; `view:cache`, `view:clear`, dan Pint test AuthorizationTest berhasil. Tidak menambah dependency.
- Responsivitas navbar yang membungkus, urutan konten, fokus keyboard, disclosure native, target klik, serta empty state ditinjau pada kode. Visual desktop/mobile, keyboard aktual, dan kontras browser belum diuji karena tidak tersedia tool browser dalam sesi. Review di `/dashboard` sebagai pegawai dan `/pic/dashboard` sebagai PIC.

## Frontend Dashboard Pegawai dan PIC

Catatan di bawah adalah implementasi awal, disempurnakan oleh revisi alur dan navbar di atas.

- Review setelah login pegawai di `/dashboard` dan PIC di `/pic/dashboard`. View bersama `resources/views/dashboard.blade.php` kini memakai `layouts.schedule`, mengikuti dashboard admin: sidebar biru gelap, header akun, card putih, spacing, tipografi, dan token Tailwind existing.
- Bagian awal menjelaskan tujuan Booking Room, kebutuhan rapat, serta aturan approval secara umum. Panduan tiga langkah menjelaskan pencarian, pengajuan, dan pemantauan status. PIC mendapat panel penjelasan tanggung jawab serta akses Ruangan Saya, Permintaan Approval, dan Riwayat Approval; pegawai tidak melihat area PIC.
- Partial `shared/dashboard-shortcuts.blade.php` memakai komponen `dashboard-shortcut` existing dengan Gate. Aktivitas booking menampilkan empty state dan penanda pratinjau tanpa statistik/data palsu. Identitas akun/unit, flash, route, controller, authorization, serta logout POST/CSRF tetap memakai Laravel existing. Tidak mengaktifkan backend tahap 5-7.
- Perubahan terbatas pada layout bersama: menu Dashboard aktif pada ketiga route dashboard. Judul/breadcrumb dashboard ditetapkan oleh view sehingga tidak tertinggal Jadwal Ruangan. Struktur konten satu kolom pada mobile, kartu dua kolom mulai sm, panel samping mulai xl; nama/unit panjang memakai break-words pada konten.
- Hasil aktual: AuthenticationTest + AuthorizationTest + FrontendSkeletonTest **31 passed, 1099 assertions**; `npm run build` berhasil setelah eskalasi karena esbuild ditolak sandbox (spawn EPERM); `php artisan view:cache` dan `view:clear` berhasil. Test termasuk matriks akses role, seluruh tautan dashboard, unit kosong, login/logout, dan halaman jadwal/pratinjau. Tidak menambah dependency atau form proses baru.
- Responsivitas, heading, warna, fokus keyboard, empty state, dan escaping ditinjau dari kode. Pemeriksaan visual desktop/mobile, keyboard aktual, serta pengukuran kontras di browser belum dilakukan karena tool browser tidak tersedia dalam sesi.

## Frontend Login

- Review di `/login` sebagai guest. `resources/views/auth/login.blade.php` kini memakai Tailwind/Vite existing, panel merek primaryDark, header surface, kartu form putih, tipografi, border, spacing, dan tombol mengikuti dashboard admin/Jadwal Ruangan. Ikon memakai komponen `schedule-icon` existing; tanpa dependency baru atau perubahan layout halaman lain.
- Desktop memakai dua kolom; mobile menampilkan merek ringkas di atas form. Form memiliki label, autocomplete, fokus keyboard, target tombol/label checkbox yang cukup besar, ringkasan error dan pesan per field terhubung melalui aria-describedby/aria-invalid. Flash status tersedia; email/Remember Me mempertahankan old input dan password tidak diisi ulang. POST login, CSRF, validasi, autentikasi, dan redirect role existing tetap.
- Hasil aktual: `npm run build` berhasil setelah eskalasi karena sandbox memblokir esbuild dengan spawn EPERM; `php artisan view:cache` dan `view:clear` berhasil; `php artisan test --filter=AuthenticationTest` **16 passed, 166 assertions** (termasuk login/logout tiga role, input invalid, CSRF, throttle, akun nonaktif, dan Remember Me).
- Responsivitas, label, fokus, warna, serta kondisi form kosong/error ditinjau pada kode. Pemeriksaan visual desktop/mobile, keyboard aktual, dan pengukuran kontras di browser belum dilakukan karena tool browser tidak tersedia dalam sesi. Halaman login aktif; tidak menambah backend Booking/Approval/Kalender atau data pratinjau.

## Perapian Return View Admin

- Semua route admin kini mengarah ke controller namespace Admin. Menambah `Admin/BookingController` (index/show) dan `Admin/ScheduleController` (index), menggantikan tiga default view pada route booking/jadwal. Controller admin lainnya sudah menangani view masing-masing dan tidak diubah.
- Scope hanya return view: Blade, URL/nama route, authorization, query/proses master data existing tetap. Booking/detail dan jadwal masih pratinjau; belum menambah backend, data, atau model binding booking. FrontendSkeletonController kini menangani 11 route non-admin; total halaman pratinjau yang diuji tetap 14.
- Hasil aktual: FrontendSkeletonTest + AuthorizationTest **15 passed, 935 assertions**; Pint empat file PHP terkait berhasil; route list mengonfirmasi semua action admin ada dalam namespace Admin. Build/browser tidak diulang karena tidak ada perubahan tampilan atau aset.

## Frontend Dashboard Admin

- Verifikasi setelah pemisahan controller admin (2026-09-23): AuthenticationTest + AuthorizationTest **25 passed, 427 assertions**; Pint tiga file PHP terkait berhasil; route list mengonfirmasi `Admin\DashboardController@index` dengan auth + access-admin; `git diff --check` berhasil. Build tidak diulang karena perapian hanya controller/route dan dokumentasi, tanpa perubahan aset atau Blade.
- Review di `/admin/dashboard` setelah login admin. View `admin/dashboard.blade.php` berisi akses cepat master data, tombol Tambah Ruangan aktif, ringkasan/aktivitas booking pratinjau, panel informasi, dan identitas akun dari Laravel. Tidak menambah statistik palsu, query modul, endpoint, dependency, atau backend tahap 5-7.
- Dashboard admin ditangani `Admin/DashboardController::index`: view, judul, dan data akun berada di controller khusus admin. Route `/admin/dashboard` hanya mengarah ke action, tanpa default judul atau pemilihan view berdasarkan nama route. Controller dashboard pegawai/PIC tetap existing. Komponen `dashboard-shortcut` dipakai ulang untuk kartu navigasi. Flash session, unit kosong, escaping, Gate, dan logout POST/CSRF tetap tersedia.
- File bersama `layouts/schedule.blade.php` kini menerima section title/breadcrumb dan menentukan menu aktif berdasarkan route. Default judul tetap Jadwal Ruangan; navigasi, kalender, dan akses role existing dipertahankan.
- Pemeriksaan kode: grid kartu satu kolom di mobile, tiga kolom ringkasan mulai sm, panel samping mulai xl, navigasi mobile existing, fokus keyboard, heading, empty state, dan pembungkusan nama/unit panjang. Tidak ada form input baru; validasi/old input master data tetap di halaman masing-masing. Pemeriksaan visual desktop/mobile, keyboard aktual, dan kontras di browser belum dilakukan karena tool browser/Playwright tidak tersedia dalam sesi.
- Hasil aktual: `npm run build` berhasil (esbuild memerlukan eskalasi setelah sandbox menolak spawn EPERM); `php artisan view:cache` dan `view:clear` berhasil; AuthenticationTest + AuthorizationTest + FrontendSkeletonTest **31 passed, 1098 assertions**. Setelah menambahkan assertion view/pratinjau, test akses admin dijalankan ulang: **1 passed, 30 assertions**. Pint kedua file PHP berubah berhasil. `git diff --check` berhasil, hanya warning normalisasi CRLF/LF pada dokumentasi existing.
- Test navigasi diperbarui untuk membaca anchor halaman saja, melewati skip-link dan aset Vite, mengikuti pola FrontendSkeletonTest. Pengujian awal menemukan syntax Blade pada directive yang menempel teks; diperbaiki sebelum pengujian ulang berhasil. Test database memakai schema PostgreSQL sementara existing.

## Tahap Saat Ini

Verifikasi sebelum publikasi commit terpisah (2026-09-22): `php artisan test` **93 passed, 2573 assertions**. Perubahan dikelompokkan menjadi commit unit organisasi, user, lantai/fasilitas, room, assignment PIC/akses, integrasi route/frontend, desain jadwal Tailwind, dan dokumentasi. Publikasi ditujukan ke origin/main sesuai instruksi pengguna; file .env, node_modules, dan public/build diabaikan Git.

Tahap 1, 2A, 2B, 3, dan 4A-4E selesai. Pengembangan frontend tim aktif per halaman yang ditugaskan, dengan Jadwal Ruangan sebagai acuan utama. Backend tahap 5-7 belum dilanjutkan.

## Pembaruan Aturan Frontend Tim

- AGENTS.md mengganti batasan HTML sederhana untuk testing dengan Blade + Tailwind/Vite dan referensi Jadwal Ruangan. Palet sembilan warna, penggunaan komponen, kontrak form/authorization, dan batas scope frontend dicantumkan.
- Menambah docs/FRONTEND_GUIDE.md: peta file referensi, tabel token warna, pola visual/responsif/aksesibilitas, integrasi HTML dari AI web, langkah CLI, verifikasi, dan contoh instruksi task untuk anggota tim.
- PROJECT_CONTEXT, ROADMAP, FRONTEND, dan DEC-020 diselaraskan. Aturan tahap lama ditandai sebagai riwayat; frontend baru boleh dikembangkan tanpa otomatis melanjutkan backend Booking/Approval/Kalender.
- Perubahan hanya dokumentasi; tidak mengubah kode aplikasi atau dependency. Test aplikasi/build tidak dijalankan karena tidak ada perubahan executable. Verifikasi dokumentasi: konsistensi palet dengan tailwind.config.js, keberadaan file referensi, dan git diff --check.

## Referensi Frontend - Jadwal Ruangan

- `resources/views/shared/schedule.blade.php` menggunakan layout khusus `layouts/schedule.blade.php`: sidebar primaryDark, header akun, judul, kalender bulanan, panel agenda kosong, tautan ruangan, dan footer. Berlaku di /schedule serta /admin/schedule setelah login. Menu mengikuti Gate; mobile memiliki tombol buka/tutup navigasi dan kalender dapat digeser horizontal.
- Palet pengguna dimasukkan dalam tailwind.config.js. Memakai instalasi Tailwind/Vite existing tanpa dependency tambahan, ikon SVG lokal, dan CSS komponen kecil. Halaman lain tetap memakai layout sebelumnya.
- `resources/js/schedule.js`: navigasi bulan, Hari Ini, pilihan tanggal dengan label Indonesia, penanda hari ini, fokus keyboard, serta menu mobile. Tidak memasukkan data booking dummy. Penanda pratinjau menjelaskan agenda belum terhubung dan tanggal kosong bukan bukti ketersediaan.
- Build `npm run build` berhasil setelah sandbox memblokir esbuild (spawn EPERM) dan build dijalankan melalui eskalasi. `node --check resources/js/schedule.js` berhasil. `FrontendSkeletonTest`: **6 passed, 674 assertions**. Test tautan disesuaikan agar hanya mengunjungi anchor navigasi, bukan aset CSS Vite atau fragment skip-link. `view:cache`/`view:clear` berhasil. Smoke check JavaScript dengan DOM stub memverifikasi 42 tanggal, navigasi bulan, pilihan tanggal, dan Hari Ini. Tidak melakukan pengujian browser visual.

## Tahap 4E - Assignment PIC dan Restricted Unit Access (Selesai)

- Melanjutkan dan memeriksa implementasi awal 4E yang sudah tersedia di workspace. `RoomPicController`, `RoomAccessController`, dua Form Request, dan `RoomAssignmentService` mengaktifkan dua halaman pengelolaan dengan data PostgreSQL. Tidak menambah migration/dependency.
- Lima route GET/PUT pics, DELETE pics/{pic}, GET/PUT access memakai auth + access-admin, binding numerik/404, dan CSRF. DELETE hanya melepas assignment pada ruangan terkait. Tersisa 14 route skeleton.
- Form PIC menyimpan beberapa akun role room_pic, menolak role lain/ID tidak ada/duplikat, mempertahankan pilihan, dan dapat mengosongkan semua assignment. Daftar assignment lama tetap terlihat meskipun role akun berubah; pelepasan tidak menghapus akun atau assignment room lain. Rule fondasi diselaraskan ke room_pic sesuai implementasi awal dan UI; alasan dicatat di DEC-018.
- Form unit restricted menyimpan pilihan eksplisit dengan sync, dapat menghapus pilihan, dan menolak ID invalid/duplikat. Tipe all menampilkan daftar tersimpan sebagai tidak digunakan, menyembunyikan form, dan menolak endpoint tulis unit. Mengubah tipe room tidak menghapus pivot. Pewarisan akses dan enforcement booking belum diputuskan/diimplementasikan; teks UI diperjelas agar tidak menyatakan sebaliknya.
- Service mengunci room dalam transaksi; PIC diperiksa ulang dengan shared lock akun. Test memverifikasi rollback saat FK pivot gagal dan memastikan field room, fasilitas, user, unit, serta room lain tetap utuh. Pilihan akun/unit nonaktif diberi label; belum menetapkan minimum PIC/unit.
- `RoomAssignmentTest` berisi 9 test untuk authorization, multi-PIC, validasi, detach scoped, sync unit, tipe all, data tetap utuh, transaksi, 404, escaping, dan CSRF. `FrontendSkeletonTest` memakai 14 route; test rule fondasi mengikuti role yang diterima.
- **Hasil aktual:** `php artisan test`: **93 passed, 2573 assertions**. Pint file PHP terkait berhasil. Route list mengonfirmasi 12 route admin room termasuk lima route assignment, seluruhnya auth + access-admin. `view:cache` berhasil dan cache dibersihkan; `git diff --check` tanpa error whitespace (warning CRLF/LF saja). Pengujian menggunakan schema PostgreSQL sementara, tanpa reset data development; tidak melakukan browser manual.
- Roadmap, DEC-018, dan dokumentasi frontend diperbarui. Room user/PIC, Booking, Approval, kalender, dan notification tetap menunggu tahap berikutnya.
- Setelah perbaikan teks pewarisan akses dan nama test, `php artisan test --filter=RoomAssignmentTest` dijalankan ulang: **9 passed, 179 assertions**.

## Tahap 4D - Backend Room Management (Selesai)

- Menambah `app/Http/Controllers/Admin/RoomController.php`, `app/Http/Requests/SaveRoomRequest.php`, dan `app/Services/RoomService.php`. Memakai model/tabel rooms, floors, facilities, facility_room existing; tidak mengubah migration/dependency.
- `routes/web.php`: tujuh route `admin.rooms.index/create/store/show/edit/update/status`, auth + access-admin, binding ID numerik dan 404; tidak ada DELETE. Empat halaman Room admin dipindahkan dari skeleton, tersisa 16 route skeleton termasuk pics/access.
- Lima Blade room admin (`index`, `create`, `edit`, `show`, `_form`) aktif dan partial `_status` baru. Index pagination 20 menampilkan seluruh field utama serta fasilitas; detail membaca record sesuai ID; pilihan lantai/fasilitas database; form CSRF, validasi, flash, old input, submit aktif. Status operasional melalui edit, aktif/nonaktif lewat edit atau tombol index/detail.
- Validasi kode nullable unique, kapasitas integer nonnegatif sesuai rentang PostgreSQL, lantai/fasilitas existing, fasilitas distinct, enum akses/status, boolean approval/aktif, batas panjang nama/kode/deskripsi. Pilihan master nonaktif ditandai tanpa aturan pembatasan baru.
- Room dan fasilitas disimpan dalam transaksi; update mengunci row room. Sync menambah/menghapus pivot sesuai pilihan; semua fasilitas bisa dikosongkan. Test membuktikan kegagalan foreign key saat sync me-rollback perubahan room dan pivot. Error validasi tidak mengembalikan checkbox lama ketika user sudah mengosongkan semua pilihan.
- Tidak mengubah assignment PIC, pivot restricted unit access, Booking, atau Approval. Detail hanya membaca relasi existing. `pics.blade.php` dan `access.blade.php` hanya diperbaiki tautan kembali ke daftar room karena detail preview kini 404.
- `tests/Feature/RoomManagementTest.php`: 9 test baru untuk guest/user/PIC, create/update, pilihan dan pivot fasilitas, validasi, status operasional/aktif, null code, error/old input, relasi di luar scope tetap utuh, rollback, 404, escaping, dan CSRF. `FrontendSkeletonTest.php` diperbarui untuk 16 route dan form Room aktif.
- Verifikasi memakai schema PostgreSQL sementara melalui `PostgresTestCase`, tanpa reset atau penambahan data uji ke schema development. Tidak melakukan browser manual. Route list mengonfirmasi tujuh route aktif ditambah dua route skeleton pics/access, semuanya auth + access-admin.
- **Hasil aktual akhir:** `php artisan test`: **84 passed, 2460 assertions**, termasuk seluruh 9 test Room Management. Test awal menemukan masalah pilihan fasilitas kosong saat validasi gagal dan test rendering skeleton tanpa middleware session; keduanya diperbaiki dan seluruh suite dijalankan ulang hingga lulus. Pint file PHP terkait berhasil; `view:cache` berhasil, cache dibersihkan; `git diff --check` tanpa error whitespace (hanya warning CRLF/LF).
- Roadmap, keputusan DEC-017, dan dokumentasi frontend diperbarui. Berhenti setelah Tahap 4D.

## Tahap 4C - Backend Floor & Facility Management (Selesai)

- Menambah `app/Http/Controllers/Admin/FloorController.php`, `FacilityController.php`, serta `app/Http/Requests/SaveFloorRequest.php` dan `SaveFacilityRequest.php`. Memakai model/tabel existing tanpa migration/dependency.
- `routes/web.php`: masing-masing tujuh route untuk `admin.floors.*` dan `admin.facilities.*`: index/create/store/show/edit/update/status. Auth + access-admin, ID numerik, binding record/404; tanpa DELETE. Delapan halaman dipindahkan dari skeleton; tersisa 20 route skeleton.
- Sepuluh Blade existing pada `admin/floors` dan `admin/facilities` terhubung ke PostgreSQL; partial `_status` masing-masing dan komponen `form-feedback` baru. Index pagination 20, form create/edit benar-benar menyimpan, detail record sebenarnya, tombol aktif/nonaktif, CSRF, old input, validation error, flash message, tanpa hardcode contoh fasilitas.
- Nama wajib maksimal 255, deskripsi opsional maksimal 5000, status boolean. Nomor lantai integer dalam rentang PostgreSQL dan unique; nama fasilitas unique. Nilai unique milik record sendiri tetap boleh saat edit. Rincian DEC-016.
- Test memastikan create/update/status tersimpan di PostgreSQL dan dibaca kembali di index/detail/edit. Nonaktif/aktif dan edit tidak mengubah record ruangan, relasi Floor-Room, pivot Facility-Room, atau master lainnya. DELETE ditolak dan record tetap ada.
- `tests/Feature/FloorFacilityManagementTest.php` ditambahkan: 12 test (6 skenario untuk masing-masing modul), meliputi guest/user/PIC, create, update, input invalid/duplicate, status, relasi, data existing, error/flash, 404, escaping, dan CSRF. `FrontendSkeletonTest.php` disesuaikan ke 20 route.
- Test khusus: `php artisan test --filter=FloorFacilityManagementTest`: **12 passed, 365 assertions**. Semua memakai schema PostgreSQL sementara melalui `PostgresTestCase`; data development tidak direset atau diisi data uji. Tidak melakukan browser manual.
- **Verifikasi akhir:** `php artisan test`: **75 passed, 2329 assertions**. Pint file PHP terkait berhasil; `view:cache` berhasil dan cache dibersihkan dengan `view:clear`; `git diff --check` tanpa error whitespace (hanya warning CRLF/LF).
- Route list mengonfirmasi 14 route dengan auth + access-admin. Roadmap, DEC-016, dan dokumentasi frontend diperbarui. Room, PIC, restricted access, Booking, dan Approval tidak diimplementasikan.

## Tahap 4B - Backend User Management (Selesai)

- Menambah `app/Http/Controllers/Admin/UserController.php` dan `app/Http/Requests/SaveUserRequest.php`; memakai model/tabel users dan organizational_units existing. Tidak mengubah migration, dependency, model fillable, atau mekanisme login.
- `routes/web.php`: delapan route bernama `admin.users.index/create/store/show/edit/update/status/reset-password`, dilindungi auth + access-admin. Model binding ID numerik, 404 record hilang, tanpa endpoint delete. Empat halaman user tidak lagi menggunakan controller skeleton.
- Lima Blade user existing (`index`, `create`, `show`, `edit`, `_form`) terhubung ke database; partial `_feedback` dan `_status` ditambahkan. Index pagination 20 beserta unit, dropdown unit dari PostgreSQL, form simpan/update aktif dengan CSRF, error, old input, flash, tombol aktif/nonaktif di index/detail, reset password pada detail. Dashboard menampilkan flash setelah perubahan role sendiri.
- Nama/email wajib, email valid unique, role hanya user/room_pic/super_admin, unit nullable dan harus ada, status boolean. Unit nonaktif tetap tersedia dengan label. Field role/unit/status diisi eksplisit setelah authorization.
- Password minimal 8 dan confirmed saat create/reset atau bila diisi saat edit; `Hash::make`, password kosong/tidak dikirim saat edit mempertahankan hash lama. Password tidak ditampilkan/diflash. Reset merotasi remember token dan benar-benar mengganti kredensial login; pemutusan seluruh session aktif tidak ditambahkan.
- Perubahan status mempertahankan record dan unit; akun nonaktif ditolak login oleh mekanisme existing. Role sendiri yang diubah diarahkan ke dashboard role baru; nonaktifkan diri mengakhiri session. Assignment room_pics tidak diubah otomatis; pengelolaan Room tetap di luar scope.
- `tests/Feature/UserManagementTest.php`: 10 test baru meliputi semua endpoint guest/user/PIC, create ketiga role, dropdown unit database, email duplicate create/update, validasi/password confirmation, edit/role/unit, password lama dipertahankan atau diganti, status/login nonaktif/aktif, reset password lama ditolak dan baru diterima, token, self-change, CSRF, 404, escaping, rendering dan database assertions.
- `FrontendSkeletonTest.php` disesuaikan menjadi 28 route skeleton; test password form sekarang melewati request HTTP agar middleware session/error ikut berjalan.
- Verifikasi memakai schema PostgreSQL sementara melalui `PostgresTestCase`, tidak mereset atau menambah akun uji pada data development. Tidak melakukan browser manual.
- **Hasil aktual akhir:** `php artisan test`: **63 passed, 2236 assertions**. Test khusus User Management pertama: **10 passed, 312 assertions**, lalu seluruh suite diulang setelah penyesuaian test form dan verifikasi flash dashboard. Pint berhasil; `view:cache` berhasil dan cache dibersihkan. `route:list --path=admin/users -v` mengonfirmasi delapan route dengan auth + access-admin. `git diff --check` tanpa error whitespace (hanya warning normalisasi CRLF/LF).
- Tidak mengaktifkan Floor, Facility, Room, Booking, atau Approval. Roadmap, keputusan DEC-015, dan dokumentasi frontend diperbarui.

## Tahap 4A - Backend Organizational Unit (Selesai)

- Controller `app/Http/Controllers/Admin/OrganizationalUnitController.php`, Form Request `app/Http/Requests/SaveOrganizationalUnitRequest.php`, dan service `app/Services/OrganizationalUnitService.php` ditambahkan. Memakai model/tabel existing, tanpa migration/dependency baru.
- `routes/web.php`: empat route halaman unit dipindahkan dari skeleton ke controller modul; store, update, dan status diaktifkan. Prefix nama `admin.organizational-units.`: index/create/store/show/edit/update/status. GET untuk halaman, POST store, PUT/PATCH update, PATCH `/{unit}/status`; auth + access-admin di semua route. Tidak ada delete.
- Lima Blade existing unit (`index`, `create`, `show`, `edit`, `_form`) dihubungkan ke database; partial `_feedback` dan `_status` ditambahkan. Index pagination 20, parent/child dari relasi, pilihan parent database, form aktif dengan CSRF, validation error, old input, flash message, link detail/edit berbasis record, serta tombol aktif/nonaktif.
- Validasi nama wajib maksimal 255, type terbatas pada tiga tipe existing, parent harus ada, status boolean. Directorate tanpa parent; Division parent Directorate; Department parent Division. Self-parent/siklus dan perubahan type yang merusak child existing ditolak. Validasi hierarchy + save memakai transaction dan lock PostgreSQL (DEC-014).
- Status berlaku pada unit yang dipilih saja; child dan relasi user tetap tersimpan, tanpa hard delete. Parent nonaktif tetap tersedia dengan label. Modul lain tetap skeleton, termasuk dropdown unit di Tambah User.
- `tests/Feature/OrganizationalUnitTest.php` ditambahkan (9 test). `FrontendSkeletonTest.php` disesuaikan menjadi 32 route skeleton karena empat halaman unit sudah aktif.
- **Hasil aktual:** `php artisan test --filter=OrganizationalUnitTest`: **9 passed, 222 assertions** pada verifikasi terakhir (termasuk perubahan ke Directorate tanpa field parent untuk memastikan parent lama dikosongkan). Suite penuh sebelumnya `php artisan test`: **53 passed, 2053 assertions**; setelah perbaikan terakhir, seluruh test modul dijalankan ulang dan lulus. Pint pada file PHP terkait berhasil.
- **Verifikasi end-to-end:** request HTTP create Directorate/Division/Department, edit nama/parent/type yang valid, nonaktif/aktif, penolakan parent salah/self-parent/siklus/child tidak valid, error dan old input, flash message, role guest/user/PIC, 404, serta escaping. Record dibaca ulang dan dicek dengan database assertions; index/detail/form membaca data PostgreSQL. Semua memakai schema PostgreSQL sementara melalui `PostgresTestCase`, tidak menambah/mengubah data development untuk pengujian. Tidak melakukan pengujian browser manual.
- `php artisan route:list --path=organizational-units -v` mengonfirmasi tujuh route beserta middleware. Blade `view:cache` berhasil dan cache dibersihkan; `git diff --check` tanpa error whitespace (hanya warning normalisasi CRLF/LF). Dokumentasi roadmap, frontend, dan keputusan diperbarui.

## Tambahan Seeder Unit Kerja

- Atas permintaan pengguna, menambah 6 unit dummy di `FoundationSeeder`; total contoh menjadi 9 unit (2 direktorat, 3 divisi, 4 departemen). Hierarki dan petunjuk penggunaan tercatat di `docs/DATABASE.md`.
- Menggunakan `firstOrCreate` seperti seeder existing: seed ulang tidak menggandakan unit yang sama atau menimpa data existing. Unit baru aktif; akun, unit akun existing, dan aturan akses ruangan tidak diubah oleh penambahan ini.
- `php artisan db:seed --class=FoundationSeeder --no-interaction` berhasil dijalankan pada database lokal tanpa reset/migration.
- Test fondasi diperbarui untuk jumlah unit dan memeriksa status aktif serta hierarki parent. `php artisan test --filter=DatabaseFoundationTest`: **11 passed, 126 assertions** pada schema PostgreSQL sementara. Pint berhasil.
- Form Tambah User tetap skeleton; dropdown belum membaca database dan penyimpanan user belum tersedia. Tambahan ini hanya data development, bukan implementasi Tahap 4.

## Tahap 3 — Frontend Skeleton (Selesai)

- Membaca AGENTS, konteks, roadmap, progress, keputusan, routes, layout, model, dan test existing sebelum implementasi. Roadmap lama diperbarui sesuai urutan terbaru pengguna.
- Seluruh halaman utama user/PIC/admin tersedia: pencarian/daftar/detail ruangan, jadwal, form/detail/My Booking; ruangan PIC, permintaan/detail/riwayat approval; daftar/tambah/edit/detail lima master data, Kelola PIC, Kelola Akses, semua/detail booking, serta jadwal admin.
- 39 route halaman terlindungi: 3 dashboard existing dan 36 route skeleton; 25 route baru. Gate Tahap 2B tetap dipakai. Admin mempertahankan akses URL umum/PIC existing, dengan navigasi utama administrasi. Cari Ruangan dan booking pribadi tetap untuk user/PIC.
- Satu `FrontendSkeletonController` hanya membuka view dari default route. Tidak ada query modul, implicit model binding, penyimpanan, endpoint tulis, atau business logic baru. Identifier `preview` memungkinkan peninjauan detail/edit tanpa record palsu.
- Layout bersama memuat nama aplikasi, identitas/role, menu sesuai izin, logout, dan content. Jadwal, informasi ruangan, detail booking/approval, dan form tambah/edit memakai partial bersama. Komponen field/select/textarea/table/form/notice/tombol nonaktif mengurangi duplikasi.
- List dan pilihan memakai empty state; tidak memasok dummy permanen meskipun seeder development sudah berisi data. Data user login dan unit tetap berasal dari backend existing.
- Tombol proses dinonaktifkan, implicit form submit dicegah dengan satu handler kecil. Form POST dengan CSRF tidak memiliki endpoint tulis. Password tambah wajib, edit opsional, dan tidak pernah diprefill.
- Dokumentasi halaman, route, folder, variabel view, placeholder, dan integrasi berikutnya tersedia di `docs/FRONTEND.md`. Keputusan frontend dicatat pada DEC-013.
- **Verifikasi aktual:** `php artisan test` menghasilkan **44 passed, 1952 assertions**; 6 test baru frontend mencakup seluruh route skeleton per role/guest, tautan, empty state, penolakan POST/PUT/PATCH/DELETE, field, escaping data, dan password. Test database tetap menggunakan schema PostgreSQL sementara.
- Laravel Pint pada controller/route/test terkait berhasil. `php artisan view:cache` berhasil; cache view kemudian dibersihkan dengan `view:clear`. `git diff --check` berhasil tanpa error whitespace (Git hanya memberi peringatan normalisasi CRLF/LF). Tidak menjalankan test browser/styling atau mengubah dependency/migration/model/rule existing.

## Completed Tahap 0–2B (Historis)

- Dokumentasi konteks, roadmap, keputusan, dan aturan agent.
- Repository GitHub terhubung; commit awal project `b3d94a4` (`first commit`) telah dipush sebelumnya. Pengguna mengizinkan publikasi gabungan hasil Tahap 1, 2A, dan 2B beserta dokumentasi penjelasan.
- PHP `pdo_pgsql` aktif; koneksi Laravel ke PostgreSQL `spmt_booking_room` berhasil. Password hanya disimpan dalam `.env` lokal yang diabaikan Git.
- Analisis project: Laravel 10.50.3, model User dan migration bawaan, tanpa package role/permission. Tidak mengubah dependency.
- Tujuh migration baru untuk tabel fondasi. Sesuai permintaan pengguna, kolom unit, role, dan status aktif digabung langsung ke migration pembuatan users; migration penambahan terpisah dihapus. Migration organizational_units diurutkan sebelum users agar foreign key valid.
- Model OrganizationalUnit, Floor, Facility, Room dan relasi User; validasi calon PIC melalui EligibleRoomPic.
- Constraint PostgreSQL: foreign key, batas kapasitas, nilai enum, kode ruang unik, dan pasangan pivot unik. Penghapusan unit/lantai yang masih digunakan dibatasi.
- Seeder development: 3 unit, 3 akun, 8 lantai, 7 fasilitas, dan 4 ruang contoh. Seeder bisa diulang dan tidak mereset password akun lama.
- Template koneksi `.env.example` dan default database diarahkan ke PostgreSQL.
- Panduan singkat tabel, akun dummy, dan cara melihat data di `docs/DATABASE.md`.
- Test integrasi memakai schema PostgreSQL sementara; konfigurasi XML PHPUnit disesuaikan dengan PHPUnit 10 yang sudah terpasang.
- Tahap 2A: Breeze 1.29.1 baru dipasang pada Laravel 10.50.3. Scaffolding session/LoginRequest resmi disesuaikan untuk login internal, logout, Remember Me, dan dashboard Blade sederhana tanpa NPM build.
- Akun nonaktif ditolak sebelum login; session/Remember Me akun yang dinonaktifkan juga dihentikan. Password salah tidak mengungkap status akun.
- Route login memakai guest, dashboard/logout memakai auth; form POST memakai CSRF. Sejak Tahap 2B, redirect dashboard mengikuti role. Registrasi publik dan route profil/reset password/verifikasi email tidak tersedia.
- Helper schema test dipindahkan ke `tests/PostgresTestCase.php` agar dipakai bersama oleh test fondasi dan authentication. Panduan menjalankan tersedia di `docs/AUTHENTICATION.md`.
- Tahap 2B: empat Gate terpusat, middleware `can` pada kelompok route, dashboard Pegawai/PIC/Admin, navigasi `@can`, halaman 403, dan sebelas halaman placeholder melalui satu view bersama.
- Login dan guest redirect memakai mapping dashboard yang sama. Role tidak dikenal ditolak dengan 403. Unit organisasi hanya ditampilkan; tidak dipakai untuk authorization.
- PIC dapat mengakses halaman pegawai; admin dapat mengakses administrasi, informasi umum, dan placeholder PIC tanpa mengubah assignment `room_pics`. Rincian route dan akses ada di `docs/AUTHORIZATION.md`.
- Penjelasan percakapan tentang `can`, nama izin `access-employee`, awalan nama route `admin.`/`pic.`, pemisahan auth.php, dan alasan tidak membutuhkan NPM disimpan di `docs/AUTHORIZATION.md` untuk rujukan berikutnya.

## Verifikasi Tahap 1–2B (Historis)

- Migration awal pernah menjalankan 12 file. Setelah penggabungan migration users, riwayat migration lokal diselaraskan menjadi 11 file tanpa menghapus atau mengubah data aplikasi. `php artisan migrate:status`: seluruh 11 migration berstatus Ran.
- `php artisan db:seed --no-interaction`: berhasil.
- `php artisan test` setelah Tahap 2B: **38 passed, 513 assertions**, mencakup 16 test authentication, 9 test authorization, 11 test fondasi, dan 2 test existing.
- `php artisan migrate --no-interaction`: Nothing to migrate; migration Tahap 1 tidak diubah pada Tahap 2A. Seeder existing dijalankan ulang dan berhasil.
- Route list terverifikasi: kelompok umum, pegawai, PIC, admin memakai auth + can; login/logout tetap tersedia dan tidak ada route registrasi publik.
- Ketiga akun development pada database lokal terverifikasi aktif dan password dummy lolos validasi guard Laravel; tidak mereset password akun.
- Test authentication mencakup ketiga role, password salah, akun nonaktif, auth/guest, session regeneration, logout/session invalidation/CSRF rotation, CSRF 419, throttle login, Remember Me, dan penonaktifan session lama.
- Test authorization login memakai akun seeder pada schema PostgreSQL sementara, lalu mencoba URL setiap area secara langsung. Memverifikasi menu, redirect per role, 403, role tidak dikenal dalam memori, perubahan role pada request berikutnya, unit null, dan assignment PIC tetap sama. Tidak menjalankan test pada tabel development/produksi.
- Pengujian mencakup seluruh relasi, seed ulang, password, validasi PIC, enum, kapasitas, seluruh foreign key fondasi, unique pivot/kode, restrict/cascade, penolakan seed produksi, dan rollback/migrate ulang pada schema test.
- Laravel Pint pada file PHP terkait: passed. `git diff --check`: tidak ada kesalahan whitespace.
- Tidak menjalankan reset atau rollback terhadap tabel development; rollback test hanya di schema sementara.

## Batasan dan Asumsi

- Satu role dan maksimal satu unit per user. PIC mendapat halaman pegawai; hak booking pribadi admin dan pewarisan akses unit masih belum ditentukan.
- Data dummy bukan struktur/data resmi perusahaan. Password development tercatat di `docs/DATABASE.md`.
- Assignment PIC memvalidasi role room_pic saat penyimpanan; penulisan pivot langsung tidak menjalankan rule otomatis. Perubahan role setelah assignment tidak otomatis melepas pivot, tetapi assignment lama dapat dilepas. Aturan minimum PIC/unit belum ditetapkan.
- Master data admin, assignment PIC, dan daftar unit restricted sudah aktif pada Tahap 4A-4E. Room user/PIC, Booking, Approval, kalender aktif, notification, dan dashboard kompleks belum diimplementasikan. Tahap 4E tidak mengubah migration, seeder, dependency, atau struktur role.
- Parameter URL halaman skeleton (Room user/PIC, Booking, Approval) belum melakukan resolution record; backend berikutnya wajib menambahkan 404 dan authorization record. Nama field booking di view bersifat kontrak presentasi sementara, bukan keputusan schema.
- UI PIC dan EligibleRoomPic menerima role room_pic. Inheritance restricted access dan booking pribadi admin tetap belum diputuskan; penyimpanan unit eksplisit tidak menetapkan aturan pewarisan saat booking.

## Next Step

Tahap 4E selesai. Tunggu instruksi Tahap 5 Booking Backend; jangan lanjut ke Booking atau Approval secara otomatis. `FrontendSkeletonController` tetap digunakan oleh modul yang belum aktif.
