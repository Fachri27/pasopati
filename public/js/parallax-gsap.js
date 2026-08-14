/**
 * Parallax + tepi lunak lewat GSAP ScrollTrigger.
 *
 * Mengambil alih js/parallax.js bila GSAP + ScrollTrigger termuat; berkas itu
 * berhenti sendiri saat mendeteksi keduanya, jadi tidak pernah ada dua yang
 * menggerakkan nilai yang sama. Kalau CDN-nya gagal, versi rAF di sana tetap
 * jalan dan halaman tidak berubah.
 *
 * Yang didapat dari ScrollTrigger:
 * - Satu ticker untuk semua pemicu, dengan fase baca dan tulis yang dipisah —
 *   tidak ada layout paksa berselang-seling seperti kalau tiap efek membaca
 *   rect lalu langsung menulis gaya.
 * - Perhitungan posisi hanya dilakukan ulang saat refresh (ubah ukuran), bukan
 *   tiap frame.
 *
 * Guliran halamannya sendiri dilunakkan Lenis (js/gulir-lenis.js), yang juga
 * memanggil ScrollTrigger.update() tiap guliran dan berbagi ticker GSAP yang
 * sama — pola dari proyek rujukan. ScrollTrigger sendiri tidak melunakkan
 * guliran; ia hanya menggerakkan animasi berbasis posisi gulir.
 *
 * Angka-angkanya sama persis dengan js/parallax.js supaya tampilannya tidak
 * berubah — asal-usulnya dijelaskan panjang di berkas itu.
 */
(function () {
  if (!window.gsap || !window.ScrollTrigger) return;

  var foto = document.querySelectorAll("[data-foto-latar], .layar__foto");
  var pahlawan = document.querySelectorAll("[data-pahlawan-parallax]");
  var tepi = document.querySelectorAll("[data-kabur-tepi]");

  if (!foto.length && !pahlawan.length && !tepi.length) return;

  /* prefers-reduced-motion mematikan semuanya; foto tetap menutupi layar berkat
     posisi CSS-nya, jadi tidak ada fallback jelek. */
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  gsap.registerPlugin(ScrollTrigger);

  var DURASI = 2; /* panjang scene dalam kelipatan viewport */
  var JELAJAH = 0.8; /* fraksi panjang scene */
  var AMP = 0.35; /* geseran hero, fraksi tinggi viewport */

  /* Dua jenis scrub, sengaja dibedakan.
  
     Rujukan memakai scrub berangka (1 dan 1.2) untuk paralaksnya, dan justru
     kelambatan itulah yang memberi rasa mengalir — bukan cacat. Dipakai di sini
     untuk foto latar, yang memang dekoratif.
  
     Tepi lunak tetap `true`. Nilainya menyatakan jarak section 2 ke tepi atas
     viewport, jadi ia harus cocok dengan posisi section yang sebenarnya; kalau
     ditunda, pudarnya tidak lagi sejalan dengan yang terlihat. Rujukan tidak
     punya efek geometris semacam ini, jadi soal ini tidak muncul di sana. */
  var SCRUB_FOTO = 1;
  var SCRUB_TEPI = true;

  /* --- foto latar tiap layar --- */
  foto.forEach(function (f) {
    var layar = f.parentElement;
    if (!layar) return;

    gsap.fromTo(
      f,
      { y: 0 },
      {
        y: function () {
          return JELAJAH * DURASI * window.innerHeight;
        },
        ease: "none",
        /* translate3d, bukan translateY: foto ini seukuran layar penuh, dan
           tanpa lapisan GPU sendiri tiap pergeseran mengecat ulang area
           sebesar itu. */
        force3D: true,
        scrollTrigger: {
          trigger: layar,
          start: "top bottom", /* triggerHook onEnter */
          end: function () {
            return "+=" + DURASI * window.innerHeight; /* duration 200% */
          },
          scrub: SCRUB_FOTO,
          invalidateOnRefresh: true,
        },
      }
    );
  });

  /* --- hero yang di-pin ---
     Diikat ke posisi gulir halaman (0..satu viewport), bukan ke rect section
     yang diam selama sticky. */
  pahlawan.forEach(function (p) {
    gsap.fromTo(
      p,
      { y: 0 },
      {
        y: function () {
          return AMP * window.innerHeight;
        },
        ease: "none",
        force3D: true,
        scrollTrigger: {
          start: 0,
          end: function () {
            return window.innerHeight;
          },
          scrub: SCRUB_FOTO,
          invalidateOnRefresh: true,
        },
      }
    );
  });

  /* --- tepi lunak ---
     --kabur-tepi: 1 saat tepi atas section menyentuh tepi bawah viewport,
     0 saat tiba di atas. quickSetter menulis langsung tanpa menguraikan gaya
     berulang tiap frame. */
  tepi.forEach(function (bagian) {
    var setel = gsap.quickSetter(bagian, "--kabur-tepi");
    var nilai = { v: 1 };

    gsap.to(nilai, {
      v: 0,
      ease: "none",
      scrollTrigger: {
        trigger: bagian,
        start: "top bottom",
        end: "top top",
        scrub: SCRUB_TEPI,
        invalidateOnRefresh: true,
      },
      onUpdate: function () {
        setel(nilai.v.toFixed(3));
      },
    });
  });

  /* Kaitan lama tetap ada supaya pemanggil di luar (mis. saat panel dibuka)
     tidak perlu tahu implementasinya berganti. */
  window.paralaksSegarkan = function () {
    ScrollTrigger.refresh();
  };

  /* Hitung ulang setelah semua gambar selesai dimuat: posisi pemicu yang
     dihitung sebelum gambar punya tinggi akan meleset. Rujukan melakukan hal
     yang sama di window "load". */
  window.addEventListener("load", function () {
    ScrollTrigger.refresh();
  });
})();
