/**
 * Data mockup — seluruh isi halaman diambil dari sini.
 * Teks, tanggal, dan judul persis mengikuti desain sumber (Web Fire Pasopati.pdf).
 * Ganti nilai di berkas ini untuk mengubah isi halaman; tidak perlu menyentuh HTML.
 */

/* Beri `vertikal: true` pada berita yang fotonya potret: kartunya tetap kaca
   putih seperti kartu lain, tetapi fotonya ber-rasio 3:4 (di panggung mengisi
   sisa ruang di bawah teks). Tanpa tanda itu fotonya lanskap 3:2. */
window.BERITA = [
  {
    pulau: "Jawa",
    tanggal: "11 Agustus 2026",
    judul: "Karhutla di Sukabumi Diduga karena Tangan Jahil, 18 Hektare Lahan Perhutani Terbakar",
    gambar: "assets/img/berita-jawa.jpg",
    alt: "Petugas BPBD dan kepolisian mengamati lahan yang terbakar di Sukabumi",
  },
  {
    pulau: "Jawa",
    tanggal: "9 Agustus 2026",
    judul: "Lahan gambut di Jawa Barat kering ekstrem, BMKG imbau waspada titik panas",
    gambar: "assets/img/berita-jawa.jpg",
    alt: "Lahan gambut mengering di Jawa Barat",
    vertikal: true,
  },
  {
    pulau: "Sumatra",
    tanggal: "11 Agustus 2026",
    judul: "Kebakaran hutan Indonesia meluas, asap menyebar ke negara terangga",
    gambar: "assets/img/berita-sumatra.jpg",
    alt: "Regu pemadam menahan laju api di padang ilalang",
    vertikal: true,
  },
  {
    pulau: "Sumatra",
    tanggal: "10 Agustus 2026",
    judul: "Riau tegaskan status darurat karhutla, mobil pemadam dikerahkan ke Bengkalis",
    gambar: "assets/img/berita-sumatra.jpg",
    alt: "Mobil pemadam menuju lahan terbakar di Bengkalis",
  },
  {
    pulau: "Sumatra",
    tanggal: "8 Agustus 2026",
    judul: "Asap kembali selubungi Pekanbaru, kualitas udara masuk kategori tidak sehat",
    gambar: "assets/img/berita-sumatra.jpg",
    alt: "Kabut asap menyelimuti kota Pekanbaru",
  },
  {
    pulau: "Kalimantan",
    tanggal: "11 Agustus 2026",
    judul: "Ketika Kebakaran Hutan dan Lahan Menggila di Kalimantan",
    gambar: "assets/img/berita-kalimantan.jpg",
    alt: "Rumah panggung terbakar dengan kepulan asap hitam di Kalimantan",
  },
  {
    pulau: "Kalimantan",
    tanggal: "7 Agustus 2026",
    judul: "Warga Pontianak kesulitan napas saat kabut asap melanda perbatasan",
    gambar: "assets/img/berita-kalimantan.jpg",
    alt: "Warga mengenakan masker di tengah kabut asap Pontianak",
  },
];

/**
 * Kartu statistik. Pada desain sumber badan kartu masih kosong (placeholder),
 * jadi `nilai` dan `keterangan` sengaja dibiarkan kosong — isi saja bila sudah ada
 * angkanya, keduanya otomatis tampil.
 */
window.STATISTIK = [
  { tanggal: "11 Agustus 2026", label: "Statistik 1", nilai: "", keterangan: "" },
  { tanggal: "11 Agustus 2026", label: "Statistik 2", nilai: "", keterangan: "" },
  { tanggal: "11 Agustus 2026", label: "Statistik 3", nilai: "", keterangan: "" },
  { tanggal: "11 Agustus 2026", label: "Statistik 4", nilai: "", keterangan: "" },
  { tanggal: "11 Agustus 2026", label: "Statistik 5", nilai: "", keterangan: "" },
];

/*
 * window.TITIK_PANAS dan window.WILAYAH_RAWAN dulu ada di sini: angka titik
 * panas contoh per provinsi beserta status "Siaga darurat"/"Waspada"-nya.
 * Keduanya dilepas begitu peta beralih ke data nyata — jumlah laporan per
 * provinsi dihitung FireController dari lokasi tiap kejadian di CMS dan
 * dikirim ke komponen peta sebagai argumen. Dibiarkan di sini, keduanya jadi
 * sumber kedua yang diam-diam bersaing dengan yang dari server.
 */

