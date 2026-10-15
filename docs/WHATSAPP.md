# Panduan WhatsApp

## Alur utama

**Pegawai booking → aplikasi mencatat pesan → scheduler → Fonnte → WhatsApp penerima.**

Satu nomor sistem pada **Device Fonnte** mengirim untuk seluruh pegawai. Pegawai/PIC/penerima tidak perlu mendaftar di Fonnte. Nomor tujuan diambil dari `users.whatsapp_number`; pengaturan menyimpan ID akun, bukan salinan nomor. Nomor pada riwayat pesan adalah snapshot saat pesan dibuat.

Email akun tetap unik. Beberapa akun boleh memakai nomor WhatsApp yang sama; jika dipilih bersamaan, hanya satu pesan dikirim ke nomor itu untuk setiap jenis notifikasi pada booking yang sama.

| Jenis pesan | Pemicu | Penerima |
| --- | --- | --- |
| Pengajuan approval | Booking baru Pending | PIC aktif yang ditugaskan pada ruangan jika opsi otomatis dipilih, serta Admin tambahan yang dipilih |
| Konsumsi | Booking Approved dan PIC/Admin menyetujui Snack/Makanan | Akun aktif yang dipilih Admin sebagai penerima konsumsi |

Booking ditolak atau tanpa konsumsi yang disetujui tidak membuat pesan konsumsi. Permintaan konsumsi tetap memerlukan review PIC/Admin meskipun ruangan biasanya langsung Approved (DEC-049). Booking gagal tidak meninggalkan pesan; retry pengajuan tidak mencatat pesan ganda. Booking lama tidak otomatis dibuatkan pesan.

## Yang dilakukan Admin di website

1. **Kelola User → Tambah/Edit:** isi nomor WhatsApp penerima (08… atau +62…). Sistem menormalisasi ke format internasional. Nomor opsional; akun tanpa nomor valid tidak menerima pesan.
2. **Notifikasi WhatsApp** (`/admin/whatsapp`):
   - Ikuti tiga langkah pada setiap bagian: **Aktifkan pesan → Pilih penerima → Susun pesan**, lalu klik **Simpan pengajuan** atau **Simpan konsumsi**. Badge menunjukkan pengaturan yang sudah tersimpan; perubahan belum berlaku sebelum disimpan.
   - Aktifkan pengajuan, centang PIC otomatis bila diperlukan, dan pilih Admin penerima tambahan.
   - Pilih akun petugas konsumsi dan aktifkan kebutuhan konsumsi. Memilih akun tidak mengubah role/hak aksesnya.
   - Edit teks pesan dan centang **Data yang disertakan** (ruangan, tanggal, jam, dll.). Baris data otomatis ditambahkan; melepas centang menghapus datanya. Tidak perlu mengetik variabel sendiri. Klik **Simpan**; **Pratinjau** memakai booking terbaru tanpa menyimpan atau mengirim.
   - Periksa kesiapan nomor PIC, filter riwayat, lihat isi pesan, dan antrekan ulang pesan gagal.
3. Buat booking uji coba sebagai Pegawai aktif dengan akses ruangan. Centang persiapan rapat, jumlah peserta, dan konsumsi jika ingin menguji dua pesan. Setujui melalui PIC/Admin setelah pesan pengajuan diproses, lalu centang jenis konsumsi yang disetujui untuk menguji pesan konsumsi.

Hanya Admin mengelola nomor/penerima/template/riwayat. PIC tetap melakukan approval sesuai penugasan. Dua jenis notifikasi awalnya nonaktif setelah migrasi agar Admin meninjau penerima terlebih dahulu. Nomor uji coba lama yang tidak cocok dengan satu akun yang memenuhi syarat ditandai untuk dipilih ulang, tanpa membuat akun atau mengubah role otomatis.

Template baru berlaku untuk pesan yang dibuat berikutnya; isi antrean lama tetap tersimpan. Sebelum kirim, sistem memeriksa ulang akun aktif, nomor, pilihan penerima, penugasan PIC, jenis notifikasi aktif, dan status booking. Jika berubah, pesan lama dilewati (`skipped`).

## Setup pengirim dan hosting (sekali oleh pengelola teknis)

