# Latar pendataan tamu

- Mode: built-in imagegen, gambar baru tanpa referensi.
- Aset aplikasi: `public/images/guest-harbor-background.png` (1536 x 1024).
- Dipakai hanya pada form `/tamu`, dengan opacity dan lapisan gradient token aplikasi. Halaman jadwal tidak memakai gambar ini. Revisi 2026-10-20 menampilkan pelabuhan multipurpose non-peti kemas: kapal kargo umum, crane kapal, gudang, curah dan gulungan baja. URL aset diberi versi noncontainer agar browser mengambil gambar baru.

Prompt final:

> Use case: photorealistic-natural. Asset type: scenic background image only for Indonesian multipurpose port visitor registration website, wide landscape 1536x1024. Primary request: a realistic NON-CONTAINER multipurpose port handling bulk and breakbulk cargo, appropriate to PT Pelindo Multi Terminal. Show a conventional general cargo vessel with empty open deck and its own shipboard derrick cranes alongside a quay, subtle covered bulk warehouses and a few cylindrical steel coils on the distant quay. Calm sea, hazy soft morning light, quiet professional atmosphere. Absolutely NO shipping containers anywhere, no container stacks, no container ships, no box-shaped freight cargo, no container gantry cranes. Restrained navy blue, muted ocean blue, pale misty blue and off-white palette. Low contrast atmospheric photography, softened detail. Ship and quay concentrated in lower third and outer edges, spacious uncluttered sky in upper center behind a large white form card. No text, letters, numbers, logos, watermark, people in foreground. Not a website mockup.
