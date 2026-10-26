# Decisions

## DEC-059 - Batas input HTTP dan penataan controller

- Audit controller atas permintaan pengguna 2026-10-20 memakai Form Request per kontrak operasi, dengan request bersama untuk pagination, filter lokasi dan status aktif. Data tulis hanya dari hasil validasi. Route/Gate dan service tetap bertanggung jawab atas authorization serta aturan bisnis terbaru dalam transaksi.
- ID yang mengarah ke query database berhenti pada validasi tipe yang gagal (`bail`). Parameter URL numerik dibatasi integer positif sebelum binding; readable route key existing tetap berlaku. Pagination HTTP menerima integer 1 sampai 2147483647 untuk menghindari overflow offset, tanpa mengubah jumlah item per halaman.
- Hanya constraint unik PostgreSQL master/user yang dikenal dipetakan ke pesan validasi field; error database lain tidak ditutupi sebagai validasi. Constraint database tetap penentu akhir saat request bersamaan.
- Penyimpanan pengguna/pivot dan reset password dipusatkan pada UserService, membaca record setelah lock. Penentuan lokasi/expiry/referensi registrasi tamu dipindah ke GuestRegistrationService. Tidak mengubah role, scope lokasi, approval, expiry bisnis atau lifecycle booking. Inventaris dan batas audit di docs/CONTROLLER_AUDIT.md.
- Profil API bawaan memakai autentikasi Sanctum dan Gate access-general agar akun nonaktif/role tidak dikenal juga ditolak saat menggunakan token; tidak mengaktifkan fitur API baru.

## DEC-058 - Izin resepsionis melalui satu checkbox Kelola User

- Arahan pengguna 2026-10-20 kembali ke alur pendataan/jadwal tamu lalu booking dibantu pegawai yang ditunjuk, tanpa antrean permintaan baru. Admin cukup mencentang Petugas resepsionis pada tambah/Kelola User existing; daftar user menandai petugasnya. Tidak ada role/menu administrasi/lokasi khusus tambahan.
- users.is_receptionist default false, tidak mass assignable. Berlaku untuk user/PIC aktif; Admin tidak otomatis mendapatkan hak booking. Role menjadi Admin mengosongkan flag. Simpan melalui form dengan checkbox dilepas mencabut izin; request lama tanpa field ini mempertahankan izin selama role masih eligible. Izin lokasi tetap mengikuti branch_user.
- Hanya petugas mendapat dropdown Booking untuk (Diri sendiri atau nama/instansi tamu aktif di kantor ruangan). Tamu cukup menyebut nama/instansi; kode tidak perlu tampil pada jadwal tamu atau diketik manual. Referensi internal dan snapshot laporan existing dipertahankan.
- Livewire memuat ulang status izin untuk daftar tamu dan checkPlan. BookingService memeriksa user terkunci sebelum memproses referensi tamu, termasuk replay token; pencabutan izin langsung memblokir pengajuan tamu berikutnya. Pengajuan existing tetap tercatat dan dapat diputuskan mengikuti kewenangan PIC/Admin existing. Pegawai biasa tetap dapat booking pribadi.


## DEC-057 - Isian dan tampilan pendataan tamu

- Arahan pengguna 2026-10-20: form di tengah, lebih besar, dengan latar pelabuhan lembut sesuai palet. Isian: nama, instansi, NIP, kontak dan tujuan; kantor/cabang dihapus dari form. NIP opsional karena tamu eksternal belum tentu memilikinya, disimpan sebagai string agar nol awal utuh. Tujuan kunjungan wajib untuk registrasi baru; record lama tetap kompatibel.
- Lokasi tetap diperlukan untuk scope jadwal/booking DEC-056, ditentukan di server melalui GUEST_BRANCH_ID. Jika belum diatur, memakai kantor ID pertama pada master data. Field branch_id yang disisipkan dalam POST diabaikan. Konfigurasi lokasi tidak mengubah registrasi existing.
- NIP dan tujuan disimpan hanya pada registrasi sementara, ikut terhapus saat expiry; snapshot booking/laporan tetap nama, instansi dan kode tamu. Tidak memperluas scope akses jadwal atau mengubah approval.


## DEC-056 - Pendataan tamu sementara, jadwal terbatas, dan snapshot peminta

- Permintaan pengguna 2026-10-20 mengizinkan pendataan tamu eksternal melalui QR/form, memperluas scope DEC-008 dan pengecualian DEC-053. Session registrasi aktif membaca jadwal khusus tamu tanpa akun pegawai; kalender internal tetap terlindungi.
- Jadwal hanya ruangan aktif akses all di kantor tujuan serta jam Pending/Approved, tanpa nama pemohon, unit, agenda, kontak atau catatan.
- Pegawai/resepsionis memakai kode registrasi aktif pada kantor yang sama dalam form booking existing. Izin access-employee tetap, tanpa role baru atau hak booking Admin. Pengajuan tamu wajib PIC aktif dan persetujuan; izin lokasi/unit/ruangan/operasional dan konflik tetap diperiksa.
- Snapshot nama, instansi dan kode tamu pada booking terpisah dari user_id pegawai pengaju. Detail, laporan layar/CSV/cetak menampilkan kedua pihak. Kontak tidak disalin; penghapusan registrasi tidak mengubah riwayat booking.
- Default sementara tiga hari, configurable days/end_of_day pukul 17.00 WIB. Registrasi mulai pukul 17.00 berakhir esok pukul 17.00. Kebijakan akhir masih menunggu konfirmasi pemilik proses. Expiry diperiksa setiap akses/submit; scheduler menghapus registrasi kedaluwarsa setiap menit. Panduan: docs/GUESTS.md.


## DEC-055 - Kelola user tunggal dan tombol Tampilkan pada filter

