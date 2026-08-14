/**
 * Parallax foto latar tiap layar ([data-foto-latar]).
 *
 * Ini port dari pola ScrollMagic + GSAP yang diminta:
 *
 *     controller: { triggerHook: "onEnter", duration: "200%" }
 *     Scene({triggerElement: "#parallaxN"})
 *       .setTween("#parallaxN video", { y: "80%", ease: Linear.easeNone })
 *
 * Artinya: scene mulai saat tepi atas layar menyentuh tepi bawah viewport
 * ("onEnter"), berlangsung sepanjang 200% tinggi viewport, dan selama itu media
 * di dalamnya digeser LINIER sampai 80% — bukan dipatok pada satu ambang.
 *
 * Ditulis tanpa ScrollMagic/GSAP karena pemetaannya hanya beberapa baris: kedua
 * pustaka itu sudah tak dirawat (ScrollMagic terakhir 2019) dan menambah ~100KB
 * hanya untuk satu interpolasi linier. Perilakunya sama.
 *
 * Geometri media disamakan dengan hero acuan: 200% tinggi layar pada top:-130%.
 *
 * Soal amplitudo: `y: "80%"` pada GSAP itu 80% dari tinggi MEDIA. Di halaman
 * acuan kontainernya tepat `h-screen`, jadi tinggi media = 2x viewport dan 80%
 * darinya = 160% viewport — persis 80% dari panjang scene (200% viewport).
 * Hasilnya media turun 0,8 px per 1 px guliran, sehingga RELATIF VIEWPORT ia
 * hanya bergerak ~20% kecepatan gulir alias hampir diam. Itulah rasa paralaks
 * yang dimaksud.
 *
 * Di sini amplitudo dinyatakan terhadap PANJANG SCENE, bukan tinggi media.
 * Alasannya: di mode aliran sebuah layar bisa lebih tinggi daripada viewport,
 * dan porting literal "80% tinggi media" di situ membuat media bergerak ~60%
 * kecepatan gulir — terasa ikut tergulir, bukan paralaks. Dengan patokan panjang
 * scene, kecepatan tampaknya tetap ~20% di kedua mode; di mode panggung (layar
 * tepat 1 viewport) angkanya identik dengan halaman acuan.
 *
 * - Hanya transform (GPU), lewat requestAnimationFrame + scroll pasif.
 * - Dimatikan total bila sistem meminta `prefers-reduced-motion`; foto tetap
 *   menutupi layar karena aturan CSS, jadi tidak ada fallback jelek.
 * - Berlaku di kedua mode: aliran (gulir biasa) dan panggung (scroll-snap).
 *   Di mode panggung, posisi diam tiap layar jatuh di tengah scene, jadi
 *   pergeserannya terlihat selama animasi snap dan tidak pernah menyingkap tepi.
 */
