# Panduan Agent

## Peta konteks

Sebelum mengubah kode, baca:

- `docs/PROJECT_CONTEXT.md`: tujuan, requirement, dan batasan scope.
- `docs/ROADMAP.md`: tahap pengembangan.
- `docs/PROGRESS.md`: pekerjaan selesai, kondisi terkini, dan langkah berikutnya.
- `docs/DECISIONS.md`: keputusan yang sudah dibuat; jangan mengubahnya tanpa alasan.

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
- Tahap aktif mengikuti roadmap dan progress. Tahap 0 hanya dokumentasi: jangan membuat migration, model, controller, route, view, frontend, atau fitur, dan jangan mengubah logic aplikasi. Tunggu instruksi berikutnya sebelum melanjutkan tahap.

## Frontend

- Frontend awal hanya untuk testing functionality; gunakan Blade/HTML sederhana.
- Tidak perlu desain kompleks atau CSS custom berlebihan.
- Sediakan semua tombol, form, tabel, filter, dan navigasi yang dibutuhkan untuk menguji fungsi.
- Data harus berasal dari Laravel, bukan hardcoded di HTML.
- Frontend final akan diganti/dikembangkan oleh anggota tim lain menggunakan HTML dan Tailwind CSS.
- Buat struktur Blade yang mudah diganti tanpa mengubah backend.