- Daftar user hanya memiliki aksi Kelola menuju form edit. Halaman detail terpisah dihapus; route GET detail lama mengalihkan ke edit. Status aktif/nonaktif, identitas, izin lokasi, dan penggantian/reset password dikelola pada halaman Kelola. Endpoint status/reset-password tetap tersedia dengan authorization dan validasi existing; redirect setelah perubahan menuju Kelola, termasuk penanganan aman ketika admin menonaktifkan akun sendiri atau mengganti role sendiri.
- Dropdown Kantor / Cabang pada daftar user menyaring akses efektif: pegawai/PIC berdasarkan pivot branch_user, Admin ikut semua filter karena memiliki akses global. Pagination mempertahankan lokasi. Tidak menambah kolom bisnis user, migration, maupun perubahan seed untuk task ini; DEC-054 tetap berlaku.
- Dropdown filter memiliki tombol Tampilkan. Pilihan pada kalender/katalog disimpan sebagai draft dan diterapkan oleh aksi Livewire setelah tombol ditekan. Filter daftar admin, approval/riwayat, laporan, dan WhatsApp memakai komponen ListingFilters dengan navigasi Livewire: hasil dimuat tanpa reload browser, URL GET diperbarui, halaman pagination kembali ke awal, dan tautan CSV/cetak mengikuti filter yang diterapkan. Mengganti lokasi laporan mengosongkan pilihan ruangan, dengan daftar ruangan tetap dibatasi izin server.
- Tujuan navigasi menggunakan whitelist route dan properti Locked; role/akun aktif diperiksa kembali pada render/aksi. Filter lokasi hanya boleh dijalankan Admin. Laporan PIC memvalidasi ruangan dalam izin lokasi dan penugasannya. Route GET existing tetap melakukan validasi/authorization. Tidak menerima URL redirect bebas.
- Dropdown isian form (role, status akun, lantai, durasi/jam booking) tetap menggunakan aksi simpan/pengajuan masing-masing. Tombol Tampilkan hanya untuk filter data, bukan perubahan data akun/booking. Script menu mobile, dialog approval, editor WhatsApp, navbar, dan indikator loading menyesuaikan siklus navigasi Livewire.

## DEC-054 - Izin lokasi pada akun, bukan filter pegawai/PIC

- Koreksi pengguna 2026-10-19 menggantikan asumsi akses seluruh lokasi pegawai/PIC pada DEC-051. Admin tetap meliputi semua lokasi; hanya Admin melihat dropdown lokasi. Pegawai/PIC otomatis menerima gabungan data dari lokasi yang diizinkan, tanpa dropdown untuk berpindah lokasi.
- Izin menggunakan pivot `branch_user` (user_id, branch_id, primary key gabungan), bukan boolean kantor pusat atau satu kolom lokasi. Satu akun dapat diberi satu/beberapa lokasi secara eksplisit melalui checkbox Kelola User. Tanpa izin berarti tidak dapat mengakses data lokasi; akun baru tanpa pilihan tidak otomatis mendapat lokasi. Izin tetap terpisah dari role dan unit organisasi.
- Migration additive memberi izin Kantor Pusat kepada akun existing yang sebelumnya digunakan untuk pusat. FoundationSeeder memberi HQ hanya untuk akun demo yang baru dibuat; menjalankan seeder ulang tidak mengembalikan izin yang sudah dicabut dan tidak mengubah password/role existing. Tidak memerlukan migrate:fresh atau reset data.
- Scope Room::visibleTo dan User::canAccessBranch menjadi aturan bersama untuk katalog/dashboard, jadwal dan refresh Livewire, detail/URL lama, booking termasuk retry token, riwayat/detail pribadi, approval, rekap/badge PIC, laporan layar/CSV/cetak, assignment PIC, serta penerima/kelayakan notifikasi. Akun nonaktif/role tidak dikenal ditolak. Akses diperiksa di server, termasuk setelah izin dicabut; menyembunyikan dropdown bukan pengamanan utama.
- PIC membutuhkan kedua syarat: izin lokasi dan assignment room. Assignment lama di lokasi yang tidak lagi diizinkan tidak memberi akses. Admin tidak otomatis memberi izin lokasi ketika memilih PIC; perbaiki izin akun dahulu. Hak pemohon, operational/restricted unit, waktu, bentrok dan idempotensi booking tetap berlaku di dalam lokasi yang diizinkan.
- Penyimpanan user dan izin bersifat atomik, dengan lock row user agar sinkron dengan booking/keputusan yang membaca ulang akun. Request edit lama tanpa branch_ids mempertahankan izin; marker form dan semua checkbox kosong secara eksplisit mencabut seluruh izin. Riwayat booking tetap tersimpan dan tersedia pada laporan Admin walaupun izin akun dicabut.

## DEC-053 - Jadwal hanya untuk akun pegawai

- Arahan pengguna 2026-10-19 menggantikan akses jadwal publik DEC-031. Beranda tetap publik sebagai pengantar, tanpa tombol atau data jadwal. URL lama `/jadwal-ruangan` memerlukan auth + access-general lalu redirect ke jadwal internal.
- Komponen legacy PublicRoomSchedule juga mewajibkan access-general pada boot setiap request, termasuk refresh Livewire; menghapus tautan saja tidak cukup menutup akses.

## DEC-052 - Kebutuhan teknisi IT dan kelengkapan laporan

- Booking memiliki `it_support` dan `approved_it_support`, default false. Permintaan IT berdiri sendiri, tidak bergantung pada pengaturan persiapan/konsumsi ruangan. Permintaan memerlukan review dan PIC aktif walau ruangan biasanya langsung Approved.
- PIC yang ditugaskan atau Admin dapat menyetujui ruangan dengan/tanpa IT melalui checkbox, default tidak dicentang. IT yang tidak diminta tidak boleh disetujui; service dan CHECK database menjaga subset. Penolakan booking mengosongkan persetujuan IT. Keputusan final tetap tidak dapat diubah.
- Field IT termasuk fingerprint idempotensi dan diterapkan ke semua tanggal dalam submitMany. Form lama dengan IT tidak dikirim mempertahankan kompatibilitas fingerprint.
- Laporan bulanan layar/CSV/cetak memuat seluruh status sesuai cakupan dan filter, lokasi, perkiraan peserta, konsumsi diminta/disetujui, IT diminta/disetujui, serta catatan pemohon/keputusan. Pending diberi label menunggu review. Data kebutuhan bukan bukti layanan telah diberikan; peserta hanya tersedia bila diisi saat pengajuan, tanpa backfill perkiraan palsu.

## DEC-051 - Lokasi kantor pusat dan cabang

- Arahan pengguna 2026-10-19 menambahkan master `branches` (lokasi), terpisah dari unit organisasi dan role. Setiap room terhubung satu lokasi melalui FK; migration menempatkan seluruh room existing di Kantor Pusat. Booking tetap mengikuti room, sehingga konflik antarroom/lokasi tidak saling memblokir.
- Asumsi implementasi yang disampaikan ke pengguna: Admin tetap terpusat dengan kewenangan seluruh lokasi. PIC tetap berbasis assignment ruangan, sehingga PIC pusat/cabang dapat berbeda, atau satu PIC ditugaskan di beberapa lokasi. Pegawai dapat memilih semua lokasi, tetap tunduk pada akses restricted/operasional existing. Filter lokasi bukan pembatasan tenant atau role Admin cabang. Kebijakan Admin khusus cabang belum ditetapkan; tidak menebak pembatasan akun.
- Admin dapat menambah/mengganti nama lokasi dari `/admin/branches`, memilih lokasi pada pengelolaan room, dan menetapkan PIC melalui form existing. Tidak membuat akun atau ruangan cabang fiktif. Fasilitas, lantai, dan unit organisasi masih master bersama; nomor lantai dan kode room tetap mengikuti constraint global existing.
- Dropdown lokasi tersedia pada katalog/dashboard pegawai, jadwal, daftar ruangan Admin/PIC, booking pribadi/Admin, approval/riwayat dan laporan bulanan. Pergantian lokasi kalender mengosongkan filter room; katalog mereset pagination. Cakupan PIC tetap diberlakukan di server pada approval dan laporan.
- Lokasi room dengan booking tidak dapat dipindahkan untuk menjaga riwayat laporan; nonaktifkan room lama dan buat room baru pada lokasi tujuan. Nama lokasi mengikuti nama master terkini. Tidak menyediakan hapus lokasi; FK restrict menjaga referensi.


