# Panduan Agent

## Peta konteks

Sebelum mengubah kode, baca:

- `docs/PROJECT_CONTEXT.md`: tujuan, requirement, dan batasan scope.
- `docs/ROADMAP.md`: tahap pengembangan.
- `docs/PROGRESS.md`: pekerjaan selesai, kondisi terkini, dan langkah berikutnya.
- `docs/DECISIONS.md`: keputusan yang sudah dibuat; jangan mengubahnya tanpa alasan.
- Untuk task frontend, baca `docs/FRONTEND_GUIDE.md` dan `docs/FRONTEND.md`, lalu periksa halaman Jadwal Ruangan sebagai acuan visual.

## Aturan kerja

- Analisis struktur project sebelum mengubah kode dan gunakan Laravel convention.
- Jangan mengganti dependency tanpa kebutuhan.
- Kerjakan hanya task yang sedang diberikan; hindari overengineering.
- Gunakan validation dan authorization dengan benar.
- Jangan hardcode data bisnis ke frontend.
- Pisahkan business logic dari controller ketika logic mulai kompleks.
- Setelah pekerjaan selesai, jalankan test yang relevan. Catat hasil aktual atau alasan test tidak dijalankan; jangan mengarang hasil.
- Update `docs/PROGRESS.md` setelah pekerjaan selesai.
- Update `docs/DECISIONS.md` jika ada keputusan arsitektur atau bisnis baru.
- Tahap aktif mengikuti roadmap dan progress. Pengembangan frontend pada halaman yang ditugaskan sudah diperbolehkan setelah 4E. Jangan otomatis melanjutkan backend Booking, Approval, atau Kalender hanya karena mengerjakan tampilannya.

## Frontend

- Gunakan Blade + Tailwind CSS existing melalui Vite. Frontend sekarang dikembangkan sebagai UI aplikasi yang konsisten, responsif, dan siap digunakan; aturan lama yang membatasi UI ke HTML sederhana untuk testing sudah digantikan.
- Acuan utama: `resources/views/shared/schedule.blade.php` dan `resources/views/layouts/schedule.blade.php`, dapat dilihat di `/schedule` atau `/admin/schedule` setelah login.
- Ikuti sidebar biru gelap, header, tipografi, spacing, card putih, border halus, tombol, dan pola mobile referensi. Adaptasikan konten sesuai halaman; jangan menyalin kalender ke setiap halaman.
- Gunakan token `tailwind.config.js`: primary `#216EAD`, primaryDark `#0E336A`, secondary `#5EA9D8`, secondaryLight `#AFCFE4`, background `#F2F4F5`, surface `#FFFFFF`, muted `#A2A2A6`, text `#222424`, danger `#D4302C`. Pakai class token seperti `bg-primary`; hindari palet baru dan pengulangan hex di halaman.
- Gunakan ulang komponen/partial Blade. Layout schedule masih khusus jadwal: saat dipakai ulang, parameterkan judul, breadcrumb, dan menu aktif dengan perubahan terbatas; jangan meninggalkan judul Jadwal Ruangan di halaman lain. Koordinasikan perubahan layout/komponen bersama agar tidak mengganggu pekerjaan tim.
- Pertahankan route, nama field, method, CSRF, old input, error validasi, flash, pagination, dan authorization existing. Jangan menonaktifkan form backend aktif untuk menyesuaikan desain.
- Data bisnis berasal dari Laravel; jangan query database di Blade atau hardcode akun, ruangan, booking, statistik, dan izin. Modul tanpa backend memakai empty state/penanda pratinjau serta aksi proses nonaktif, tanpa memberi kesan data sudah tersimpan.
- HTML dari AI web menjadi bahan tampilan yang diintegrasikan ke Blade dan token yang sama. Jangan menambah CDN Tailwind, React/Vue, framework CSS, atau dependency lain tanpa kebutuhan task.
- Kerjakan hanya halaman yang ditugaskan. Periksa desktop/mobile, keyboard, label input, kontras, empty state, dan kondisi error. Ikuti verifikasi pada `docs/FRONTEND_GUIDE.md` dan catat hasil aktual.
