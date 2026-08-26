@php
    // Penanda versi untuk berkas di public/: nama berkasnya tetap, jadi tanpa
    // ini peramban tidak punya alasan mengambil ulang setelah kita mengubahnya.
    //
    // Bukan kekhawatiran teoretis — ini sudah pernah terjadi. Server tidak
    // mengirim Cache-Control untuk berkas statis, hanya ETag dan Last-Modified,
    // dan tanpa Cache-Control peramban memakai aturan kira-kira sendiri: berkas
    // dianggap masih segar selama sekian lama TANPA bertanya ke server. HTML-nya
    // sementara itu `no-cache, private`, jadi selalu baru. Hasilnya markup baru
    // berpasangan dengan JS lama — persis yang memunculkan
    // "durasiVideo is not defined" di production.
    //
    // filemtime, bukan nomor versi yang ditulis tangan: yang ditulis tangan
    // pasti suatu saat lupa dinaikkan, dan diamnya kegagalan itu sama persis.
    // Berkas yang tidak ada dilewatkan apa adanya supaya halaman tidak mati
    // hanya karena satu aset salah ketik.
    //
    // @vite di bawah tidak ikut: build Vite sudah menyisipkan hash ke nama
    // berkasnya sendiri.
    $aset = function (string $jalur) {
        $penuh = public_path($jalur);

        return asset($jalur) . (is_file($penuh) ? '?v=' . filemtime($penuh) : '');
    };
@endphp

<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    {{-- Dipakai kolom komentar pop-up rincian saat mengirim lewat fetch. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Fire Pasopati — Pantauan Karhutla Indonesia')</title>
    <meta
      name="description"
      content="@yield('description', 'Pantauan kebakaran hutan dan lahan di Indonesia — berita terkini, statistik harian, dan peta sebaran wilayah rawan.')"
    />
    @stack('meta')
    <link rel="stylesheet" href="{{ $aset('assets/vendor/leaflet/leaflet.css') }}" />
    {{-- Pemilih rentang tanggal pada dialog peta. Di-vendor ke public/ seperti
         leaflet dan alpine, bukan lewat bundel Vite: resources/js/app.js juga
         menarik gsap, preloader, dan infinite-scroll yang tidak dipakai di
         halaman ini. --}}
    <link rel="stylesheet" href="{{ $aset('assets/vendor/flatpickr/flatpickr.min.css') }}" />

    {{-- Navbar halaman ini (pasopati/nav.blade.php) disalin dari navbar situs
         utama, jadi kelas utility-nya berasal dari build Vite — bukan dari
         dist/style.css yang isinya hanya kelas yang terpakai di halaman Fire.

         WAJIB dimuat SEBELUM dist/style.css. Keduanya build Tailwind dan
         kelasnya jatuh di lapisan yang sama, jadi yang belakangan menang. Bila
         build ini ditaruh belakangan, kelas polosnya (.relative, .grid,
         .w-full, .max-w-[940px]) mengalahkan varian `panggung:` milik dist —
         kanvas 1920x1080 kehilangan penempatannya sementara --kartu-lebar tetap
         526px, dan korselnya melebar keluar layar.

         Konsekuensinya: di halaman ini dist yang menang untuk kelas yang sama.
         Yang jadi korban hanya varian responsif navbar (mis. `md:flex` kalah
         dari `.hidden` milik dist) — itu dipulihkan di css/nav-pasopati.css
         yang dimuat paling akhir tanpa lapisan, sehingga menang atas keduanya. --}}
    @vite(['resources/css/app.css'])

    <link rel="stylesheet" href="{{ $aset('dist/style.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/nav-pasopati.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/pantauan-kosong.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/rincian-laporan.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/tepi-lunak.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/kartu-kursor.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/kartu-video.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/peta-angka.css') }}" />
    <link rel="stylesheet" href="{{ $aset('css/peta-popup.css') }}" />
    <link rel="stylesheet" href="{{ $aset('assets/vendor/lenis/lenis.css') }}" />
  </head>
  <body>
    <h1 class="sr-only">Pantauan kebakaran hutan dan lahan Indonesia</h1>

    @include('pasopati.nav')

    @yield('konten')

    <script src="{{ $aset('assets/vendor/leaflet/leaflet.js') }}"></script>
    <script src="{{ $aset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ $aset('data/konten.js') }}"></script>
    <script src="{{ $aset('data/peta-provinsi.js') }}"></script>
    <script src="{{ $aset('js/panggung.js') }}"></script>
    <script src="{{ $aset('js/beranda.js') }}"></script>
    <script defer src="{{ $aset('assets/vendor/alpine/alpine.min.js') }}"></script>
    <script src="{{ $aset('js/peta.js') }}"></script>
    <script src="{{ $aset('js/nav.js') }}"></script>
    {{-- GSAP ScrollTrigger menggerakkan paralaks & tepi lunak: satu ticker
         untuk semua pemicu, fase baca dan tulis dipisah. Guliran halamannya
         tetap bawaan peramban (compositor thread), tidak diambil alih.
         parallax.js di bawah adalah cadangan bila CDN ini gagal dimuat. --}}
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
    {{-- Lenis dibuat SEBELUM pemicu ScrollTrigger dibuat — urutan yang dipakai
         rujukan. Lenis mengubah tinggi html (html.lenis body{height:auto}), dan
         ScrollTrigger mengukur posisi pemicu saat dibuat; kalau diukur lebih
         dulu, ukurannya diambil dari tata letak sebelum Lenis menyesuaikannya.
         Di-vendor (bukan CDN) karena dist Lenis bukan UMD dan `class L`-nya
         menutupi window.L milik Leaflet. --}}
    <script src="{{ $aset('assets/vendor/lenis/lenis.min.js') }}"></script>
    <script src="{{ $aset('js/gulir-lenis.js') }}"></script>

    <script src="{{ $aset('js/parallax-gsap.js') }}"></script>
    <script src="{{ $aset('js/parallax.js') }}"></script>

    {{-- Turnstile untuk kolom komentar pop-up rincian. Dijaga config yang sama
         dengan layouts/app.blade.php: tanpa site key, widget tidak dirender dan
         verifikasinya juga dilewati di sisi server. --}}
    @if (! empty(config('services.turnstile.site_key')))
      <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    @endif

  </body>
</html>