## DEC-050 - Dashboard Admin berfokus pada administrasi

- Permintaan pengguna 2026-10-15 mengganti ringkasan dan aktivitas booking Admin dengan ringkasan master ruangan/akun/unit, kondisi operasional ruangan aktif, serta kelengkapan PIC aktif dan fasilitas ruangan. Operasional booking harian tetap menjadi fokus dashboard PIC; akses Admin ke jadwal/approval/booking existing dipertahankan.
- Daftar pengaturan yang perlu dilengkapi hanya mencakup ruangan aktif; PIC harus ber-role room_pic dan aktif. Fasilitas belum diatur berarti relasi fasilitas kosong. Ringkasan menghitung seluruh hasil, daftar menampilkan maksimal lima ruangan dengan tautan Kelola. Kondisi ruangan bukan ukuran okupansi. Tidak mengubah data atau memberi/mencabut penugasan otomatis.


## DEC-049 - Review konsumsi dan booking beberapa tanggal

- Permintaan pengguna 2026-10-15 melanjutkan checkbox Snack/Makanan DEC-048. Permintaan disimpan terpisah dari `approved_consumption_snack/meal`; PIC yang ditugaskan atau Admin memilih subset yang disetujui, default kosong. Ruangan boleh disetujui tanpa konsumsi. Konsumsi tidak diminta tidak boleh disetujui; validasi service dan CHECK database menjaga subset. Keputusan final tetap tidak dapat diubah.
- Permintaan konsumsi mewajibkan review walau ruangan tidak mewajibkan approval; membutuhkan PIC aktif. WhatsApp konsumsi hanya dicatat untuk jenis yang benar-benar disetujui dan menyertakan rinciannya. Booking Approved/Completed existing mempertahankan konsumsi yang sebelumnya sudah dikonfirmasi melalui backfill migration.
- Tombol Tambah tanggal booking memakai jam/agenda/persiapan sama untuk tanggal terpisah, bukan rapat kontinu lintas tengah malam. Setiap tanggal menjadi booking dan keputusan tersendiri. Simpan seluruh tanggal dalam satu transaksi dengan lock ruangan, validasi akses/jam/bentrok tiap tanggal, dan token idempotensi deterministik; kegagalan satu tanggal membatalkan semuanya. Maksimal 365 tanggal tambahan sebagai batas ukuran request.

## DEC-048 - Pilihan Konsumsi Snack dan Makan

- Permintaan pengguna 2026-10-14 mengganti checkbox tunggal `needs_consumption` (boolean) dengan dua checkbox independen: `consumption_snack` dan `consumption_meal`. Pemohon dapat memilih Snack saja, Makan saja, keduanya, atau tidak sama sekali. Menggantikan model konsumsi ya/tidak pada DEC-040.
- Kedua kolom boolean default false pada database. CHECK constraint `bookings_preparation_consistent` diperbarui: ketika `meeting_preparation` false, kedua flag konsumsi harus false. Ketika `meeting_preparation` true, kedua flag konsumsi bersifat opsional dan independen.
- Notifikasi WhatsApp konsumsi terpicu jika salah satu jenis konsumsi dipilih (`consumption_snack OR consumption_meal`), bukan hanya berdasarkan satu boolean. Data booking existing dengan `needs_consumption=true` dimigrasi menjadi keduanya aktif.
- Detail booking dan dialog review menampilkan jenis konsumsi spesifik (Snack, Makan) alih-alih "Diperlukan/Tidak diperlukan". Ini tetap perkiraan kebutuhan, bukan konfirmasi penyediaan.

## DEC-047 - Penugasan PIC Existing untuk Seluruh Ruangan Saat Ini

- Pengguna pada 2026-10-14 mengizinkan penggunaan akun PIC existing untuk setiap ruangan saat ini, menyelesaikan penundaan penugasan pada DEC-045. Akun aktif satu-satunya, `Room PIC Demo`, ditambahkan melalui RoomAssignmentService ke ruangan yang belum terhubung dengannya; penugasan existing dipertahankan.
- Berlaku pada database proyek saat penugasan, bukan default otomatis untuk ruangan baru. Role akun dan aturan approval tetap mengikuti pengaturan existing.

## DEC-046 - Email Unik, Nomor WhatsApp Boleh Bersama

- Konfirmasi pengguna 2026-10-13 membatalkan keunikan nomor pada DEC-045: beberapa akun boleh memakai nomor WhatsApp yang sama, terutama untuk uji coba. Nomor tetap nullable, dinormalisasi dan divalidasi format canonical. Tidak menghapus nomor duplikat existing atau menentukan akun pemilik tunggal.
- Email tetap lowercase/trim dan unik case-insensitive pada validasi dan database. Migration kontak yang belum diterapkan pada database proyek disesuaikan sebelum dijalankan agar tidak menambah unique nomor, tetapi tetap menjaga format nomor dan unique email normalized.
- Pengiriman tetap deduplikasi nomor per booking/event; dua akun penerima bernomor sama menghasilkan satu pesan pengajuan, dan satu pesan konsumsi jika event tersebut terjadi. Pesan antarjenis atau antarbooking tetap berbeda.

## DEC-045 - Pembersihan Ruangan Demo dan Kontak Unik

- Permintaan pengguna 2026-10-13 mengizinkan penghapusan ruangan demo dan penamaan ruangan biasa berdasarkan lantai+huruf: 2A, 3A/3B, 4A/4B, 6A/6B. Selat Malaka I/II/III tetap memakai nama yang diberikan pengguna. Penghapusan hanya fixture code/description demo yang tidak mempunyai booking, melalui RoomService/lock/transaksi existing; tidak menghapus akun PIC demo atau riwayat booking.
- Seeder ruangan memakai format nama baru untuk record baru; data existing/ID/code dipertahankan. FoundationSeeder tidak membuat ulang ruangan demo di luar testing bila master ruangan SPMT sudah ada; fixture test tetap tersedia. Penugasan tujuh ruangan yang belum punya PIC menunggu pilihan pengguna; tidak menebak akun atau mengubah role.
- Nomor WhatsApp unik setelah normalisasi internasional, nullable untuk akun tanpa nomor. Email dinormalisasi lowercase/trim dan unik tanpa membedakan huruf besar/kecil. Validasi create/update mengabaikan akun sendiri; login ikut menormalisasi email. Migration menambah index email normalized, unique nomor, serta check format nomor canonical. Konflik/format invalid pada data existing menghentikan migrasi tanpa menghapus kontak; pemilik nomor duplikat harus ditentukan pengguna dahulu.

