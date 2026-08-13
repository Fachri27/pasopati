/**
 * Mode tata letak + penskalaan kanvas.
 *
 * Ambang pergantian mode ditentukan sepenuhnya oleh CSS lewat custom property
 * --mode (lihat src/input.css). Modul JS lain membacanya dari sini, jadi tidak
 * ada dua ambang terpisah yang bisa berselisih.
 *
 * Mode "panggung": kanvas 1920x1080 diskalakan agar pas di viewport, sehingga
 * seluruh ukuran di CSS bisa ditulis dalam piksel desain.
 * Mode "aliran": halaman mengalir normal, kanvas tidak diskalakan.
 */
(function () {
  var LEBAR = 1920;
  var TINGGI = 1080;
  /* Kaitan JS berupa atribut, bukan kelas: kelas dipakai untuk gaya (utility
     Tailwind) dan tidak boleh dipakai skrip. `.panggung` masih diterima selama
     ada section yang belum dikonversi. */
  var panggungSemua = document.querySelectorAll("[data-kanvas], .panggung");

  function modeTataLetak() {
    return (
      window
        .getComputedStyle(document.documentElement)
        .getPropertyValue("--mode")
        .trim() || "aliran"
    );
  }

  window.modeTataLetak = modeTataLetak;

  if (!panggungSemua.length) return;

  function skalakan() {
    var skala =
      modeTataLetak() === "panggung"
        ? Math.min(window.innerWidth / LEBAR, window.innerHeight / TINGGI)
        : 1;
    for (var i = 0; i < panggungSemua.length; i++) {
      panggungSemua[i].style.setProperty("--skala", skala);
    }
  }

  skalakan();
  window.addEventListener("resize", skalakan);
})();
