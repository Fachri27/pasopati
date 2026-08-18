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
    <link rel="stylesheet" href="{{ asset('assets/vendor/leaflet/leaflet.css') }}" />
    {{-- Pemilih rentang tanggal pada dialog peta. Di-vendor ke public/ seperti
         leaflet dan alpine, bukan lewat bundel Vite: resources/js/app.js juga
         menarik gsap, preloader, dan infinite-scroll yang tidak dipakai di
         halaman ini. --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}" />

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

    <link rel="stylesheet" href="{{ asset('dist/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/nav-pasopati.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/pantauan-kosong.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/rincian-laporan.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/tepi-lunak.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/kartu-kursor.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/kartu-video.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/peta-angka.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/peta-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/lenis/lenis.css') }}" />
  </head>
  <body>
    <h1 class="sr-only">Pantauan kebakaran hutan dan lahan Indonesia</h1>

    @include('pasopati.nav')

    @yield('konten')

    <script src="{{ asset('assets/vendor/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('data/konten.js') }}"></script>
    <script src="{{ asset('data/peta-provinsi.js') }}"></script>
    <script src="{{ asset('js/panggung.js') }}"></script>
    <script src="{{ asset('js/beranda.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/alpine/alpine.min.js') }}"></script>
    <script src="{{ asset('js/peta.js') }}"></script>
    <script src="{{ asset('js/nav.js') }}"></script>
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
    <script src="{{ asset('assets/vendor/lenis/lenis.min.js') }}"></script>
    <script src="{{ asset('js/gulir-lenis.js') }}"></script>

    <script src="{{ asset('js/parallax-gsap.js') }}"></script>
    <script src="{{ asset('js/parallax.js') }}"></script>

    {{-- Turnstile untuk kolom komentar pop-up rincian. Dijaga config yang sama
         dengan layouts/app.blade.php: tanpa site key, widget tidak dirender dan
         verifikasinya juga dilewati di sisi server. --}}
    @if (! empty(config('services.turnstile.site_key')))
      <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    @endif

  </body>
</html>