## DEC-044 - Pengajuan Tanpa Batas Minimal Dua Jam

- Permintaan pengguna 2026-10-13 menghapus batas minimal dua jam sebelum rapat, menggantikan bagian batas waktu pengajuan pada DEC-028/DEC-029/DEC-034. Booking dapat diajukan kurang dari dua jam sebelum mulai; tidak menambahkan batas pengajuan baru.
- Pemeriksaan/submit tetap menolak waktu mulai yang sudah lewat, memakai waktu server WIB dan presisi input menit (ceilMinute). Waktu diperiksa ulang saat submit; form yang dibiarkan terbuka tidak membolehkan waktu lampau. Jam selesai harus setelah mulai pada tanggal sama, akses/status/konflik serta idempotensi tetap berlaku. Approval tetap harus diputuskan sebelum mulai sesuai aturan existing.
- Konfigurasi minimum_notice_hours dihapus; panduan beranda, dashboard dan form diperbarui. Default tanggal mengikuti waktu saat ini, bukan sekarang ditambah dua jam. Durasi pilihan 2 jam adalah durasi rapat dan tetap tersedia.

## DEC-043 - Penerima dari Akun dan Pengelolaan Notifikasi Admin

- Penugasan pengguna 2026-10-13 mengaktifkan pengelolaan nomor pada Kelola User serta halaman Admin `/admin/whatsapp` untuk penerima, template, pratinjau, riwayat/filter/pagination dan retry. Hanya Admin aktif dapat mengelola; PIC tidak memperoleh akses master pengaturan atau nomor akun lain.
- Nomor bersumber dari `users.whatsapp_number` yang tetap guarded/hidden. Input Admin menerima format lokal Indonesia atau internasional, dinormalisasi dan divalidasi; nomor opsional dan pengiriman tidak mensyaratkan nomor pemohon. Request lama tanpa field mempertahankan nomor; input kosong menghapus nomor.
- Pengajuan memakai PIC aktif pada room jika opsi otomatis dipilih, ditambah akun Admin aktif terpilih. Konsumsi memakai akun aktif terpilih tanpa mengubah role/hak akses. `whatsapp_recipients.user_id` mereferensikan akun; nomor legacy disimpan nonaktif bila tidak dapat dipetakan ke satu akun eligible, tanpa menebak akun/role. Migrasi tidak mengubah nomor akun dan menjaga pesan historis. Kedua jenis pesan awalnya nonaktif untuk review Admin.
- `whatsapp_templates` menyimpan body plain text (maksimal 2.000 karakter), daftar variabel terbatas, status aktif dan opsi PIC otomatis. Tidak mengevaluasi Blade/PHP. Pratinjau menggunakan booking terbaru tanpa menulis pengaturan/mengirim pesan. Riwayat dan pratinjau di-escape.
- Outbox tetap snapshot nomor/isi serta `recipient_user_id`. Nomor terbaru dipakai untuk pesan berikutnya; sebelum kirim/retry, server mengecek status booking, akun/nomor, pilihan penerima, penugasan dan jenis notifikasi aktif. Perubahan menyebabkan skip/penolakan retry, bukan memindahkan pesan historis. Nomor sama pada beberapa akun dideduplikasi per booking/event.
- Retry UI hanya requeue failed, tanpa HTTP dalam request Admin; cron/scheduler tetap mengirim. Token/perangkat/global enable tetap konfigurasi server, bukan field user atau token yang ditampilkan UI. Unknown/processing tidak otomatis diulang. Migrasi referensi user tidak mendukung rollback otomatis karena pemetaan multi-akun tidak dapat dikembalikan tanpa kehilangan konfigurasi; pemulihan deployment memakai backup.

## DEC-042 - Antrean WhatsApp dan Penerima Uji Coba

- Instruksi pengguna 2026-10-13 mengizinkan penyimpanan penerima tanpa akun tambahan, pesan pengajuan Pending dan kebutuhan konsumsi setelah Approved. Dua tujuan tersimpan pada `whatsapp_recipients`; nomor asli diinput operasional, bukan seeder/source. Nomor akun sebelumnya adalah calon pengirim; koneksi pengirim ditentukan perangkat/token provider, bukan field user.
- Fonnte digunakan sebagai adapter uji coba berdasarkan opsi yang dibahas, pengiriman default nonaktif sampai token/perangkat dihubungkan. Token hanya konfigurasi server. Tidak mengirim pesan nyata dalam implementasi/test ini.
- Outbox `whatsapp_messages` ditulis dalam transaksi BookingService; unique booking/event/nomor dan idempotensi booking mencegah pencatatan ganda. Scheduler memproses maksimal 50 pesan per menit di luar transaksi booking; lock dan status processing mencegah dua worker mengambil pesan sama. Tidak memakai queue sync untuk HTTP.
- Approved langsung yang meminta konsumsi juga memicu pesan konsumsi. Tidak ada backfill/pesan ke pemohon/distribusi PIC produksi. Pesan approval lama atau penerima yang berubah dinonaktifkan sebelum kirim dilewati.
- Accepted berarti diterima API, bukan sampai/dibaca. Retry eksplisit hanya untuk provider rejection (failed); unknown/processing tertinggal tidak otomatis diulang karena risiko duplikat eksternal. Panduan operasional ada di `docs/WHATSAPP.md`.

## DEC-041 - Persiapan Penerima Notifikasi WhatsApp

- Klarifikasi pengguna 2026-10-13: nomor pada akun uji coba sebelumnya akan dipakai sebagai pengirim, bukan penerima. Penerima uji coba memakai nomor lain yang diberikan pengguna dan tidak memerlukan akun aplikasi hanya untuk menerima pesan. Nomor pribadi tidak dicatat di dokumentasi/source. Notifikasi kebutuhan konsumsi dikirim setelah booking disetujui; layanan pengirim belum dipilih dan pengiriman belum aktif.
- Permintaan pengguna 2026-10-12 mengizinkan pengembangan bertahap notifikasi WhatsApp untuk booking ke PIC/Admin dan kebutuhan konsumsi, menggantikan pengecualian WhatsApp DEC-008. Layanan pengiriman, penerima konsumsi, dan waktu pemberitahuan konsumsi belum diputuskan; pengiriman belum diaktifkan.
- Tahap pertama hanya menyiapkan akun uji coba role Pegawai aktif, tanpa assignment PIC/Admin atau unit kerja yang diasumsikan. Nomor penerima disimpan di `users.whatsapp_number` nullable dalam format internasional dengan awalan `+`; nomor bukan konfigurasi pengirim. Data akun pribadi tidak dimasukkan ke seeder atau source frontend.
- Nomor tetap guarded dan hidden dari serialisasi User. Pengaturan nomor melalui UI, verifikasi kepemilikan nomor, serta channel/provider WhatsApp belum dibuat. Tidak mengirim pesan atau mengubah alur booking/approval dalam tahap persiapan ini.

