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

/**
 * Titik panas per provinsi — nilai yang diwarnai pada peta choropleth.
 * Nama provinsi harus sama dengan properti `nama` di data/peta-provinsi.js.
 * SEMUA ANGKA DI SINI CONTOH (mock), BUKAN DATA RESMI.
 *
 * Provinsi yang tidak tercantum dianggap belum ada datanya dan diwarnai netral.
 */
window.TITIK_PANAS = {
  Riau: 412,
  "Kalimantan Barat": 356,
  "Kalimantan Tengah": 289,
  "Sumatera Selatan": 231,
  Jambi: 168,
  "Kalimantan Selatan": 154,
  "Kalimantan Timur": 122,
  "Sumatera Utara": 96,
  Lampung: 74,
  "Nusa Tenggara Timur": 61,
  "Kalimantan Utara": 58,
  Aceh: 47,
  Papua: 44,
  "Sumatera Barat": 41,
  "Sulawesi Selatan": 38,
  Bengkulu: 33,
  "Sulawesi Tengah": 31,
  "Nusa Tenggara Barat": 29,
  "Jawa Timur": 27,
  "Kepulauan Riau": 26,
  "Jawa Barat": 23,
  "Kepulauan Bangka Belitung": 22,
  "Sulawesi Tenggara": 19,
  "Jawa Tengah": 18,
  "Papua Barat": 17,
  Maluku: 14,
  "Sulawesi Barat": 12,
  "Maluku Utara": 11,
  "Sulawesi Utara": 9,
  Banten: 8,
  Gorontalo: 7,
  Bali: 4,
  "DI Yogyakarta": 3,
  "DKI Jakarta": 0,
};

/**
 * Rincian tambahan untuk wilayah yang ditandai merah pada desain sumber.
 * Dipakai di daftar wilayah rawan dan sebagai isi tambahan pada tooltip peta.
 * `status` di sini menimpa status yang biasanya diturunkan dari jumlah titik panas.
 * Angka di bawah ini contoh (mock), bukan data resmi.
 */
window.WILAYAH_RAWAN = {
  Riau: {
    titikPanas: "412 titik",
    luasTerbakar: "6.180 ha",
    status: "Siaga darurat",
  },
  "Kalimantan Barat": {
    titikPanas: "356 titik",
    luasTerbakar: "4.940 ha",
    status: "Siaga darurat",
  },
  "Kalimantan Tengah": {
    titikPanas: "289 titik",
    luasTerbakar: "3.725 ha",
    status: "Waspada",
  },
};