1. Fonnte: **Device → Add Device → Connect**. Scan QR menggunakan WhatsApp nomor sistem melalui **Perangkat tertaut**, lalu pastikan **Connected**.
2. Klik **Token pada Device pengirim**. Token **Settings** adalah account token, bukan token pengiriman. [Device](https://docs.fonnte.com/device/), [Account Token](https://docs.fonnte.com/account-token/).
3. Isi `.env` di root proyek (sejajar `artisan`), tanpa menggandakan key:

   ```dotenv
   WHATSAPP_ENABLED=true
   FONNTE_TOKEN=isi_token_device_kamu
   ```

4. Jalankan `php artisan migrate` dan `php artisan config:clear`. Token asli hanya di `.env`, bukan source/chat. “Token tersedia” belum membuktikan token valid atau Device Connected.
5. Lokal: jalankan `php artisan schedule:work` dan biarkan terminal terbuka. Setelah `.env` berubah, hentikan dengan Ctrl+C dan jalankan kembali.
6. Hosting: atur cron server untuk menjalankan `schedule:run` setiap menit. Contoh (sesuaikan lokasi PHP/proyek):

   ```cron
   * * * * * /usr/bin/php /path/to/project/artisan schedule:run >> /dev/null 2>&1
   ```

   Hosting perlu mendukung cron dan PHP CLI; tidak perlu `queue:work`. Setelah terpasang, pengelola harian cukup memakai website. Atur `APP_URL` ke alamat website yang dapat dibuka penerima; tautan review tetap membutuhkan login dan izin PIC/Admin. [Scheduler Laravel](https://laravel.com/docs/10.x/scheduling#running-the-scheduler).

## Status dan pengiriman ulang

Pengiriman menunggu scheduler (sekitar satu menit) dan pemrosesan Fonnte. Periksa riwayat di website atau `php artisan whatsapp:status`.

| Status | Arti / tindakan |
| --- | --- |
| `pending` | Menunggu scheduler |
| `processing` | Sedang diproses; jika tertinggal, periksa provider sebelum tindakan manual |
| `accepted` | Fonnte menerima permintaan, belum bukti sampai/dibaca; cek WA penerima |
| `failed` / `provider_rejected` | Ditolak Fonnte; cek token Device dan koneksi, lalu pilih **Antrekan ulang** |
| `unknown` | Hasil tidak pasti; periksa provider sebelum mengirim ulang |
| `skipped` | Booking, penerima, nomor, akun, atau pengaturan berubah sebelum kirim |

Tombol **Antrekan ulang** memasukkan pesan ke antrean; scheduler yang mengirim. Retry hanya untuk `failed` dengan penerima dan booking masih sesuai. Tidak perlu booking ulang. Alternatif teknis:

```powershell
php artisan whatsapp:send
php artisan whatsapp:send --retry=1
php artisan whatsapp:status
```

`--retry=1` memakai ID pesan, bukan ID booking. “Pemrosesan antrean selesai” bukan bukti semua pesan berhasil. Belum ada webhook bukti sampai/dibaca. Untuk menghentikan semua pengiriman, set `WHATSAPP_ENABLED=false`, jalankan `config:clear`, dan restart scheduler; antrean tetap tersimpan.

## Peta file dan tabel

| File / tabel | Fungsi |
| --- | --- |
| `.env`, `config/whatsapp.php` | Token Device dan pengaktifan pengiriman server |
| `SaveUserRequest.php`, `Admin/UserController.php`, view `admin/users` | Validasi, normalisasi, dan pengelolaan nomor akun |
| `Admin/WhatsappController.php`, `SaveWhatsappSettingsRequest.php`, `WhatsappSettings.php` | Pengaturan Admin, validasi template/penerima, dan retry |
| `resources/views/admin/whatsapp/index.blade.php` | Halaman pengaturan, pratinjau, dan riwayat |
| `app/Services/BookingService.php`, `BookingWhatsapp.php` | Pemicu booking, penerima dari user/PIC, dan pengisian variabel template |
| `app/Services/WhatsappDelivery.php` | Pengiriman Fonnte dan hasilnya |
| `app/Console/Commands/*Whatsapp*.php`, `app/Console/Kernel.php` | Perintah teknis dan scheduler |
| `users.whatsapp_number` | Sumber nomor penerima |
| `whatsapp_recipients.user_id` | Akun yang dipilih Admin per tujuan; nomor legacy tidak dipakai mengirim |
| `whatsapp_templates` | Template, status aktif jenis pesan, dan opsi PIC otomatis |
| `whatsapp_messages` | Antrean, akun penerima, snapshot nomor/isi, dan hasil pengiriman |

Template sehari-hari diedit melalui website, tanpa mengubah kode. Nomor pengirim ditentukan Device/token Fonnte, bukan nomor pada akun user. Migrasi perubahan penerima ke user mempertahankan riwayat; rollback otomatis migrasi tersebut tidak didukung, gunakan backup database untuk pemulihan deployment.