## DEC-040 - Persiapan Rapat Opsional dan Unit Kerja di Jadwal Internal

- Arahan VP yang disetujui pengguna mengaktifkan pencatatan persiapan rapat, menggantikan pembatasan jumlah peserta/konsumsi pada DEC-029/DEC-033 dan konteks lama. Ini hanya perkiraan peserta serta kebutuhan konsumsi ya/tidak, tanpa pemesanan catering, porsi, biaya, atau konfirmasi penyediaan.
- Room memiliki `allows_meeting_preparation`, dapat diubah Admin. Migration mengaktifkannya satu kali pada ruangan existing bernama Selat Malaka; seeder mengaktifkannya pada Selat Malaka baru. Ruangan lain default nonaktif; seeder ulang mempertahankan perubahan pengaturan Admin.
- Checkbox persiapan membuka jumlah peserta wajib integer positif dan checkbox konsumsi. Melepas checkbox membuang detail; server memeriksa pengaturan room kembali saat check/submit. Snapshot booking disimpan dan tampil pada detail pemohon/Admin/PIC serta dialog review, termasuk setelah pengaturan room berubah. Jumlah peserta tidak dibatasi kapasitas yang belum dikonfirmasi.
- Seeder Foundation dan BookingRooms mewajibkan approval seluruh room yang dikelolanya, termasuk record existing saat seeder diulang. Tidak mengubah status booking historis. Assignment PIC existing dipertahankan; tidak menyalin PIC Selat Malaka ke ruang biasa secara otomatis. Admin perlu menetapkan PIC aktif sebelum room tersebut menerima pengajuan.
- Jadwal internal setelah login menampilkan snapshot `unit_name` dengan label Unit kerja (akun dapat terhubung ke departemen/divisi/direktorat). Unit kosong memakai fallback; jadwal publik tidak memilih/mengirim field unit. Agenda, pemohon, catatan, detail persiapan dan token tetap tidak ditampilkan pada jadwal bersama.

## DEC-039 - URL Master Data Terbaca dan Area Approval Admin

- Permintaan perapian route diterapkan dengan route key `ID-slug-nama` pada Room, Floor, Facility, dan OrganizationalUnit melalui trait `HasReadableRouteKey`. Contoh: `/rooms/12-selat-malaka` dan `/admin/rooms/12-selat-malaka`. Slug diturunkan dari nama menggunakan `Str::slug`, tanpa kolom, migration, atau dependency tambahan. ID tetap identitas record agar nama duplikat tidak bentrok. Nama kosong/tidak menghasilkan slug memakai ID saja.
- Model binding menerima ID lama dan ID dengan suffix slug, termasuk suffix sebelum pergantian nama; nama route, method form, field ID database, query filter, dan authorization tetap berlaku. Tautan baru memakai model agar menghasilkan nama terkini. Parameter tidak valid atau di luar jangkauan bigint ditolak dengan 404. URL lama tetap dapat dibuka tanpa redirect wajib.
- Account/User, Booking, dan Approval tetap memakai ID: nama akun, agenda, atau catatan rapat tidak dijadikan slug URL. Nama path utama mengikuti bahasa Inggris existing; `/jadwal-ruangan` publik dipertahankan untuk kompatibilitas. Perubahan ini tidak mengubah bahasa antarmuka.
- Approval Admin mempunyai route `/admin/approvals`, `/admin/approvals/history`, dan `/admin/approvals/{approval}` serta POST keputusan khusus middleware Admin. Sidebar, tautan, form, dan redirect di area ini tetap dalam prefix Admin. Controller/view review existing digunakan bersama tanpa menduplikasi business logic. URL PIC existing masih kompatibel dengan kewenangan Admin DEC-037; PIC tetap hanya room penugasannya.
- Form booking room terpilih memakai `/rooms/{ID-slug}/book` (`rooms.book`) dengan model binding dan middleware employee. Tautan katalog/detail serta redirect login room terpilih memakai URL ini. `/my-bookings/create?room_id={id}` tetap tersedia sebagai redirect kompatibilitas (302); tanpa room menuju katalog. Query filter jadwal/laporan tetap menggunakan ID karena merupakan filter, bukan identitas halaman detail. Submit tetap melalui Livewire dengan validasi/otorisasi existing; tidak menambah endpoint POST biasa.

## DEC-038 - Pengelolaan Ruangan Terpadu dan Review Booking

- Atas permintaan pengguna, detail/edit Admin memakai satu view; informasi, fasilitas, status, PIC, dan akses unit tersedia di halaman yang sama. URL GET PIC/akses lama diarahkan ke bagian terkait; endpoint penulisan existing dipertahankan. Form per bagian tetap terpisah agar perubahan PIC/akses tidak ikut mengubah data ruangan. Old input checkbox dibatasi menurut bagian form yang gagal.
- Kewenangan master data tetap Admin sesuai DEC-018; tidak memperluas hak PIC untuk mengubah assignment, akses, atau menghapus ruangan. PIC dapat membaca informasi PIC/akses pada detail ruangan dan memutuskan booking sesuai assignment.
- DELETE ruangan khusus Admin memerlukan konfirmasi, lock ruangan dan transaksi. Ruangan yang memiliki booking dalam status apa pun tidak boleh dihapus permanen; gunakan status nonaktif agar riwayat tetap utuh. Ruangan tanpa booking dihapus bersama pivot fasilitas/PIC/akses melalui FK cascade existing, tanpa menghapus akun/fasilitas/unit. Lock yang sama dengan submit booking mencegah pemeriksaan dan penghapusan bersaing dengan pengajuan.
- Daftar Pending menggunakan satu tombol Review: ringkasan ruangan, pemohon, snapshot unit, tanggal/jam, agenda, catatan pemohon, serta Setujui/Tolak dan Catatan. Catatan persetujuan opsional; penolakan tetap wajib sesuai DEC-029. Kolom nullable `decision_notes` menyimpan catatan kedua keputusan; `rejection_reason` tetap diisi untuk kompatibilitas penolakan lama. Halaman detail existing tetap berfungsi sebagai fallback tanpa JavaScript dan untuk riwayat, bukan langkah wajib approval.
- Logout POST seluruh role tetap logout guard, invalidate session dan regenerate CSRF, lalu menuju route home. Menggantikan redirect login DEC-011. Footer tanpa navigasi Jadwal/Login/Dashboard; tombol Keluar PIC/Admin berada bersama profil pada header, mengikuti pola pegawai.

