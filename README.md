# Sistem Booking Ruang Rapat

Aplikasi internal untuk membantu pegawai Pelindo Multi Terminal melihat jadwal dan mengajukan penggunaan ruang rapat. Admin mengelola data dan akses, sedangkan Room PIC menangani persetujuan untuk ruang yang menjadi tanggung jawabnya.

> **Status:** aplikasi aktif dikembangkan. Beberapa kemampuan operasional, seperti pengiriman WhatsApp, memerlukan konfigurasi server tambahan. Lihat bagian [konfigurasi](#konfigurasi-opsional) dan [dokumentasi proyek](#dokumentasi).

![Ilustrasi ruang rapat](public/images/Beranda.jpeg)

*Ilustrasi ruang rapat yang digunakan pada beranda aplikasi; gambar ini bukan tangkapan layar antarmuka.*

## Fitur utama

- **Jadwal ruangan:** kalender dan daftar penggunaan untuk membantu melihat waktu yang tersedia.
- **Booking:** pegawai memilih ruangan, tanggal, waktu, dan agenda. Sistem memeriksa akses, status ruangan, waktu yang sudah lewat, dan konflik jadwal saat pengajuan.
- **Persetujuan:** booking yang memerlukan persetujuan masuk ke antrean PIC ruangan atau Admin. PIC/Admin dapat menyetujui atau menolak dengan alasan.
- **Administrasi:** Admin mengelola akun, unit organisasi, kantor/cabang, lantai, fasilitas, ruangan, PIC, akses ruangan, serta kebutuhan approval.
- **Laporan bulanan:** Admin dan PIC dapat melihat rekap berdasarkan bulan dan ruangan, mengunduh CSV, serta mencetak atau menyimpan PDF melalui browser.
- **Pendataan tamu:** tamu dapat mengisi registrasi sementara dan melihat jadwal publik terbatas. Pengajuan booking tetap dilakukan oleh pegawai atau resepsionis.
- **Notifikasi WhatsApp (opsional):** antrean pesan dapat dikirim melalui Fonnte setelah token, penerima, dan scheduler dikonfigurasi.

## Alur penggunaan

1. Admin menyiapkan kantor/cabang, akun dan unit organisasi, lantai, fasilitas, ruangan, aturan akses, serta PIC.
2. Pegawai masuk, melihat jadwal, memilih ruangan dan mengirim booking.
3. Server memeriksa izin pemohon, kondisi ruangan, jam rapat, serta bentrok dengan booking aktif.
4. Jika ruangan atau permintaan tambahan memerlukan persetujuan, booking menunggu keputusan PIC/Admin. Jika tidak, booking dapat langsung disetujui.
5. Pemohon melihat status dan riwayat booking melalui halaman **My Booking**. Admin/PIC dapat memantau data dan laporan sesuai aksesnya.

Status Pending dan Approved menahan slot waktu. Rejected dan Cancelled tidak memblokir slot. Booking multi-tanggal dibuat sebagai pengajuan terpisah untuk tiap tanggal.

## Tech stack

| Bagian | Teknologi |
| --- | --- |
| Backend | PHP 8.1+, Laravel 10 |
| UI | Blade, Livewire 3, Tailwind CSS 3 |
| Build aset | Vite 4, Node.js dan npm |
| Database bawaan konfigurasi | PostgreSQL |
| Integrasi WhatsApp | Fonnte, opsional |

Autentikasi menggunakan session Laravel; Sanctum juga tersedia untuk API. Aplikasi menggunakan zona waktu kerja WIB pada alur booking, sementara konfigurasi zona waktu inti Laravel mengikuti `config/app.php`.

## Persyaratan

- PHP 8.1 atau lebih baru dengan ekstensi yang dibutuhkan Laravel dan driver `pdo_pgsql`.
- Composer.
- Node.js dan npm.
- PostgreSQL yang berjalan dan database kosong yang sudah dibuat.

## Menjalankan secara lokal

Clone repository, lalu jalankan dari direktori proyek:

```bash
composer install
npm install
```

Buat file environment dan kunci aplikasi:

```bash
cp .env.example .env
php artisan key:generate
```

Di Windows PowerShell, bila `cp` tidak tersedia gunakan `Copy-Item .env.example .env`.

Perbarui `.env` agar cocok dengan PostgreSQL lokal Anda, khususnya `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`. Buat database tersebut terlebih dahulu, lalu jalankan:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Buka alamat yang ditampilkan oleh `php artisan serve` (umumnya <http://127.0.0.1:8000>). Untuk pengembangan frontend dengan hot reload, jalankan `npm run dev` pada terminal kedua.

Seeder menyiapkan data fondasi dan ruangan. Akun awal atau kredensial login tidak dijanjikan oleh README ini; ikuti prosedur provisioning akun yang berlaku untuk lingkungan Anda. Jangan gunakan data seed lokal sebagai data produksi.

## Konfigurasi opsional

### WhatsApp melalui Fonnte

Secara default integrasi dinonaktifkan. Untuk mengaktifkannya, atur `WHATSAPP_ENABLED=true` dan `FONNTE_TOKEN` di `.env`, kemudian konfigurasi nomor penerima serta template melalui halaman Admin. Pengiriman memerlukan scheduler Laravel berjalan setiap menit:

```bash
php artisan schedule:work
```

Pada server produksi, daftarkan cron Laravel Scheduler sesuai panduan Laravel. Periksa status melalui `php artisan whatsapp:status`. Simpan token sebagai rahasia dan jangan commit file `.env`.

### Registrasi tamu

Pengaturan masa aktif tersedia di `.env.example` melalui `GUEST_EXPIRY_MODE` dan `GUEST_ACTIVE_DAYS`. `GUEST_BRANCH_ID` dapat diisi dengan ID lokasi resepsionis; bila kosong, aplikasi memakai lokasi pertama dari master data.

## Perintah pengembangan

```bash
php artisan test       # menjalankan tes
npm run dev            # Vite dengan hot reload
npm run build          # build aset untuk penggunaan
php artisan route:list # melihat route aplikasi
```

## Tangkapan layar

Repository saat ini menyediakan ilustrasi beranda, tetapi belum menyertakan tangkapan layar aktual halaman aplikasi. Setelah mengambil screenshot dari aplikasi yang berjalan, letakkan file di `docs/screenshots/` dan tampilkan di sini, misalnya:

```markdown
![Jadwal ruangan](docs/screenshots/jadwal-ruangan.png)
```

## Dokumentasi

- [Konteks dan cakupan proyek](docs/PROJECT_CONTEXT.md)
- [Roadmap](docs/ROADMAP.md)
- [Progress pengembangan](docs/PROGRESS.md)
- [Keputusan proyek](docs/DECISIONS.md)
- [Panduan frontend](docs/FRONTEND_GUIDE.md)
- [Panduan WhatsApp](docs/WHATSAPP.md)
- [Panduan pendataan tamu](docs/GUESTS.md)

## Lisensi dan penggunaan

Aplikasi ini dibuat untuk kebutuhan internal. Pastikan Anda memiliki izin dan mengikuti kebijakan organisasi sebelum menggunakan atau mendistribusikan kode maupun data. Lisensi framework Laravel tidak otomatis menjadi lisensi repository ini.
