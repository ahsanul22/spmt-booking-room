# Database Singkat

Database project menggunakan **PostgreSQL**. Laravel membuat tabel melalui migration; pgAdmin dipakai untuk melihat tabel dan isinya.

## Isi tabel

| Tabel | Kegunaan |
| --- | --- |
| `organizational_units` | Unit kerja. `parent_id` menunjuk unit induk, sehingga Direktorat, Divisi, dan Departemen bisa disusun bertingkat. |
| `users` | Akun pegawai; satu role dan maksimal satu unit kerja per akun. |
| `floors` | Daftar lantai; lantai boleh belum memiliki ruangan. |
| `facilities` | Daftar fasilitas seperti projector dan whiteboard. |
| `rooms` | Identitas ruang, lantai, kapasitas, kondisi operasional, dan pengaturan akses/approval. |
| `facility_room` | Penghubung ruang dengan fasilitasnya. |
| `room_pics` | Penghubung ruang dengan satu atau beberapa PIC. |
| `room_unit_access` | Daftar unit yang diizinkan untuk ruang berakses `restricted`. |

Tabel bawaan Laravel (`password_reset_tokens`, `failed_jobs`, `personal_access_tokens`, dan `migrations`) tetap tersedia. Belum ada tabel booking.

Seluruh kolom `users`, termasuk role dan unit kerja, didefinisikan dalam satu file `2014_10_12_000000_create_users_table.php`. Migration unit kerja berjalan lebih dulu agar hubungan antar tabel bisa dibuat.

## Aturan dasar

- Role: `user`, `room_pic`, atau `super_admin`. Nama unit kerja bukan role.
- Satu lantai memiliki banyak ruang. Ruang bisa memiliki banyak fasilitas, PIC, dan unit yang diizinkan.
- `all` berarti terbuka untuk seluruh pegawai; `restricted` memakai daftar unit. Pewarisan akses ke unit turunan belum diputuskan.
- Status `available`, `maintenance`, dan `unavailable` menunjukkan kondisi ruang, bukan jadwal kosong/terisi.
- Kapasitas minimal 0, kode ruang unik jika diisi, dan pasangan pada tabel penghubung tidak boleh duplikat.
- Unit yang masih memiliki anak/user dan lantai yang masih memiliki ruang tidak bisa dihapus. Gunakan `is_active` untuk menonaktifkan.
- Menghapus salah satu sisi relasi hanya membersihkan baris penghubungnya, tidak menghapus data di sisi lain.
- Rule `EligibleRoomPic` memeriksa role PIC sebelum penugasan. Seeder sudah memakainya; modul pengelolaan PIC nanti harus memakai rule ini juga. Menulis langsung ke pivot tidak menjalankan validasi role otomatis.
- Belum ada validasi alur organisasi (termasuk siklus parent), kewajiban minimal satu PIC, perubahan role PIC, atau aturan booking. Itu perlu ditangani saat modul terkait dibuat.

## Data contoh development

Ada 3 unit berjenjang, 8 lantai, 7 fasilitas, dan 4 ruang. Selat Malaka berada di Lantai 7. Ruang contoh mencakup semua kombinasi akses terbuka/terbatas dan perlu/tidak perlu approval.

**Semua struktur organisasi dan rincian ruang adalah dummy, bukan data resmi perusahaan.**

| Email | Role | Password awal |
| --- | --- | --- |
| `admin@example.test` | Super Admin | `password` |
| `pic@example.test` | Room PIC | `password` |
| `pegawai@example.test` | Pegawai | `password` |

Akun sudah dapat dipakai di `/login` setelah Tahap 2A; panduan ada di `docs/AUTHENTICATION.md`. Seeder hanya berjalan jika `APP_ENV=local` atau `testing`. Menjalankannya ulang tidak menggandakan contoh atau mereset password akun yang sudah ada.

## Menjalankan

Pastikan service PostgreSQL aktif, extension PHP `pdo_pgsql` aktif, database sudah dibuat, dan `.env` berisi koneksi beserta password lokal. Contohnya tersedia di `.env.example`; jangan commit `.env`.

```sh
php artisan config:clear
php artisan migrate
php artisan db:seed
php artisan test
```

Test fondasi menggunakan schema PostgreSQL sementara bernama `foundation_test_*`, lalu menghapusnya setelah test. Tabel development tidak di-reset. User database untuk test perlu izin membuat schema. Jangan menggunakan database produksi untuk test.

Di pgAdmin: **Databases → spmt_booking_room → Schemas → public → Tables**. Klik kanan **Tables → Refresh** jika tabel belum terlihat, lalu klik kanan tabel → **View/Edit Data → All Rows**.