## DEC-037 - Approval Langsung dan Kewenangan Admin

- Atas permintaan pengguna, Admin aktif boleh menyetujui/menolak seluruh pengajuan yang membutuhkan approval dan masih Pending, tanpa penugasan PIC. Ini menggantikan batas keputusan hanya PIC pada DEC-029; tidak memberi kewenangan mengubah keputusan final atau booking otomatis tanpa approval.
- PIC tetap hanya memutuskan ruangan penugasannya. Service memeriksa ulang role/status aktif dan assignment dalam transaksi, mempertahankan lock ruangan/booking, validasi kondisi/akses pemohon, waktu mulai, konflik, dan penolakan keputusan kedua. Aktor serta waktu keputusan dicatat pada decided_by/decided_at existing.
- Daftar approval menyediakan tombol Setujui/Tolak dengan dialog konfirmasi; alasan penolakan wajib maksimal 2.000 karakter, tetap terlihat pada detail pemohon. Halaman detail tetap tersedia untuk agenda/catatan lengkap dan jalur proses tanpa JavaScript. Tidak menambah kolom catatan persetujuan atau schema baru.
- Footer semua role memakai komponen dan konten beranda; tautan Dashboard menggantikan Login untuk akun masuk. Header dan konten minimal setinggi viewport, footer berada setelahnya sehingga perlu scroll bahkan pada halaman pendek. Menggantikan posisi footer di dalam area minimum layar pada DEC-036.

## DEC-036 - Laporan Bulanan dan Audit Antarmuka Seluruh Role

- Atas permintaan pengguna 2026-10-05, laporan bulanan aktif untuk Admin dan PIC melalui `/admin/reports/monthly` dan `/pic/reports/monthly`. Periode mengikuti tanggal rapat (WIB), bukan tanggal pengajuan atau keputusan. Pilihan bulan default bulan berjalan; filter ruangan opsional.
- Admin mencakup seluruh ruangan. PIC dibatasi penugasan ruangan saat laporan dibuka, termasuk ruangan nonaktif agar riwayatnya tetap dapat dilaporkan. Filter, CSV, dan versi cetak menggunakan scope yang sama; pegawai/tamu tidak dapat mengakses laporan. Ini bukan arsip penugasan historis atau laporan yang dibekukan.
- Rekap memisahkan seluruh status database. Jam terjadwal adalah jumlah durasi Approved + Completed, bukan okupansi aktual/kehadiran. Pending lewat waktu tetap Pending; tidak mengaktifkan expiry, auto Completed, pembatalan, atau kebijakan baru lifecycle booking.
- Rincian layar dipaginasi; CSV dan cetak/PDF melalui browser mencakup semua hasil filter. CSV UTF-8 menggunakan snapshot unit pengajuan, mengamankan nilai yang dapat dibaca sebagai formula spreadsheet, serta tidak menyertakan token pengajuan/catatan privat. Tidak menambah dependency PDF/Excel, schema, atau email otomatis.
- Audit melengkapi detail ruangan umum dengan data nyata (menggantikan route skeleton). Pegawai hanya dapat membuka ruangan aktif; PIC dapat membuka ruangan aktif atau nonaktif yang ditugaskan kepadanya; Admin dapat membuka seluruhnya. Aksi booking tetap mengikuti authorization dan validasi existing.
- Dashboard Admin menampilkan ringkasan booking nyata dan lima pengajuan terbaru; angka rapat berlangsung mengikuti Approved pada interval waktu saat ini. Halaman dashboard alternatif tetap menampilkan konteks role akun.
- Tipografi aplikasi: informasi utama/input/tombol 16px, keterangan/badge minimal 14px; heading mengikuti skala existing. Layout menggunakan tinggi minimum layar dengan konten fleksibel agar footer mencapai bawah layar pada halaman pendek. Tabel tetap dapat digulir di dalam container, bukan melebarkan halaman.

## DEC-035 - Penerapan Logo Resmi Pelindo Multi Terminal (SPMT)

- Menggantikan tampilan teks merek sementara `pelindo ∿ Multi Terminal` dengan aset logo resmi PT Pelindo Multi Terminal (SPMT) sesuai arahan pengguna.
- Menyediakan komponen Blade reusable `<x-application-logo>` dengan dukungan varian tema:
  - `variant="light"` menggunakan `public/images/pelindo-logo.png` (warna cyan `#2FA4D7` dan corporate blue `#0E73A7` resmi) untuk permukaan latar terang seperti header pengguna (`layouts/user.blade.php`).
  - `variant="dark"` menggunakan `public/images/pelindo-logo-white.png` (teks dan kontur putih kontras tinggi dengan aksen cyan `#2FA4D7`) untuk permukaan latar gelap (`primaryDark` `#0E336A`) seperti sidebar jadwal (`layouts/schedule.blade.php`) dan panel samping login (`auth/login.blade.php`).
- Tidak mengubah route, otorisasi, controller, maupun menambah dependensi baru.

## DEC-034 - Pemilihan Waktu Booking dengan Jam Kerja Fleksibel

- Pengguna menyetujui jam kerja 08.00–17.00 sebagai default pilihan waktu, bukan batas keras. Opsi tampilkan jam di luar jam kerja menyediakan pilihan sepanjang hari; tidak menambah approval khusus atau pembatasan akhir pekan.
- Form memakai pilihan jam mulai interval 15 menit dan durasi 30 menit, 1 jam, 2 jam, atau jam selesai custom. Jam selesai otomatis mengikuti jam mulai/durasi; durasi yang melewati tengah malam mengosongkan jam selesai dan meminta koreksi, tidak memutar waktu ke hari berikutnya. Pilihan jam kerja dan durasi berasal dari config/booking.php.
- Ringkasan memberi pengingat bila waktu berada di luar jam kerja. Validasi server minimal dua jam, tanggal yang sama, konflik, akses, dan approval ruangan tetap berlaku. Interval picker merupakan bantuan UI, bukan constraint baru untuk data booking.

## DEC-033 - Kapasitas Tidak Ditampilkan dan Label Admin

- Sesuai permintaan pengguna, kapasitas dihapus dari seluruh UI ruangan (kartu/detail/form admin, katalog pegawai/PIC, form booking, dan panduan dashboard). Kolom database tetap disimpan untuk kompatibilitas: room baru tanpa kapasitas menggunakan 0, edit tanpa kapasitas mempertahankan nilai lama. Validasi tetap berlaku jika field dikirim oleh klien lama.
- Seluruh label role administratif menjadi Admin, termasuk dashboard, header, ringkasan akun, tabel/detail user dan pilihan role. Nilai internal super_admin beserta Gate/constraint database tetap dipertahankan untuk akun existing; roleLabel memisahkan label dari identifier. Nama pribadi akun existing tidak diubah otomatis.

