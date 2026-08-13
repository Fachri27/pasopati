/**
 * Navigasi & laci — replika navbar pasopati.id, tanpa Alpine.js.
 *
 * Mockup ini berbasis JS biasa (tanpa framework), jadi buka/tutup laci mobil
 * ditangani di sini, bukan dengan `x-data`/`x-show` seperti di pasopati.id.
 * Tautan menu pada laci merupakan placeholder (halaman tujuan tidak ada di
 * mockup ini); kecuali "Home" → #beranda dan "Peta Sebaran" → #peta.
 */
(function () {
  var nav = document.querySelector("[data-nav-pasopati]");
  if (!nav) return;

  var tombol = nav.querySelector("[data-tombol-laci]");
  var tutup = nav.querySelector("[data-tutup-laci]");
  var tabir = nav.querySelector("[data-tabir]");

  function buka() {
    nav.setAttribute("data-open", "true");
    if (tombol) tombol.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
    if (tutup) tutup.focus();
  }

  function tutupLaci() {
    nav.setAttribute("data-open", "false");
    if (tombol) tombol.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
    if (tombol) tombol.focus();
  }

  if (tombol) tombol.addEventListener("click", buka);
  if (tutup) tutup.addEventListener("click", tutupLaci);
  if (tabir) tabir.addEventListener("click", tutupLaci);

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && nav.getAttribute("data-open") === "true") {
      tutupLaci();
    }
  });

  // Tutup laci bila sebuah tautan di dalamnya diklik (mis. lompat ke #peta).
  nav.querySelectorAll(".laci a, .laci__forum").forEach(function (el) {
    el.addEventListener("click", function () {
      if (nav.getAttribute("data-open") === "true") tutupLaci();
    });
  });

  // Mockup tanpa backend: cegah formulir pencarian menavigasi/memuat ulang.
  nav.querySelectorAll("form").forEach(function (f) {
    f.addEventListener("submit", function (e) { e.preventDefault(); });
  });
})();