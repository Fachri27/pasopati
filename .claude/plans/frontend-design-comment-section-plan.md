# Rencana Redesign Kolom Komentar Pasopati

## Subjek & brief
- Produk: Pasopati — publikasi data lingkungan/kehutanan (Auriga Nusantara).
- Halaman: artikel data (`page-expose`, `page-ngopini`).
- Job kolom komentar: memfasilitasi diskusi berstruktur yang mudah dibaca, dengan hierarki balasan jelas.
- Referensi visual: screenshot nested comment thread dengan avatar bulat berwarna mint muda, nama bold, tanggal di kanan atas, body rapi, aksi "BALAS", dan toggle "TUTUP N BALASAN" dengan garis penghubung vertikal.

## Keputusan desain

### Palette (6 warna)
| Token | Hex | Peran |
|-------|-----|-------|
| forest | `#376A64` | aksi balas, mention, link, avatar text |
| ink | `#1a1a1a` | nama, judul, body utama |
| ink-2 | `#7a6e60` | tanggal, meta, placeholder |
| paper | `#ffffff` | background |
| line | `#E5E7EB` | divider antar komentar & benang balasan |
| avatar-bg | `#e5efed` | latar avatar (mint muda) |
| accent | `#bc4a3c` | tombol suka aktif, badge "Anda" |

### Tipografi
- Nama: Poppins 600, 15–16 px, `#1a1a1a`.
- Tanggal: 10–11 px, uppercase, tracking 0.08 em, `#7a6e60`.
- Body: Inter 15–16 px, leading 1.7, `#1a1a1a`/80.
- Aksi (BALAS, TUTUP N BALASAN): 11 px, uppercase, tracking 0.12 em, font-weight 700, `#376A64`.

### Layout
- Kontainer: `max-w-[720px]` di tengah.
- Komentar utama: flex row, avatar 44 px di kiri, konten di kanan, gap 14–16 px.
- Header konten: flex justify-between — nama bold kiri, tanggal kanan.
- Aksi: baris horizontal di bawah body; urutan: suka (ikon+angka) → BALAS → terjemah → toggle balasan.
- Balasan: blok `.replies` dengan garis vertikal kiri; tiap balasan `.reply-item` memiliki kait melengkung (`::before`) seperti CSS yang sudah ada. Avatar balasan sedikit lebih kecil (36 px).
- Composer: tetap ada di atas daftar, diselaraskan visualnya (avatar, input minimal, toolbar format).

### Signature element (aesthetic risk)
Benang balasan (thread line) dengan kait melengkung pada setiap balasan, bukan box nesting berbayang. Ini mengurangi kebisingan visual dan membuat hierarki diskusi terbaca seperti konversasi berthread — berani tapi fungsional, khas editorial data.

## Implementasi

### File yang diubah
1. `resources/views/livewire/comment-section.blade.php`
   - Susun ulang markup komentar & balasan agar sesuai referensi.
   - Gunakan `.replies` dan `.reply-item` untuk thread line.
   - Perbarui aksi menjadi "BALAS" uppercase, toggle "TUTUP N BALASAN" dengan chevron.
   - Pertahankan semua fitur backend: suka, balas, terjemah, urutkan, captcha.
2. `resources/css/app.css`
   - Rapikan `.comment-item`, `.replies`, `.reply-item` agar spacing, ukuran avatar, dan garis benang cocok dengan referensi.
   - Hapus atau tandai legacy `.comment-reply` jika tidak lagi dipakai.
3. `resources/views/livewire/komentar.blade.php`
   - Biarkan tidak diubah karena tidak dipakai di halaman mana pun; hindari konflik inline style.

### Yang tidak diubah
- `app/Livewire/CommentSection.php`: logika submit, balasan, reaksi, terjemah, sort tetap.
- Tata letak halaman: `<livewire:comment-section>` sudah benar di `page-expose` dan `page-ngopini`.

### Responsive
- Desktop: avatar 44 px, balasan 36 px, benang di kiri.
- Mobile: tetap avatar 40 px, benang tetap terlihat, padding balasan sedikit lebih kecil.
- Reduced motion: transisi Alpine tetap halus, tidak ada animasi esensial.