## DEC-032 - Ruangan Saya PIC berdasarkan Penugasan

- Halaman `/pic/rooms` memakai controller baca khusus dan relasi managedRooms akun login, menggantikan skeleton sesuai penugasan pengguna. Scope mengikuti pivot room_pics, bukan unit kerja; tidak otomatis memperlihatkan seluruh room kepada admin yang membuka URL ini.
- Menampilkan seluruh room penugasan termasuk nonaktif/perawatan dengan label kondisi, pagination, serta tautan kalender berfilter room. Menggunakan kartu Blade bersama dalam mode managed tanpa tombol booking pribadi. Tidak mengubah Gate, aturan booking, approval, atau data penugasan.

## DEC-031 - Jadwal Ruangan pada Beranda Sebelum Login

- Sesuai permintaan pengguna, bagian Kenali pilihan ruangan dan kartu di `/` diganti kalender yang memakai tampilan/interaksi jadwal pegawai. Tautan navigasi dan hero menuju Jadwal Ruangan.
- Menggantikan batas jadwal tamu DEC-030: tamu boleh membaca nama ruangan, tanggal, jam, dan status okupansi hanya untuk room aktif dengan access_type=all. Agenda, pemohon, unit, catatan, dan token tetap privat. Booking tetap memerlukan login.
- PublicRoomSchedule memakai ulang RoomSchedule dengan scope publik pada pilihan/filter dan query booking, termasuk saat refresh atau perubahan akses room. Gate kalender internal dan route pegawai/admin tetap berlaku. Tidak menambah dependency/schema.


## DEC-030 - Beranda Tamu dan Jadwal Booking Terhubung

- Pengguna meminta beranda sebelum login yang mengikuti gaya dashboard pegawai serta jadwal penggunaan nyata, termasuk di atas form booking. Root `/` memakai HomeController/layout public, pengantar dan kartu yang sama gayanya. Akun yang sudah login diarahkan ke dashboard role masing-masing.
- Tamu hanya melihat room aktif dengan akses all, nama/lantai/kapasitas/fasilitas/status operasional. Tidak memuat deskripsi internal, PIC/unit, pemohon, agenda, catatan, atau jadwal. Partial room-catalog-card dipakai ulang tanpa membuka Gate RoomCatalog Livewire kepada tamu.
- Tombol tamu secara eksplisit Login untuk Booking. Param room hanya menerima ID numerik room publik aktif/operasional dan disimpan ke session sebagai pilihan. Login sukses user/PIC melanjutkan ke form room itu; admin tetap ke dashboard admin. URL redirect dibuat dari route server, tidak menerima return URL bebas. Pilihan bertahan saat login gagal; dikonsumsi setelah login; room yang kemudian nonaktif membawa user ke katalog. Halaman login menjelaskan pilihan tersebut.
- `/schedule` dan `/admin/schedule` kini menampilkan RoomSchedule Livewire, bukan kalender JavaScript dummy. BookingSchedule mengambil field okupansi saja dari Pending/Approved/Completed: ID room/booking, nama room, tanggal, waktu, status. Tidak mengirim field rapat privat atau token dalam HTML/snapshot jadwal.
- Kalender 42 hari dengan navigasi bulan/Hari Ini, lompat tanggal, filter room, jumlah booking dan penanda warna per tanggal. Rincian jam/room ditampilkan dalam panel harian yang cukup besar dan turun ke bawah pada mobile; kalender dapat digeser lokal. Room nonaktif yang memiliki jadwal tersimpan tetap terlihat beserta peringatan.
- Sesuai permintaan warna pengguna, menambah token warning dan warningDark pada Tailwind. Kuning = Pending; biru = Terjadwal; merah = Sedang berlangsung (Approved dan start <= sekarang < end); abu-abu = waktu lewat/Completed. Semua memakai label teks. Pending yang belum selesai tidak dianggap sedang digunakan. Status waktu dihitung dalam WIB tanpa mengubah status booking tersimpan. Cancelled/Rejected tidak tampil sebagai okupansi.
- Sumber data yang sama dipakai panel jadwal room di atas form. Satu field tanggal mengatur jadwal sekaligus tanggal booking, tanpa mengganti room. Kalender lengkap dapat dibuka di tab baru dengan filter room/tanggal, sehingga isian form tetap. Detail booking menautkan tanggal dan room langsung ke kalender.
- Pembaruan otomatis 30 detik dan tombol refresh kalender, dengan label waktu WIB/offline. Jadwal adalah informasi pada waktu pembacaan, bukan penguncian slot. Lock/transaksi dan validasi konflik saat submit tetap utuh. Tidak menambah library/schema atau memodifikasi booking existing.

## DEC-029 - Booking Aktif dan Data Ruangan per Lantai