(function () {
  /* Versi GSAP (js/parallax-gsap.js) mengambil alih bila ScrollTrigger termuat.
     Berkas ini tetap ada sebagai cadangan: kalau CDN GSAP gagal dimuat,
     paralaks dan tepi lunak tetap jalan seperti biasa. */
  if (window.gsap && window.ScrollTrigger) return;

  /* Kaitan JS berupa atribut: kelas dipakai untuk gaya (utility Tailwind).
     `.layar__foto` masih diterima selama ada halaman lama yang memakainya. */
  var foto = document.querySelectorAll("[data-foto-latar], .layar__foto");
  /* Gambar hero yang di-pin (sticky): parallax berbasis scrollY, bukan rect
     section (yang diam karena di-pin). Lihat catatan di bawah. */
  var pahlawan = document.querySelectorAll("[data-pahlawan-parallax]");

  /* Section yang menggulir menutupi hero ([data-kabur-tepi]). Tepi atasnya
     dilembutkan selama transisi menutup: masker pemudar (.tepi-lunak) +
     jalur buram (.kabur-tepi) membaca --kabur-tepi pada section — 1 saat
     tepi atas section baru menyentuh tepi bawah viewport, 0 saat tiba di
     atas, jadi batas kerasnya hilang tepat selama "konten belum di atas". */
  var tepi = document.querySelectorAll("[data-kabur-tepi]");

  if (!foto.length && !pahlawan.length && !tepi.length) return;

  /* prefers-reduced-motion mematikan kedua jenis geseran; gambar tetap
     menutupi layar berkat posisi CSS (oversize), jadi tidak ada fallback jelek. */
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  var DURASI = 2; /* duration: "200%" — panjang scene dalam kelipatan viewport */
  var JELAJAH = 0.8; /* y: "80%" — fraksi panjang scene (lihat catatan di atas) */
  /* Geseran maksimum gambar hero yang di-pin, sebagai fraksi tinggi viewport.
     Selama satu viewport gulir (saat section 2 menutupi hero), gambar turun
     sebesar AMP × viewport — lebih lambat dari section 2 yang naik 1:1, itulah
     rasa paralaksnya. */
  var AMP = 0.35;

  var pas = null; /* permintaan animasi yang tertunda */

  /* Nilai terakhir yang benar-benar ditulis ke DOM, supaya frame yang tidak
     mengubah apa pun tidak menyentuh style sama sekali. */
  var terakhirFoto = [];
  var terakhirPahlawan = [];
  var terakhirTepi = [];

  /* --kabur-tepi menggerakkan masker gradien DAN backdrop-filter blur yang
     radiusnya ikut berubah — keduanya memaksa cat ulang seluruh section di
     bawahnya setiap kali nilainya berganti. Dibulatkan agar frame dengan
     pergeseran sangat kecil tidak memicu hitung ulang itu.

     0,005 dipilih setelah menimbang bentuk efeknya: tingginya
     sisa*(1-sisa)*200svh, jadi langkah terbesar muncul di kedua ujung dan di
     sana besarnya hanya 1svh — pada jalur yang memang tembus pandang dan
     buram. Langkah yang lebih kasar (0,02) memang lebih hemat, tetapi
     lompatannya sampai 4svh dan mulai terlihat sebagai patahan. */
  var LANGKAH_TEPI = 0.005;

  function perbarui() {
    pas = null;

    /* --- fase baca ---
       Semua pengukuran dikerjakan lebih dulu. Sebelumnya rect dibaca dan
       transform ditulis berselang-seling di dalam satu perulangan; karena
       transform ikut mengubah hasil getBoundingClientRect(), tiap bacaan
       berikutnya memaksa layout sinkron — persis pola yang membuat guliran
       tersendat. */
    var tinggiViewport = window.innerHeight;
    var gulir = window.scrollY || window.pageYOffset || 0;

    var rectFoto = [];
    for (var i = 0; i < foto.length; i++) {
      var layar = foto[i].parentElement;
      rectFoto.push(layar ? layar.getBoundingClientRect() : null);
    }

    var rectTepi = [];
    for (var k = 0; k < tepi.length; k++) {
      rectTepi.push(tepi[k].getBoundingClientRect());
    }

    /* --- fase tulis --- */
    for (i = 0; i < foto.length; i++) {
      var rect = rectFoto[i];
      if (!rect) continue;
      /* Lewati layar yang sama sekali di luar viewport. */
      if (rect.bottom < 0 || rect.top > tinggiViewport) continue;

      /* Kemajuan scene, 0..1:
           0   = tepi atas layar tepat di tepi bawah viewport (triggerHook onEnter)
           0,5 = layar tepat memenuhi viewport (posisi diam di mode panggung)
           1   = sudah tergulir sejauh 200% tinggi viewport sejak scene mulai   */
      var maju = (tinggiViewport - rect.top) / (DURASI * tinggiViewport);
      if (maju < 0) maju = 0;
      else if (maju > 1) maju = 1;

      /* Linear.easeNone: dari 0 ke 80% panjang scene, tanpa pelunakan. Nilai awal
         nol dipertahankan (bukan ditengahkan) supaya posisi diamnya sama seperti
         hero acuan: top -130% + 80% viewport = -50% viewport. */
      var geser = (maju * JELAJAH * DURASI * tinggiViewport).toFixed(2);
      if (terakhirFoto[i] === geser) continue;
      terakhirFoto[i] = geser;
      foto[i].style.transform = "translate3d(0," + geser + "px,0)";
    }

    /* Hero di-pin (sticky top:0) hanya di mode panggung; di aliran ia statis
       supaya kartu statistik bisa satu blok dengan korsel. Progres tetap
       diambil dari scrollY (bukan rect — yang diam selama di-pin): 0..1
       selama satu viewport gulir pertama, lalu dibatasi pada AMP. Di panggung
       itu masa section 2 naik menutupi hero; di aliran itu masa hero tergulir
       pergi — keduanya memberi geseran paralaks yang sama. */
    for (var j = 0; j < pahlawan.length; j++) {
      var prog = gulir / tinggiViewport;
      if (prog < 0) prog = 0;
      else if (prog > 1) prog = 1;
      var geserP = (prog * AMP * tinggiViewport).toFixed(2);
      if (terakhirPahlawan[j] === geserP) continue;
      terakhirPahlawan[j] = geserP;
      pahlawan[j].style.transform = "translate3d(0," + geserP + "px,0)";
    }

    /* Tepi lunak: sisa jarak tepi atas section ke atas viewport, 0..1.
       Disetel pada section (bukan jalurnya) supaya masker & jalur buram
       membaca nilai yang sama lewat pewarisan custom property. */
    for (k = 0; k < tepi.length; k++) {
      var rt = rectTepi[k];
      if (rt.bottom < 0 || rt.top > tinggiViewport) continue;
      var sisa = rt.top / tinggiViewport;
      if (sisa < 0) sisa = 0;
      else if (sisa > 1) sisa = 1;

      var dibulatkan = (Math.round(sisa / LANGKAH_TEPI) * LANGKAH_TEPI).toFixed(3);
      if (terakhirTepi[k] === dibulatkan) continue;
      terakhirTepi[k] = dibulatkan;
      tepi[k].style.setProperty("--kabur-tepi", dibulatkan);
    }
  }

  function jadwalkan() {
    if (pas != null) return;
    pas = requestAnimationFrame(perbarui);
  }

  /* Kaitan untuk memaksa hitung ulang dari luar — dipakai saat tata letak
     berubah tanpa gulir (mis. panel dibuka) dan oleh pengujian. */
  window.paralaksSegarkan = perbarui;

  /* Sekali saat pasang, lalu pada tiap gulir & ubah ukuran. */
  perbarui();
  window.addEventListener("scroll", jadwalkan, { passive: true });
  window.addEventListener("resize", jadwalkan, { passive: true });
  window.addEventListener("load", perbarui);
})();
