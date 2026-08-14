/**
 * Guliran halus (Lenis) + jembatan ke ScrollTrigger.
 *
 * Pola dan angkanya diambil dari proyek rujukan (lakunafoto/main.js), yang
 * rasa gulirnya memang jadi acuan:
 *
 *     lenis = new Lenis({ duration: 1.15, smoothWheel: true });
 *     lenis.on("scroll", ScrollTrigger.update);
 *     gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
 *     gsap.ticker.lagSmoothing(0);
 *
 * Tiga bagian itu bekerja sebagai satu kesatuan:
 * - Lenis yang benar-benar melunakkan gulirannya. ScrollTrigger sendiri hanya
 *   menggerakkan animasi; tanpa Lenis, gulirannya tetap guliran biasa.
 * - Lenis dijalankan DARI ticker GSAP, bukan rAF sendiri, supaya seluruh
 *   halaman berdenyut pada satu jam yang sama — dua rAF yang berebut membuat
 *   animasi tampak sedikit meleset dari gulirannya.
 * - lagSmoothing(0) mematikan koreksi lag GSAP yang, saat satu frame telat,
 *   melompatkan animasi untuk "mengejar".
 *
 * Lenis menggulirkan wadah aslinya (mengubah scrollTop), bukan menggeser
 * wrapper dengan transform — penting di sini karena hero section 1 memakai
 * `position: sticky`, yang akan mati di dalam elemen ber-transform.
 */
(function () {
  if (!window.Lenis || !window.gsap) return;

  /* Hormati permintaan sistem; guliran kembali ke bawaan peramban. */
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  var lenis = new window.Lenis({
    /* Rujukan memakai 1.15. Di sini sedikit lebih pendek: halaman ini hanya
       sekitar dua layar dan layar pertamanya di-pin, jadi luncuran sepanjang
       itu terasa menggantung — jarak gulirnya keburu habis sebelum luncurannya
       selesai. Angka ini hasil mencoba langsung, bukan salinan rujukan:
       1.15 (nilai rujukan) dan 1 terasa berat, 0.9 lebih ringan, 0.5 terlalu
       dekat ke guliran biasa. Jangan dinaikkan tanpa mengujinya lagi. */
    duration: 0.7,
    smoothWheel: true,
  });

  if (window.ScrollTrigger) {
    lenis.on("scroll", window.ScrollTrigger.update);
  }

  window.gsap.ticker.add(function (waktu) {
    lenis.raf(waktu * 1000); /* ticker GSAP dalam detik, Lenis dalam milidetik */
  });

  window.gsap.ticker.lagSmoothing(0);

  /* Tautan #anchor ikut lewat Lenis — kalau tidak, lompatannya memakai guliran
     bawaan dan terasa berbeda dari guliran roda. */
  document.querySelectorAll('a[href^="#"]').forEach(function (tautan) {
    tautan.addEventListener("click", function (e) {
      var id = tautan.getAttribute("href");
      if (!id || id.length < 2) return;

      var tujuan = document.querySelector(id);
      if (!tujuan) return;

      e.preventDefault();
      lenis.scrollTo(tujuan, { offset: -70, duration: 1.4 });
    });
  });

  /* Panel yang menutupi layar mengunci halaman lewat body overflow hidden
     (pop-up rincian, dialog peta, laci navigasi). Lenis dihentikan selama itu
     supaya roda di dalam panel tidak ikut menggerakkan halaman di belakangnya.
     Rujukan tidak butuh ini karena halamannya tidak punya panel semacam itu. */
  new MutationObserver(function () {
    if (document.body.style.overflow === "hidden") lenis.stop();
    else lenis.start();
  }).observe(document.body, { attributes: true, attributeFilter: ["style"] });

  /* Untuk menyetel dari konsol saat mencari rasa yang pas. */
  window.lenis = lenis;
})();