- Pengguna meminta data ruangan dan memastikan booking/pengajuan sudah bisa dilakukan. Ini penugasan eksplisit backend booking dan alur keputusan PIC, menggantikan batas pratinjau pada DEC-028 untuk modul tersebut.
- Rincian pengguna berjumlah 10 (lantai 2:1, 3:2, 4:2, 6:2, 7:3), walaupun total awal disebut 9. Pertanyaan klarifikasi diajukan; belum ada jawaban saat implementasi. Mengikuti rincian per lantai sebagai asumsi yang disampaikan. Tiga ruangan lantai 7 sementara dinamai Selat Malaka I/II/III; lainnya memakai nama lantai/nomor sampai nama resmi diberikan.
- Semua ruangan baru access_type=all. Tujuh ruangan non-Selat Malaka langsung Approved sesudah validasi, tanpa izin/PIC. Asumsi Selat Malaka: semua pegawai boleh mengajukan, tetapi memerlukan approval PIC. PIC disalin dari penugasan Selat Malaka existing (lokal: Room PIC Demo), bukan memilih akun atau memberi role baru. Nama/PIC/batas akses masih dapat dikoreksi melalui administrasi.
- BookingRoomsSeeder terpisah dari FoundationSeeder agar data fixture/master existing tidak direset. Idempotent berdasarkan kode SPMT-L{lantai}-{nomor}; isian admin pada record existing tidak ditimpa saat dijalankan ulang. Empat record demo lama hanya dinonaktifkan bila kode dan deskripsi demo cocok; record/relasinya dipertahankan. Kapasitas 0 sebagai belum diisi (ditampilkan demikian), fasilitas kosong, tanpa mengarang data fasilitas/kapasitas resmi.
- Tabel bookings menyimpan pemohon, room, snapshot nama unit dan ID unit, tanggal/waktu WIB, agenda/catatan, status, kebutuhan approval saat pengajuan, token idempotensi, hash isian, serta jejak keputusan PIC. Tidak ada jumlah peserta. Foreign key melindungi riwayat; nama unit dipertahankan meskipun unit pemohon berubah.
- BookingService menjalankan transaksi dengan lock room sebelum memeriksa konflik dan insert. Pending/Approved memblokir interval setengah terbuka `[mulai, selesai)`; booking bersebelahan diperbolehkan tanpa buffer untuk implementasi awal. Seluruh penulisan booking/keputusan menggunakan lock room yang sama; request bersamaan diuji dengan dua proses PostgreSQL. Tidak memakai constraint overlap lintas baris; penulisan booking baru wajib melalui service ini.
- Waktu minimal dua jam WIB, urutan waktu, akun, akses unit dan kondisi room divalidasi ulang saat submit. Room dengan approval wajib mempunyai PIC aktif. Token dikunci di Livewire dan unik per pemohon; retry isian yang sama mengembalikan record yang sudah tersimpan, bukan membuat duplikat. Token sama dengan isian berbeda ditolak.
- PIC aktif yang ditugaskan boleh menyetujui/menolak Pending. Satu keputusan PIC cukup; alasan penolakan wajib. Keputusan kedua ditolak. Approval mengecek ulang kondisi/access pemohon dan ruangan, konflik, serta memastikan waktu mulai belum lewat. Batas dua jam berlaku pada pengajuan, bukan waktu PIC memberikan keputusan. Admin dapat memantau seluruh pengajuan namun tidak mempunyai override keputusan.
- My Booking/list/detail hanya pemohon. Admin list/detail seluruh booking; PIC list/detail hanya ruangan penugasannya. Semua route/mutasi tetap auth/Gate/CSRF. Tidak mengaktifkan pembatalan, penjadwalan ulang, expiry Pending, auto Completed, notifikasi, atau kalender data nyata; kebutuhan tersebut dicatat untuk tahap selanjutnya.

## DEC-028 - Satu Katalog untuk Memulai Booking dan Batas Dua Jam

- Koreksi pengguna menggantikan alur DEC-027: daftar ruangan dan awal booking disatukan di `/rooms` dengan satu menu Booking Ruangan. Dashboard hanya ringkasan tiga kartu dari komponen/data katalog yang sama, bukan cuplikan My Booking. My Booking khusus pengajuan/riwayat pribadi, tidak mengulang katalog.
- Kartu operasional dengan akses unit yang sesuai membuka `/my-bookings/create?room_id=...`; form tidak lagi memiliki dropdown. Tanpa ID diarahkan ke katalog; ID invalid/hilang menghasilkan 404. ID komponen Livewire dikunci. Ganti ruangan membuka form baru dengan penjelasan isian belum disimpan.
- Sesuai instruksi minimal dua jam, pemeriksaan rencana memakai waktu server. Asumsi implementasi zona bisnis adalah WIB (`Asia/Jakarta`) yang ditampilkan pada field dan dikonfigurasi di `config/booking.php`, tanpa mengubah timezone global aplikasi. Awal tepat dua jam diterima; karena input presisi menit, batas dengan detik dibulatkan ke menit berikutnya. Waktu dihitung ulang setiap pemeriksaan, termasuk melewati tengah malam.
- Bentuk form satu tanggal berarti akhir wajib setelah awal pada hari yang sama. Lintas hari belum didukung, bukan keputusan larangan bisnis permanen. Akses restricted pada pratinjau hanya unit yang secara eksplisit terdaftar; tidak menambah pewarisan ke turunan yang belum diputuskan. Status aktif/operasional dan akses terkini diperiksa ulang pada server, bukan hanya tombol kartu.
- `BookingPreparation` memisahkan aturan pemeriksaan dari controller/komponen. Livewire Periksa Rencana memvalidasi waktu/isian/akses, tanpa menyimpan atau mengunci jadwal. Pesan pemeriksaan disembunyikan saat isian berubah. Pengajuan tetap nonaktif, dan bentrok belum diperiksa. Backend tahap 5/approval/kalender tidak otomatis diaktifkan.
- Katalog umum mengikuti Gate access-general sesuai `/rooms` existing; admin dapat membaca tanpa aksi booking. Form/pemeriksaan tetap access-employee, tanpa menambah hak booking pribadi admin.

### Wajib saat backend pengajuan diaktifkan

- Validasi ulang batas dua jam, akses unit, akun, dan kondisi ruangan saat submit nyata, termasuk ketika form lama masih terbuka. Pemeriksaan rencana bukan bukti slot tersedia.
- Pemeriksaan overlap Pending/Approved dan penyimpanan harus aman terhadap dua request bersamaan (transaksi dan penguncian/constraint database yang sesuai), bukan sekadar cek lalu insert. Tangani pengiriman ulang agar tidak menggandakan pengajuan.
- Perubahan status ruangan/akses sesudah booking, pembatalan, expiry Pending, aturan buffer, dan kewenangan approval masih membutuhkan keputusan tersendiri sesuai PROJECT_CONTEXT. Jangan menganggap aturan tersebut telah diterapkan.

## DEC-027 - Booking Tanpa Jumlah Peserta dan Halaman Cari Ruangan

- Sesuai permintaan pengguna, jumlah peserta dihapus dari form, detail booking, daftar approval, dan panduan UI. Kapasitas master ruangan tetap menjadi informasi ruangan.
- Halaman dan route Cari Ruangan dihapus; URL lama menghasilkan 404. Booking Ruangan (`/my-bookings/create`) tersedia langsung di navbar user dan dashboard.


## DEC-026 - Navigasi Administrasi Tanpa Menu Lantai

- Pengguna meminta frontend Kelola User, Unit Organisasi, dan Fasilitas serta penghapusan bagian Lantai. Ketiga modul memakai layout schedule pada daftar, tambah, edit, dan detail, dengan seluruh proses backend existing dipertahankan.
- Menu Lantai di kedua navigasi dan shortcut dashboard admin dihapus. Cakupan sementara, sesuai asumsi yang disampaikan sambil meminta klarifikasi, adalah penghapusan dari tampilan administrasi; bukan penghapusan data atau atribut lantai. Ruangan masih bergantung pada floor_id, sehingga route/backend/model/schema Lantai dipertahankan. Klarifikasi penghapusan menyeluruh belum dijawab; tidak menetapkan bahwa data lantai tidak lagi dibutuhkan.
- Tidak mengubah aturan organisasi, akses akun, relasi fasilitas, atau kewenangan role. Halaman form menggunakan komponen workspace baru agar desain dan aksesibilitas konsisten tanpa mengubah komponen skeleton modul lain.


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
- Admin.

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
