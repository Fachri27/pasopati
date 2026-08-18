/**
 * Section 2 — peta sebaran, sebagai komponen Alpine.
 *
 * Markup panel, daftar wilayah rawan, kabar kegagalan, dan tabel setara ada di
 * index.html sebagai template Alpine — bukan dibangun dengan createElement di
 * sini. Itulah yang membuat section ini bisa dipindahkan apa adanya ke sebuah
 * Blade view; yang tinggal di JS hanya keadaan, Leaflet, dan geometri.
 *
 * Peta tidak memakai citra dasar: wadahnya tembus pandang sehingga foto latar
 * layar plus gradasi merah terlihat sebagai dasar. Di atasnya ditumpangkan
 * choropleth WMS Simontini (PROVINSI_STADI_2025), lalu poligon provinsi
 * (data/peta-provinsi.js) yang tembus pandang sebagai penangkap klik.
 *
 * Data: window.PETA_PROVINSI, window.TITIK_PANAS, window.WILAYAH_RAWAN,
 * window.BERITA — semuanya bisa diganti argumen dari Blade bila perlu.
 */
document.addEventListener("alpine:init", function () {
  Alpine.data("peta", function (beritaAwal) {
    /* Objek Leaflet TIDAK disimpan sebagai keadaan Alpine: x-data dibungkus
       Proxy reaktif, dan membungkus instance Leaflet di dalamnya membuat
       internalnya tak terduga. Semuanya tinggal di closure ini. */
    var peta = null;
    var provinsi = null;
    var lapisWilayah = null;
    var lapisAngka = null;
    var daftarAngka = []; /* { penanda, kotak } per provinsi yang berangka */
    /* Peta sudah punya center & zoom? Sebelum itu Leaflet menolak memproyeksikan
       koordinat ("Set map center and zoom first"), dan penempatan angka memang
       berjalan lebih dulu: bangunPeta() menyusun lapisannya, fitBounds baru
       dikerjakan setelMode() sesudahnya. */
    var petaSiap = false;
    /* Titik layar tempat pop-up wilayah "tumbuh" — koordinat viewport, bukan
       koordinat peta. Sengaja begitu: di mode panggung peta duduk di dalam
       kanvas yang diperkecil transform, dan clientX/clientY sudah menghitung
       transform itu sedangkan titik peta tidak. */
    var asalPopup = null;
    var garisHalo = null;
    var garisTerpilih = null;
    var simpananGeometri = {};
    var TINTA = "";
    var TANPA_DATA = "";
    var TANGGA = [];
    var nomorPermintaan = 0;
    var pewaktuSorot = null;
    var pengawasTile = null;
    var tileTermuat = false;
    var tileGagal = 0;
    var pengamatUkuran = null;
    var tundaUkur = null;
    var modeTerpasang = null;
    var kurangiGerak = false;

    var WMS_URL = "https://aws.simontini.id/geoserver/wms";
    var WFS_URL = "https://aws.simontini.id/geoserver/wfs";
    /* Choropleth per PROVINSI. Layer kabupaten (KABUPATEN_STADI_2025) masih ada
       di GeoServer yang sama; bila suatu saat dikembalikan ke sana, yang ikut
       berubah bukan cuma nama layer ini — atributnya berbeda: kabupaten memakai
       level_4 (nama) + luas, provinsi memakai level_3 (nama) + deforestas. */
    var WMS_LAYER = "proteus:PROVINSI_STADI_2025";
    var BIDANG_NAMA = "level_3"; /* nama provinsi pada layer */
    var BIDANG_PULAU = "level_2"; /* pulau, mis. "Kalimantan" */
    var BIDANG_LUAS = "deforestas"; /* luas deforestasi, hektare */
    var MAKS_HASIL = 8; /* hasil pencarian yang ditampilkan sekaligus */
    var JEDA_SOROT = 2400; /* ms; lama baris daftar menyala setelah diketuk */
    var TUNGGU_TILE = 8000; /* ms; tanpa satu pun tile termuat setelah ini = gagal */
    /* Sisi raster untuk GetFeatureInfo berjendela sendiri (lihat getFeatureInfo)
       dan patokan halus-kasarnya geometri yang dikembalikan. 256 sudah cukup
       halus untuk provinsi terkecil sekalipun, dan lebih hemat daripada 512:
       yang membuat jawaban besar bukan resolusinya melainkan jumlah pulau. */
    var PIKSEL_QUERY = 256;
    /* Jarak bebas antar angka, piksel. Dua angka yang kotaknya bersinggungan
       lebih dekat dari ini dianggap bertumpuk. */
    var ANGKA_SELA = 3;

    /* Kotak pembatas Indonesia — dipakai mengepaskan peta ke wadahnya. */
    var BATAS = [
      [-11.2, 94.7],
      [6.4, 141.3],
    ];

    /* Provinsi -> pulau, supaya artikel (di-key per pulau di data/konten.js)
       bisa diambil dari provinsi yang diklik. */
    var PROVINSI_KE_PULAU = {
      Aceh: "Sumatra", "Sumatera Utara": "Sumatra", "Sumatera Barat": "Sumatra",
      Riau: "Sumatra", "Kepulauan Riau": "Sumatra", Jambi: "Sumatra",
      "Sumatera Selatan": "Sumatra", Bengkulu: "Sumatra", Lampung: "Sumatra",
      "Kepulauan Bangka Belitung": "Sumatra",
      Banten: "Jawa", "DKI Jakarta": "Jawa", "Jawa Barat": "Jawa",
      "Jawa Tengah": "Jawa", "DI Yogyakarta": "Jawa", "Jawa Timur": "Jawa",
      "Kalimantan Barat": "Kalimantan", "Kalimantan Tengah": "Kalimantan",
      "Kalimantan Selatan": "Kalimantan", "Kalimantan Timur": "Kalimantan",
      "Kalimantan Utara": "Kalimantan",
      "Sulawesi Utara": "Sulawesi", Gorontalo: "Sulawesi",
      "Sulawesi Tengah": "Sulawesi", "Sulawesi Barat": "Sulawesi",
      "Sulawesi Selatan": "Sulawesi", "Sulawesi Tenggara": "Sulawesi",
      Bali: "Bali-Nusa", "Nusa Tenggara Barat": "Bali-Nusa",
      "Nusa Tenggara Timur": "Bali-Nusa",
      Maluku: "Maluku", "Maluku Utara": "Maluku",
      Papua: "Papua", "Papua Barat": "Papua",
    };

    /* Nama pulau pada layer (level_2) ditulis sedikit berbeda dari kunci pulau
       di data/konten.js — dua ini yang tidak sama persis. */
    var ALIAS_PULAU = {
      Sumatera: "Sumatra",
      "Bali & Nusa Tenggara": "Bali-Nusa",
    };

    /* Layer memakai 38 provinsi (termasuk pemecahan Papua 2022); data lokal
       masih 34 dan menyingkat satu nama. */
    var ALIAS_LOKAL = {
      "Daerah Istimewa Yogyakarta": "DI Yogyakarta",
    };

    /* Tab pulau pada popup berita. `kunci` dipakai sebagai keadaan tab, `isi`
       adalah nilai `pulau` di data/konten.js yang ikut tab itu — Bali & Nusa
       Tenggara dibaca bersama Jawa, sesuai pengelompokan desain. */
    var PULAU_TAB = [
      { kunci: "Sumatra", label: "Sumatera", isi: ["Sumatra"] },
      { kunci: "Jawa", label: "Jawa, Bali, & Nusa Tenggara", isi: ["Jawa", "Bali-Nusa"] },
      { kunci: "Kalimantan", label: "Kalimantan", isi: ["Kalimantan"] },
      { kunci: "Sulawesi", label: "Sulawesi", isi: ["Sulawesi"] },
      { kunci: "Maluku", label: "Maluku", isi: ["Maluku"] },
      { kunci: "Papua", label: "Papua", isi: ["Papua"] },
    ];

    var BULAN = [
      "januari", "februari", "maret", "april", "mei", "juni",
      "juli", "agustus", "september", "oktober", "november", "desember",
    ];

    /* --- fungsi murni: tidak menyentuh keadaan, jadi di luar komponen --- */

    function token(nama) {
      return window
        .getComputedStyle(document.documentElement)
        .getPropertyValue(nama)
        .trim();
    }

    function pulauDariProvinsi(nama) {
      if (!nama) return null;
      if (PROVINSI_KE_PULAU[nama]) return PROVINSI_KE_PULAU[nama];
      /* Cadangan untuk nama yang tak persis sama (mis. provinsi pemecahan
         Papua, yang di data lokal masih tergabung sebagai "Papua"). */
      var kunci = Object.keys(PROVINSI_KE_PULAU).find(function (k) {
        return nama.indexOf(k) === 0 || k.indexOf(nama) === 0;
      });
      return kunci ? PROVINSI_KE_PULAU[kunci] : null;
    }

    /* Pulau menurut layer (level_2). Lebih dipercaya daripada pemetaan lokal di
       atas: layer tahu provinsi hasil pemecahan Papua 2022 dan menulis
       "Daerah Istimewa Yogyakarta" lengkap — dua hal yang meleset di sana. */
    function pulauDariLayer(nilai) {
      if (!nilai) return null;
      return ALIAS_PULAU[nilai] || nilai;
    }

    function tabDariPulau(pulau) {
      if (!pulau) return null;
      var tab = PULAU_TAB.find(function (t) {
        return t.isi.indexOf(pulau) !== -1;
      });
      return tab ? tab.kunci : null;
    }

    /* "11 Agustus 2026" -> milidetik. Tanggal di data ditulis apa adanya
       seperti desain sumber, jadi penerjemahannya dikerjakan di sini. Yang tak
       terbaca kembali null dan tak pernah tersaring keluar: berita lebih baik
       ikut tampil daripada hilang diam-diam karena salah ketik tanggal. */
    function waktuTeks(teks) {
      if (!teks) return null;
      var bagian = String(teks).trim().toLowerCase().split(/\s+/);
      if (bagian.length < 3) return null;
      var hari = parseInt(bagian[0], 10);
      var bulan = BULAN.indexOf(bagian[1]);
      var tahun = parseInt(bagian[2], 10);
      if (!hari || bulan < 0 || !tahun) return null;
      return Date.UTC(tahun, bulan, hari);
    }

    /* "2026-08-11" (nilai <input type="date">) -> milidetik. */
    function waktuIso(iso) {
      if (!iso) return null;
      var bagian = String(iso).split("-");
      if (bagian.length !== 3) return null;
      var waktu = Date.UTC(+bagian[0], +bagian[1] - 1, +bagian[2]);
      return isNaN(waktu) ? null : waktu;
    }

    function mode() {
      return window.modeTataLetak ? window.modeTataLetak() : "aliran";
    }

    function poligonFitur(geom) {
      return geom.type === "Polygon" ? [geom.coordinates] : geom.coordinates;
    }

    /* Siluet wilayah: hanya "d" sebuah path, dinormalkan ke dalam kotak 100x100
       yang sudah ditulis tetap di markup (viewBox="0 0 100 100").

       Kenapa dinormalkan dan bukan mengirim viewBox-nya sendiri: nama atribut
       di HTML diturunkan jadi huruf kecil, jadi :viewBox berakhir sebagai
       "viewbox" dan diabaikan SVG. Bentuknya diambil dari geometri yang sama
       dengan outline di peta. */
    var KOTAK_SILUET = 100;

    function siluet(geometri) {
      var poligon = poligonFitur(geometri);
      var minX = Infinity, maksX = -Infinity, minY = Infinity, maksY = -Infinity;

      poligon.forEach(function (cincinSemua) {
        cincinSemua.forEach(function (cincin) {
          cincin.forEach(function (t) {
            if (t[0] < minX) minX = t[0];
            if (t[0] > maksX) maksX = t[0];
            if (t[1] < minY) minY = t[1];
            if (t[1] > maksY) maksY = t[1];
          });
        });
      });

      /* Bujur dipendekkan menurut lintang tengah supaya bentuknya tidak melar. */
      var rapat = Math.cos((((minY + maksY) / 2) * Math.PI) / 180);
      var lebar = (maksX - minX) * rapat;
      var tinggi = maksY - minY;

      /* Skala agar sisi terpanjang mengisi 92% kotak, lalu ditengahkan —
         sisanya jadi pias supaya garis tepi tidak terpotong. */
      var skala = (KOTAK_SILUET * 0.92) / Math.max(lebar, tinggi);
      var geserX = (KOTAK_SILUET - lebar * skala) / 2;
      var geserY = (KOTAK_SILUET - tinggi * skala) / 2;

      var d = poligon
        .map(function (cincinSemua) {
          return cincinSemua
            .map(function (cincin) {
              return (
                cincin
                  .map(function (t, i) {
                    var x = geserX + (t[0] - minX) * rapat * skala;
                    var y = geserY + (maksY - t[1]) * skala;
                    return (i ? "L" : "M") + x.toFixed(2) + " " + y.toFixed(2);
                  })
                  .join("") + "Z"
              );
            })
            .join("");
        })
        .join("");

      return { d: d };
    }

    /* Kotak pembatas sebuah provinsi dari data lokal — dipakai sebagai wilayah
       pencarian saat melokasikan provinsi lewat gambar (lihat lokasiWilayah). */
    function bbeksProvinsi(nama) {
      var fitur = (window.PETA_PROVINSI.features || []).filter(function (f) {
        return f.properties.nama === nama;
      })[0];
      if (!fitur) return null;
      var minX = Infinity, maksX = -Infinity, minY = Infinity, maksY = -Infinity;
      poligonFitur(fitur.geometry).forEach(function (cincinSemua) {
        cincinSemua.forEach(function (cincin) {
          cincin.forEach(function (t) {
            if (t[0] < minX) minX = t[0];
            if (t[0] > maksX) maksX = t[0];
            if (t[1] < minY) minY = t[1];
            if (t[1] > maksY) maksY = t[1];
          });
        });
      });
      return [minX, minY, maksX, maksY];
    }

    /* Nama provinsi di layer kadang beda ejaan dengan data lokal (mis. provinsi
       hasil pemecahan Papua 2022 belum ada di data 34 provinsi; yang terdekat
       di sana adalah induk lamanya). Padanannya cuma pendekatan — pemakainya,
       lokasiWilayah, sudah menyiapkan jalan lain kalau ternyata meleset. */
    function provinsiLokal(nama) {
      if (!nama) return null;
      nama = ALIAS_LOKAL[nama] || nama;
      var semua = (window.PETA_PROVINSI.features || []).map(function (f) {
        return f.properties.nama;
      });
      if (semua.indexOf(nama) > -1) return nama;
      var cocok = semua.filter(function (n) {
        return nama.indexOf(n) === 0 || n.indexOf(nama) === 0;
      });
      /* Yang TERPANJANG, bukan yang pertama ketemu: "Papua Barat Daya" cocok
         dengan "Papua" maupun "Papua Barat", dan hanya yang kedua yang kotaknya
         benar-benar memuat wilayah itu — Papua lokal cuma separuh timur. */
      cocok.sort(function (a, b) {
        return b.length - a.length;
      });
      return cocok[0] || null;
    }

    /* Tempat angka jumlah kebakaran diletakkan pada sebuah provinsi.

       Pusat kotak pembatas tidak dipakai: pada provinsi yang melengkung atau
       terpecah banyak pulau (Maluku, Kepulauan Riau, Sulawesi) titik itu jatuh
       di laut, jauh dari daratan mana pun. Yang dicari di sini adalah titik
       yang benar-benar berada DI DALAM daratan terbesarnya.

       Dikembalikan sekalian kotak pembatas cincin terbesar itu — bukan kotak
       seluruh provinsi — karena cincin itulah yang harus cukup lapang untuk
       memuat angkanya. Kepulauan Riau kotak provinsinya raksasa sementara tiap
       pulaunya sebesar titik; memakai kotak provinsi, angkanya akan tetap
       digambar padahal tak ada tempat untuk berdiri. */
    function tempatAngka(geometri) {
      var poligon = poligonFitur(geometri);
      var terbesar = null;
      var luasTerbesar = -1;

      for (var p = 0; p < poligon.length; p++) {
        var cincin = poligon[p][0]; /* cincin luar; hole diabaikan untuk ukuran */
        if (!cincin || cincin.length < 3) continue;
        var luas = Math.abs(luasCincin(cincin));
        if (luas > luasTerbesar) {
          luasTerbesar = luas;
          terbesar = cincin;
        }
      }

      if (!terbesar) return null;

      var kotak = kotakCincin(terbesar);
      var pusat = pusatMassaCincin(terbesar);

      /* Pusat massa sudah cukup untuk bentuk yang cembung. */
      if (pusat && titikDalamCincin(pusat[0], pusat[1], terbesar)) {
        return { titik: pusat, kotak: kotak };
      }

      /* Bentuk cekung (Sulawesi yang seperti huruf K): pusat massanya di luar
         daratan. Sapu kisi di dalam kotaknya, ambil titik dalam yang paling
         dekat ke pusat massa — masih terbaca sebagai "tengah" tanpa perlu
         hitungan pole-of-inaccessibility yang jauh lebih mahal. */
      var KISI = 32;
      var pilihan = null;
      var jarakTerdekat = Infinity;
      var acuan = pusat || [(kotak[0] + kotak[2]) / 2, (kotak[1] + kotak[3]) / 2];

      for (var bx = 0; bx < KISI; bx++) {
        for (var by = 0; by < KISI; by++) {
          var x = kotak[0] + ((bx + 0.5) / KISI) * (kotak[2] - kotak[0]);
          var y = kotak[1] + ((by + 0.5) / KISI) * (kotak[3] - kotak[1]);
          if (!titikDalamCincin(x, y, terbesar)) continue;
          var d = (x - acuan[0]) * (x - acuan[0]) + (y - acuan[1]) * (y - acuan[1]);
          if (d < jarakTerdekat) {
            jarakTerdekat = d;
            pilihan = [x, y];
          }
        }
      }

      return pilihan ? { titik: pilihan, kotak: kotak } : null;
    }

    /* Luas bertanda (rumus tali sepatu). Tandanya tidak dipakai, hanya besarnya. */
    function luasCincin(cincin) {
      var jumlah = 0;
      for (var i = 0, j = cincin.length - 1; i < cincin.length; j = i++) {
        jumlah += cincin[j][0] * cincin[i][1] - cincin[i][0] * cincin[j][1];
      }
      return jumlah / 2;
    }

    function kotakCincin(cincin) {
      var minX = Infinity, maksX = -Infinity, minY = Infinity, maksY = -Infinity;
      for (var i = 0; i < cincin.length; i++) {
        if (cincin[i][0] < minX) minX = cincin[i][0];
        if (cincin[i][0] > maksX) maksX = cincin[i][0];
        if (cincin[i][1] < minY) minY = cincin[i][1];
        if (cincin[i][1] > maksY) maksY = cincin[i][1];
      }
      return [minX, minY, maksX, maksY];
    }

    /* Pusat massa poligon — bukan rata-rata titik sudutnya, yang tertarik ke
       sisi yang simpulnya paling rapat. */
    function pusatMassaCincin(cincin) {
      var luas = luasCincin(cincin);
      if (!luas) return null;
      var x = 0;
      var y = 0;
      for (var i = 0, j = cincin.length - 1; i < cincin.length; j = i++) {
        var silang = cincin[j][0] * cincin[i][1] - cincin[i][0] * cincin[j][1];
        x += (cincin[j][0] + cincin[i][0]) * silang;
        y += (cincin[j][1] + cincin[i][1]) * silang;
      }
      return [x / (6 * luas), y / (6 * luas)];
    }

    /* Titik di dalam poligon? Uji lempar sinar; hole ikut terhitung karena
       aturan ganjil-genap dipakai pada seluruh cincin satu poligon. */
    function titikDalamCincin(x, y, cincin) {
      var dalam = false;
      for (var i = 0, j = cincin.length - 1; i < cincin.length; j = i++) {
        var xi = cincin[i][0], yi = cincin[i][1];
        var xj = cincin[j][0], yj = cincin[j][1];
        if (yi > y !== yj > y && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi) dalam = !dalam;
      }
      return dalam;
    }

    function titikDalamGeometri(latlng, geometri) {
      if (!geometri) return false;
      var poligon = poligonFitur(geometri);
      for (var p = 0; p < poligon.length; p++) {
        var dalam = false;
        for (var c = 0; c < poligon[p].length; c++) {
          if (titikDalamCincin(latlng.lng, latlng.lat, poligon[p][c])) dalam = !dalam;
        }
        if (dalam) return true;
      }
      return false;
    }

    return {
      /* ----------------------------- keadaan ----------------------------- */
      pilihan: null, /* { nama, meta, angka, provinsi, siluet } */
      memuat: false, /* menunggu jawaban GetFeatureInfo */
      /* Popup berita: tab pulau + rentang tanggal. Wilayah yang ditekan hanya
         menentukan tab mana yang terbuka lebih dulu — sesudah itu popup jadi
         jalan masuk ke seluruh berita, jadi tabnya bisa dipindah bebas. */
      tabPulau: PULAU_TAB,
      tabAktif: PULAU_TAB[0].kunci,
      /* Berita dari Blade (CMS Event/Kejadian); mockup window.BERITA hanya
         dipakai bila halaman tidak mengirim data. */
      berita: beritaAwal || window.BERITA || [],
      dari: "",
      sampai: "",
      kalender: null,
      kabar: false, /* layanan peta luar gagal */
      cadangan: false, /* choropleth provinsi dipakai sebagai ganti WMS */
      barisSorot: null, /* nama provinsi yang barisnya menyala */
      /* Tiga provinsi dengan luas deforestasi terbesar — data nyata dari layer
         yang sama dengan yang mewarnai peta (gaya layer: "Choropleth Deforestasi
         per Provinsi"), diambil lewat WFS dengan sortBy=deforestas. */
      atas: [],
      atasGagal: false,
      /* Pencarian provinsi: indeks 38 nama diambil sekali (beberapa KB, tanpa
         geometri) saat kotak cari pertama kali dipakai, lalu disaring lokal. */
      cari: "",
      indeks: [],
      indeksMuat: false,
      hasil: [],
      tabel: [],

      /* Tidak ada getter untuk "panel tampil": markup menuliskan
         x-show="pilihan || memuat" langsung. Bacaan di dalam sebuah getter tidak
         terlacak reaktivitas Alpine, jadi panel tetap terlihat setelah ditutup
         walau keadaannya sudah null. */

      /* ------------------------------- init ------------------------------ */
      init: function () {
        if (!window.L || !window.PETA_PROVINSI) return;

        kurangiGerak = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        TINTA = token("--color-tinta");
        TANPA_DATA = token("--peta-tanpa-data");
        TANGGA = [
          { batas: 350, warna: token("--peta-5"), status: "Siaga darurat" },
          { batas: 250, warna: token("--peta-4"), status: "Siaga" },
          { batas: 150, warna: token("--peta-3"), status: "Siaga" },
          { batas: 50, warna: token("--peta-2"), status: "Waspada" },
          { batas: 1, warna: token("--peta-1"), status: "Normal" },
          { batas: 0, warna: token("--peta-0"), status: "Aman" },
        ];

        this.bangunPeta();
        this.bangunTabel();
        this.ambilTiga();

        this.setelMode();
        this.pasangAksesibilitas();
        this.pantauTile();

        /* ResizeObserver, bukan cuma resize jendela: kotak peta juga berubah
           ukuran tanpa jendela berubah — saat mode berganti, atau saat halaman
           disematkan di iframe yang diubah ukurannya. Rujukannya disimpan agar
           tidak dibuang pemulung memori. */
        if ("ResizeObserver" in window) {
          pengamatUkuran = new ResizeObserver(this.jadwalkanSetelMode.bind(this));
          pengamatUkuran.observe(this.$refs.kotak);
        }
        window.addEventListener("resize", this.jadwalkanSetelMode.bind(this));
      },

      /* ------------------------------ Leaflet ---------------------------- */
      bangunPeta: function () {
        peta = L.map(this.$refs.kotak, {
          zoomControl: false,
          attributionControl: false,
          /* Semua interaksi dimatikan dulu; setelMode() menyalakan yang sesuai. */
          dragging: false,
          touchZoom: false,
          doubleClickZoom: false,
          boxZoom: false,
          keyboard: false,
          scrollWheelZoom: false, /* roda tetikus milik guliran halaman */
          zoomSnap: 0, /* pengepasan persis ke kotak wadah */
          zoomAnimation: !kurangiGerak,
          fadeAnimation: !kurangiGerak,
          maxBoundsViscosity: 1,
        });

        peta.createPane("wilayahPane");
        peta.getPane("wilayahPane").style.zIndex = 350;

        /* Pane batas provinsi terpilih, DI BAWAH poligon provinsi (overlayPane
           z-index 400) supaya tidak menahan klik. Provinsi sendiri tembus
           pandang, jadi outline di bawahnya tetap kelihatan. */
        peta.createPane("batasPane");
        peta.getPane("batasPane").style.zIndex = 380;
        peta.getPane("batasPane").style.pointerEvents = "none";

        /* Angka jumlah kebakaran, DI ATAS poligon provinsi (overlayPane 400)
           supaya tidak tertutup, dan tembus klik supaya provinsi di bawahnya
           tetap bisa ditekan — angkanya penanda, bukan sasaran. */
        peta.createPane("angkaPane");
        peta.getPane("angkaPane").style.zIndex = 450;
        peta.getPane("angkaPane").style.pointerEvents = "none";

        lapisWilayah = L.tileLayer.wms(WMS_URL, {
          layers: WMS_LAYER,
          format: "image/png",
          transparent: true,
          version: "1.1.0",
          pane: "wilayahPane",
        });
        lapisWilayah.addTo(peta);

        lapisWilayah.on("tileload", function () {
          tileTermuat = true;
          if (pengawasTile) window.clearTimeout(pengawasTile);
        });

        lapisWilayah.on(
          "tileerror",
          function () {
            tileGagal++;
            /* Satu tile gagal bisa kebetulan; tiga tanpa satu pun berhasil tidak. */
            if (!tileTermuat && tileGagal >= 3) this.nyalakanCadangan();
          }.bind(this)
        );

        /* Outline provinsi terpilih: halo putih tebal di bawah, garis tinta di
           atas — garis hitam tunggal hilang di atas provinsi yang gelap. */
        garisHalo = L.geoJSON(null, {
          interactive: false,
          pane: "batasPane",
          style: { color: "#ffffff", weight: 5, opacity: 0.9, fill: false, lineJoin: "round" },
        }).addTo(peta);

        garisTerpilih = L.geoJSON(null, {
          interactive: false,
          pane: "batasPane",
          style: { color: TINTA, weight: 2.5, opacity: 1, fill: false, lineJoin: "round" },
        }).addTo(peta);

        provinsi = L.geoJSON(window.PETA_PROVINSI, {
          style: this.gaya.bind(this),
          onEachFeature: function (fitur, lapis) {
            var nama = fitur.properties.nama;

            lapis.on({
              /* Dipilih saat tombol DITEKAN, bukan menunggu "click": Leaflet
                 membatalkan click kalau kursor bergeser sedikit (dianggap awal
                 geser peta), dan di layar sentuh click baru datang setelah
                 peramban selesai mengintai ketukan ganda. */
              mousedown: function (e) {
                var asli = e.originalEvent;
                if (asli && typeof asli.button === "number" && asli.button !== 0) return;
                /* Cegah fokus akibat tetikus: peramban menggulirkan elemen SVG
                   yang difokus ke dalam pandangan, dan guliran itu menggeser
                   peta di bawah kursor. Tab keyboard tetap menjangkau. */
                if (asli && asli.preventDefault) asli.preventDefault();
                this.catatAsal(asli);
                this.pilihDariTitik(e.latlng, nama, lapis.feature.geometry);
              }.bind(this),
            });
          }.bind(this),
        }).addTo(peta);

        this.bangunAngka();
      },

      /* --------------------- angka jumlah kebakaran ---------------------- */

      /* Satu angka per provinsi, diletakkan di dalam daratan terbesarnya.
         Sumbernya sama dengan yang dipakai panel dan tabel setara
         (window.TITIK_PANAS), jadi ketiganya tidak mungkin berbeda. */
      bangunAngka: function () {
        lapisAngka = L.layerGroup([], { pane: "angkaPane" }).addTo(peta);
        daftarAngka = [];

        provinsi.eachLayer(
          function (lapis) {
            var nama = lapis.feature.properties.nama;
            var jumlah = this.angka(nama);
            if (jumlah === null) return; /* tanpa data: tanpa angka */

            var tempat = tempatAngka(lapis.feature.geometry);
            if (!tempat) return;

            var penanda = L.marker([tempat.titik[1], tempat.titik[0]], {
              pane: "angkaPane",
              interactive: false,
              keyboard: false,
              /* Ukuran nol: penempatannya diserahkan ke CSS. Leaflet menulis
                 transform pada elemen ikon untuk memposisikannya, jadi elemen
                 itu sendiri tidak bisa dipusatkan dengan transform lagi —
                 yang dipusatkan adalah <span> di dalamnya. */
              icon: L.divIcon({
                className: "peta-angka",
                iconSize: [0, 0],
                /* aria-hidden: dibacakan satu per satu, angka-angka ini hanya
                   deretan bilangan tanpa konteks. Isi yang sama sudah disajikan
                   dengan nama provinsinya di tabel setara (bangunTabel). */
                html:
                  '<span class="peta-angka__nilai" aria-hidden="true">' +
                  jumlah.toLocaleString("id-ID") +
                  "</span>",
              }),
            });

            penanda.addTo(lapisAngka);
            daftarAngka.push({
              penanda: penanda,
              titik: [tempat.titik[1], tempat.titik[0]],
              kotakDeg: tempat.kotak, /* kotak daratan terbesarnya, derajat */
              elemen: null,
            });
          }.bind(this)
        );

        /* TIDAK dihitung di sini: saat ini peta belum punya center & zoom.
           setelMode() yang memanggilnya, tepat setelah fitBounds.

           Peta ini tidak digeser saat halaman digulir, jadi pendengar di bawah
           hanya berjalan saat zum/geser sungguhan atau saat wadahnya diukur
           ulang. */
        peta.on("zoomend moveend", this.perbaruiAngka.bind(this));
      },

      /* Sembunyikan angka yang benar-benar BERTUMPUK dengan angka lain.

         Sebelumnya yang diuji ukuran wilayahnya: angka disembunyikan kalau
         daratan provinsinya terlihat lebih sempit daripada sekian piksel. Itu
         ukuran yang salah — ia menyembunyikan angka yang sebenarnya punya
         banyak ruang kosong di sekelilingnya (Bali, Gorontalo, Bangka Belitung),
         sementara yang jadi masalah sejak awal hanya satu: dua angka saling
         menutupi sampai keduanya tak terbaca. Di kanvas panggung, menguji
         tumpukan langsung membuat ke-34 angka tampil tanpa satu pun bertabrakan.

         Yang lebih luas menang. Provinsi besar yang kehilangan angkanya karena
         kalah dari tetangga kecil akan terlihat seperti wilayah tanpa data,
         sedangkan provinsi kecil yang angkanya tersembunyi hanya tampak padat —
         dan angkanya tetap terbaca lewat panelnya saat ditekan. */
      perbaruiAngka: function () {
        if (!petaSiap || !daftarAngka.length) return;

        var i;

        /* Semua ditampilkan lebih dulu: elemen ber-display:none tidak punya
           ukuran, padahal ukurannya yang menentukan siapa yang boleh tampil. */
        for (i = 0; i < daftarAngka.length; i++) {
          var el = daftarAngka[i].penanda.getElement();
          daftarAngka[i].elemen = el || null;
          if (el) el.classList.remove("peta-angka--bertumpuk");
        }

        /* Fase baca, tanpa satu pun tulisan di sela-selanya: offsetWidth memaksa
           tata letak dihitung, dan menyelinginya dengan perubahan kelas membuat
           peramban menghitung ulang di tiap putaran. */
        var kotak = [];
        for (i = 0; i < daftarAngka.length; i++) {
          var baris = daftarAngka[i];
          if (!baris.elemen) continue;

          /* offsetWidth, bukan getBoundingClientRect: yang pertama memberi
             ukuran tata letak, tidak terpengaruh transform kanvas panggung —
             satu satuan dengan latLngToContainerPoint. */
          var isi = baris.elemen.firstElementChild;
          var pusat = peta.latLngToContainerPoint(baris.titik);
          var d = baris.kotakDeg;
          var ka = peta.latLngToContainerPoint([d[3], d[0]]);
          var kb = peta.latLngToContainerPoint([d[1], d[2]]);

          kotak.push({
            urut: i,
            x: pusat.x,
            y: pusat.y,
            w: (isi ? isi.offsetWidth : 0) + ANGKA_SELA,
            h: (isi ? isi.offsetHeight : 0) + ANGKA_SELA,
            /* Luas daratan terbesarnya di layar — dipakai sebagai urutan
               kemenangan, bukan sebagai ambang. */
            luas: Math.abs(kb.x - ka.x) * Math.abs(kb.y - ka.y),
          });
        }

        kotak.sort(function (a, b) {
          return b.luas - a.luas;
        });

        var ditempatkan = [];
        for (i = 0; i < kotak.length; i++) {
          var c = kotak[i];
          var bertumpuk = false;

          for (var j = 0; j < ditempatkan.length; j++) {
            var t = ditempatkan[j];
            if (
              Math.abs(c.x - t.x) * 2 < c.w + t.w &&
              Math.abs(c.y - t.y) * 2 < c.h + t.h
            ) {
              bertumpuk = true;
              break;
            }
          }

          if (bertumpuk) daftarAngka[c.urut].elemen.classList.add("peta-angka--bertumpuk");
          else ditempatkan.push(c);
        }
      },

      gaya: function (fitur) {
        var tingkat = this.tingkatUntuk(this.angka(fitur.properties.nama));
        var warna = tingkat ? tingkat.warna : TANPA_DATA;
        if (this.cadangan) {
          return {
            fillColor: warna,
            fillOpacity: 1,
            color: "#ffffff", /* jeda sewarna permukaan antar provinsi */
            weight: 0.9,
            opacity: 1,
          };
        }
        return {
          fillColor: warna,
          fillOpacity: 0, /* tembus pandang: lapisan WMS di bawah tetap terlihat */
          color: "transparent", /* garis batas sudah digambar WMS */
          weight: 0,
          opacity: 0,
        };
      },

      /* --------------------------- data & angka -------------------------- */
      angka: function (nama) {
        /* Layer menulis "Daerah Istimewa Yogyakarta", data contoh menyingkatnya.
           Hanya alias yang benar-benar nama sama yang dipakai di sini — provinsi
           pemecahan Papua sengaja TIDAK dipetakan ke induknya, karena angka
           induk bukan angka pecahannya. */
        var n = (window.TITIK_PANAS || {})[ALIAS_LOKAL[nama] || nama];
        return typeof n === "number" ? n : null;
      },

      tingkatUntuk: function (nilai) {
        if (typeof nilai !== "number") return null;
        for (var i = 0; i < TANGGA.length; i++) {
          if (nilai >= TANGGA[i].batas) return TANGGA[i];
        }
        return null;
      },

      status: function (nama) {
        var khusus = (window.WILAYAH_RAWAN || {})[nama];
        if (khusus && khusus.status) return khusus.status; /* data eksplisit menang */
        var tingkat = this.tingkatUntuk(this.angka(nama));
        return tingkat ? tingkat.status : "Belum ada data";
      },

      keteranganAngka: function (nama) {
        var n = this.angka(nama);
        return n === null ? "belum ada data" : n.toLocaleString("id-ID") + " titik";
      },

      /* ------------------------- asal pop-up ----------------------------- */

      /* Dari mana pop-up terlihat tumbuh. Dicatat saat wilayahnya dipilih —
         bukan saat pop-up dibuat — karena hanya di sanalah masih ada peristiwa
         yang tahu di mana pengguna menekan. */
      catatAsal: function (peristiwa) {
        asalPopup = null;
        if (!peristiwa) return;

        /* Klik tetikus atau ketukan: titik sebenarnya. Tombol yang ditekan
           lewat papan ketik mengirim clientX/clientY nol — itu bukan titik,
           jadi yang dipakai tengah elemennya. */
        if (peristiwa.clientX || peristiwa.clientY) {
          asalPopup = { x: peristiwa.clientX, y: peristiwa.clientY };
          return;
        }

        var el = peristiwa.currentTarget || peristiwa.target || peristiwa;
        if (!el || !el.getBoundingClientRect) return;
        var kotak = el.getBoundingClientRect();
        if (!kotak.width && !kotak.height) return;
        asalPopup = { x: kotak.left + kotak.width / 2, y: kotak.top + kotak.height / 2 };
      },

      /* Dipanggil x-init pop-up: pasang titik jangkar, lalu lepaskan animasinya
         setelah isi panel selesai ditata. */
      pasangAsal: function (el) {
        if (!el) return;

        if (asalPopup) {
          var kotak = el.getBoundingClientRect();
          if (kotak.width && kotak.height) {
            /* Dijepit ke dalam panel: wilayah yang ditekan bisa berada di
               luarnya (peta lebih lebar daripada panel di sebagian ukuran
               layar), dan transform-origin di luar kotak membuat panel melesat
               masuk dari samping alih-alih tumbuh. */
            var x = Math.max(0, Math.min(kotak.width, asalPopup.x - kotak.left));
            var y = Math.max(0, Math.min(kotak.height, asalPopup.y - kotak.top));
            el.style.transformOrigin = x + "px " + y + "px";
          }
        }

        /* Animasinya ditahan CSS (animation-play-state: paused) dan dilepas di
           sini, dua frame kemudian.

           Sebabnya: panel ini lahir di frame yang sibuk. Di frame yang sama
           daftar beritanya dibangun, flatpickr dipasang, fokus dipindah, dan
           kunciGulir() menyetel body{overflow:hidden} — yang menata ulang
           seluruh halaman dan menghilangkan bilah gulirnya. Animasi yang mulai
           di situ kehilangan frame-frame pertamanya, dan awal animasi justru
           bagian yang paling terlihat.

           Dua frame, bukan satu: yang pertama untuk menuntaskan tata letak,
           yang kedua memberi compositor waktu menyiapkan lapisan yang diminta
           will-change sebelum bingkai pertama benar-benar digambar. */
        window.requestAnimationFrame(function () {
          window.requestAnimationFrame(function () {
            el.classList.add("peta-popup--jalan");
          });
        });

        /* Selesai: animasi dan lapisannya dilepas. Dijaga target-nya — peristiwa
           animationend menggelembung, dan isi panel punya animasinya sendiri. */
        el.addEventListener("animationend", function (e) {
          if (e.target !== el) return;
          el.classList.add("peta-popup--diam");
        });
      },

      /* ------------------------------ panel ------------------------------ */
      pilihDariTitik: function (latlng, namaProvinsi, geometriProvinsi) {

        if (!latlng) {
          this.hapusBatas();
          this.isiPanel(namaProvinsi, geometriProvinsi);
          return;
        }

        /* Sudah pernah dibuka? Gambar seketika, tanpa menyentuh jaringan. */
        var tersimpan = this.cariSimpanan(latlng);
        if (tersimpan) {
          nomorPermintaan++;
          this.gambarBatas(tersimpan.geometri);
          this.isiPanel(tersimpan.nama, tersimpan.geometri);
          return;
        }

        /* Belum tersimpan: outline menunggu geometri dari layer (~0,2 detik).
           Poligon lokal TIDAK dipakai sebagai pengganti sementara: layer memakai
           38 provinsi sedangkan data lokal 34, jadi di Papua poligon lokal
           menutupi beberapa provinsi layer sekaligus dan outline-nya akan
           menciut begitu jawaban datang. Panel yang berubah jadi penandanya. */
        var nomor = ++nomorPermintaan;
        this.hapusBatas();
        this.pilihan = null;
        this.memuat = true;
        this.kunciGulir(true);

        this.getFeatureInfo(
          latlng,
          function (fitur) {
            if (nomor !== nomorPermintaan) return; /* wilayah lain sudah ditekan */
            var props = fitur && fitur.properties;
            var nama = props && props[BIDANG_NAMA];
            if (nama) {
              this.barisSorot = nama; /* baris top-3 menyala bila kena */
              this.isiPanel(nama, fitur.geometry, props[BIDANG_PULAU]);
              this.simpanBatas(nama, fitur.geometry);
            } else {
              /* GFI gagal/kosong (mis. ditekan di laut) — panel jatuh ke nama
                 dan poligon provinsi dari data lokal. */
              this.isiPanel(namaProvinsi, geometriProvinsi);
              this.hapusBatas();
            }
          }.bind(this)
        );
      },

      /* `pulauLayer` = level_2 dari layer bila pemanggilnya punya; kalau tidak,
         pulaunya ditebak dari nama provinsi. */
      isiPanel: function (nama, geometri, pulauLayer) {
        var pulau = pulauDariLayer(pulauLayer) || pulauDariProvinsi(nama);
        var jumlah = this.angka(nama);
        var tab = tabDariPulau(pulau);
        if (tab) this.tabAktif = tab;

        this.pilihan = {
          nama: nama || "Wilayah ini",
          /* Judul sudah provinsi, jadi meta tinggal pulaunya — sebelumnya baris
             ini menampung "provinsi · pulau" karena judulnya kabupaten. */
          meta: pulau || "",
          angka: jumlah === null ? null : jumlah.toLocaleString("id-ID"),
          siluet: geometri ? siluet(geometri) : null,
        };
        this.memuat = false;
        this.kunciGulir(true);
      },

      /* Popup menutupi hampir seluruh layar, jadi halaman di belakangnya
         dikunci — sama seperti laci navigasi (js/nav.js). */
      kunciGulir: function (kunci) {
        /* Penanda untuk pop-up rincian: saat ia ditutup, halaman hanya boleh
           dilepas kembali kalau panel ini memang sudah tidak terbuka. */
        document.body.classList.toggle("peta-terbuka", kunci);
        document.body.style.overflow = kunci ? "hidden" : "";
      },

      /* Berita yang lolos tab pulau DAN rentang tanggal. Dipanggil sebagai
         metode, bukan getter: bacaan di dalam getter tidak terlacak
         reaktivitas Alpine di versi yang dipakai di sini. */
      beritaTampil: function () {
        var tab = PULAU_TAB.find(
          function (t) {
            return t.kunci === this.tabAktif;
          }.bind(this)
        );
        var isi = tab ? tab.isi : [];
        var awal = waktuIso(this.dari);
        var akhir = waktuIso(this.sampai);

        return (this.berita || []).filter(function (berita) {
          if (isi.indexOf(berita.pulau) === -1) return false;
          var waktu = waktuTeks(berita.tanggal);
          if (waktu === null) return true;
          if (awal !== null && waktu < awal) return false;
          if (akhir !== null && waktu > akhir) return false;
          return true;
        });
      },

      labelTab: function () {
        var tab = PULAU_TAB.find(
          function (t) {
            return t.kunci === this.tabAktif;
          }.bind(this)
        );
        return tab ? tab.label : "";
      },

      /* Satu kolom untuk rentang tanggal, digerakkan flatpickr (mode range).
         Dipasang lewat x-init pada kolomnya; `dari`/`sampai` tetap dua nilai
         terpisah karena beritaTampil() menyaring memakai keduanya. */
      pasangKalender: function (kolom) {
        if (!kolom || !window.flatpickr) return;

        this.kalender = window.flatpickr(kolom, {
          mode: "range",
          dateFormat: "Y-m-d",
          /* Kolomnya readonly (bawaan flatpickr) supaya tidak bisa diketik
             bebas; pemisahnya disamakan dengan tanda pisah di rancangan. */
          locale: { rangeSeparator: " – " },
          onChange: function (tanggal) {
            /* Saat baru satu tanggal dipilih, rentangnya belum utuh —
               keduanya diisi sama supaya penyaringan tetap masuk akal. */
            this.dari = tanggal[0] ? this.keIso(tanggal[0]) : "";
            this.sampai = tanggal[1]
              ? this.keIso(tanggal[1])
              : (tanggal[0] ? this.keIso(tanggal[0]) : "");
          }.bind(this),
        });
      },

      /* Tanggal lokal ke YYYY-MM-DD tanpa lewat toISOString(), yang mengubah
         ke UTC dan bisa memundurkan satu hari di zona waktu Indonesia. */
      keIso: function (tanggal) {
        var bulan = String(tanggal.getMonth() + 1).padStart(2, "0");
        var hari = String(tanggal.getDate()).padStart(2, "0");

        return tanggal.getFullYear() + "-" + bulan + "-" + hari;
      },

      hapusTanggal: function () {
        this.dari = "";
        this.sampai = "";
        if (this.kalender) this.kalender.clear();
      },

      tutup: function () {
        this.hapusBatas();
        this.bersihkanSorot();
        this.pilihan = null;
        this.memuat = false;
        this.kunciGulir(false);
        /* Rentang tanggal ikut bersih supaya popup berikutnya tidak terbuka
           dengan saringan tak terlihat dari wilayah sebelumnya. */
        this.hapusTanggal();
      },

      /* Identifikasi provinsi di sebuah titik via WMS GetFeatureInfo.
         Responsnya membawa atribut DAN geometri, jadi satu permintaan ini cukup
         untuk mengisi panel sekaligus menggambar outline — bukan permintaan lagi
         berupa belasan tile ber-filter. GeoServer menyederhanakan geometrinya
         mengikuti resolusi yang diminta, jadi ukurannya ikut bingkai di bawah.

         `derajat` (opsional) mengganti bingkai peta yang sedang tampil dengan
         kotak sendiri selebar sekian derajat, berpusat di `latlng` dan ditanya
         tepat di tengah. Itu perlu pada jalur pilih-lewat-nama: di ponsel peta
         hanya ~150px untuk seluruh Indonesia, satu piksel query menutupi puluhan
         kilometer, dan provinsi sekecil DKI Jakarta atau DI Yogyakarta terjawab
         sebagai tetangganya yang melingkupinya. */
      getFeatureInfo: function (latlng, kembali, derajat) {
        var kotak;
        var lebar;
        var tinggi;
        var x;
        var y;

        if (derajat) {
          var sisi = derajat / 2;
          kotak = [
            latlng.lng - sisi,
            latlng.lat - sisi,
            latlng.lng + sisi,
            latlng.lat + sisi,
          ].join(",");
          lebar = PIKSEL_QUERY;
          tinggi = PIKSEL_QUERY;
          x = Math.round(PIKSEL_QUERY / 2);
          y = Math.round(PIKSEL_QUERY / 2);
        } else {
          var ukuran = peta.getSize();
          var titik = peta.latLngToContainerPoint(latlng);
          kotak = peta.getBounds().toBBoxString();
          lebar = ukuran.x;
          tinggi = ukuran.y;
          x = Math.round(titik.x);
          y = Math.round(titik.y);
        }

        var params = new URLSearchParams({
          service: "WMS",
          version: "1.1.0",
          request: "GetFeatureInfo",
          layers: WMS_LAYER,
          query_layers: WMS_LAYER,
          info_format: "application/json",
          feature_count: "1",
          srs: "EPSG:4326",
          bbox: kotak,
          width: String(lebar),
          height: String(tinggi),
          x: String(x),
          y: String(y),
        });

        fetch(WMS_URL + "?" + params.toString())
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            var fitur = data && data.features && data.features[0];
            kembali(fitur || null);
          })
          .catch(function () {
            kembali(null);
          });
      },

      /* --------------------------- outline batas ------------------------- */
      cariSimpanan: function (latlng) {
        var kunci = Object.keys(simpananGeometri);
        for (var i = 0; i < kunci.length; i++) {
          if (titikDalamGeometri(latlng, simpananGeometri[kunci[i]])) {
            return { nama: kunci[i], geometri: simpananGeometri[kunci[i]] };
          }
        }
        return null;
      },

      simpanBatas: function (nama, geometri) {
        if (!nama) return;
        if (geometri) simpananGeometri[nama] = geometri;
        this.gambarBatas(simpananGeometri[nama]);
      },

      hapusBatas: function () {
        if (!garisHalo) return;
        garisHalo.clearLayers();
        garisTerpilih.clearLayers();
      },

      /* Satu outline saja — provinsi yang ditekan. */
      gambarBatas: function (geometri) {
        this.hapusBatas();
        if (!geometri) return;
        garisHalo.addData(geometri);
        garisTerpilih.addData(geometri);
      },

      /* --------------------- tiga teratas & pencarian --------------------- */

      /* Tiga provinsi dengan luas deforestasi terbesar. Urut menurun dengan
         null dikecualikan — tanpa filter itu server menaruh nilai kosong lebih
         dulu, dan yang muncul justru provinsi tanpa data (mis. DKI Jakarta). */
      ambilTiga: function () {
        var alamat =
          WFS_URL +
          "?" +
          new URLSearchParams({
            service: "WFS",
            version: "1.1.0",
            request: "GetFeature",
            typeName: WMS_LAYER,
            propertyName: BIDANG_PULAU + "," + BIDANG_NAMA + "," + BIDANG_LUAS,
            sortBy: BIDANG_LUAS + " D",
            maxFeatures: "3",
            outputFormat: "application/json",
            CQL_FILTER: BIDANG_LUAS + " IS NOT NULL",
          }).toString();

        fetch(alamat)
          .then(function (r) {
            return r.json();
          })
          .then(
            function (data) {
              var fitur = (data && data.features) || [];
              if (!fitur.length) {
                this.atasGagal = true;
                return;
              }
              this.atas = fitur.map(function (f, i) {
                return {
                  peringkat: i + 1,
                  nama: f.properties[BIDANG_NAMA],
                  pulau: pulauDariLayer(f.properties[BIDANG_PULAU]),
                  luas: Math.round(f.properties[BIDANG_LUAS]).toLocaleString("id-ID"),
                  siluet: null,
                };
              });
              /* Siluet menyusul: hanya perlu saat daftarnya benar-benar tampil
                 (mode aliran), dan tiap siluet memakan dua permintaan kecil.
                 Dijadwalkan terpisah supaya kegagalan pengambilan bentuk tidak
                 ikut menandai ANGKA-nya sebagai gagal. */
              if (mode() !== "panggung") {
                Promise.resolve().then(this.ambilSiluetTiga.bind(this));
              }
            }.bind(this)
          )
          .catch(
            function () {
              this.atasGagal = true;
            }.bind(this)
          );
      },

      ambilSiluetTiga: function () {
        this.atas.forEach(
          function (baris) {
            if (baris.siluet) return;
            this.geometriWilayah(baris.nama).then(
              function (geometri) {
                if (geometri) baris.siluet = siluet(geometri);
              }
            );
          }.bind(this)
        );
      },

      /* Melokasikan provinsi dari NAMANYA, tanpa mengunduh geometri penuh.

         Geometri lengkap lewat WFS berat (garis pantai penuh, ratusan KB per
         provinsi) dan tidak bisa diminta dalam bentuk sederhana. Jalan yang
         murah: minta GetMap kecil yang hanya menggambar provinsi itu, lalu baca
         pikselnya — piksel tergambar yang paling dekat pusat massa pasti berada
         di dalam wilayahnya. Titik itu lalu dipakai pada GetFeatureInfo, yang
         mengembalikan geometri versi sederhana (~3-5 KB). */
      lokasiWilayah: function (nama) {
        /* Kotak pindaian pertama: provinsi itu sendiri menurut data lokal, jadi
           wilayahnya hampir memenuhi kotak dan 120 piksel sudah cukup. */
        var kotakLokal = bbeksProvinsi(provinsiLokal(nama));
        /* Cadangan: seluruh Indonesia. Kotak selebar itu perlu raster jauh lebih
           besar — pada 120 piksel provinsi sekecil DKI Jakarta tidak menyisakan
           satu piksel pun untuk dibaca. */
        var kotakNasional = [BATAS[0][1], BATAS[0][0], BATAS[1][1], BATAS[1][0]];

        var pindai = function (kotak, LEBAR) {
          var alamat =
            WMS_URL +
            "?" +
            new URLSearchParams({
              service: "WMS",
              version: "1.1.0",
              request: "GetMap",
              layers: WMS_LAYER,
              format: "image/png",
              transparent: "true",
              srs: "EPSG:4326",
              bbox: kotak.join(","),
              width: String(LEBAR),
              height: String(LEBAR),
              CQL_FILTER: BIDANG_NAMA + "='" + String(nama).replace(/'/g, "''") + "'",
            }).toString();

          return new Promise(function (selesai) {
            var gambar = new Image();
            gambar.crossOrigin = "anonymous"; /* layanan mengizinkan; perlu untuk baca piksel */
            gambar.onload = function () {
              var kanvas = document.createElement("canvas");
              kanvas.width = LEBAR;
              kanvas.height = LEBAR;
              var ctx = kanvas.getContext("2d");
              ctx.drawImage(gambar, 0, 0);
              var data;
              try {
                data = ctx.getImageData(0, 0, LEBAR, LEBAR).data;
              } catch (_) {
                selesai(null); /* kanvas ternoda: tanpa CORS tidak bisa dibaca */
                return;
              }

              var titik = [];
              var jumlahX = 0;
              var jumlahY = 0;
              for (var y = 0; y < LEBAR; y++) {
                for (var x = 0; x < LEBAR; x++) {
                  if (data[(y * LEBAR + x) * 4 + 3] > 40) {
                    titik.push([x, y]);
                    jumlahX += x;
                    jumlahY += y;
                  }
                }
              }
              if (!titik.length) {
                selesai(null);
                return;
              }

              /* Piksel tergambar terdekat ke pusat massa — pusat massa sendiri
                 bisa jatuh di luar wilayah yang bentuknya cekung. */
              var px = jumlahX / titik.length;
              var py = jumlahY / titik.length;
              var pilih = titik[0];
              var jarakTerdekat = Infinity;
              titik.forEach(function (t) {
                var d = (t[0] - px) * (t[0] - px) + (t[1] - py) * (t[1] - py);
                if (d < jarakTerdekat) {
                  jarakTerdekat = d;
                  pilih = t;
                }
              });

              selesai({
                lng: kotak[0] + ((pilih[0] + 0.5) / LEBAR) * (kotak[2] - kotak[0]),
                lat: kotak[3] - ((pilih[1] + 0.5) / LEBAR) * (kotak[3] - kotak[1]),
              });
            };
            gambar.onerror = function () {
              selesai(null);
            };
            gambar.src = alamat;
          });
        };

        if (!kotakLokal) return pindai(kotakNasional, 512);

        /* Kotak lokal bisa meleset: data lokal masih 34 provinsi, jadi provinsi
           baru dipetakan ke induk lamanya dan induk itu belum tentu memuatnya
           (Papua lokal hanya separuh timur, sedangkan Papua Barat Daya ada di
           ujung barat). Nol piksel = meleset, dan pindaian nasional yang
           menjawab — sekali saja, dan hanya saat memang perlu. */
        return pindai(kotakLokal, 120).then(function (titik) {
          return titik || pindai(kotakNasional, 512);
        });
      },

      /* Geometri provinsi: dari simpanan bila ada, kalau tidak lokasikan dulu
         lalu tanya GetFeatureInfo di titik itu. */
      geometriWilayah: function (nama) {
        var kunci = String(nama);
        if (simpananGeometri[kunci]) return Promise.resolve(simpananGeometri[kunci]);

        /* Bingkai query seukuran provinsinya sendiri, bukan seukuran peta yang
           sedang tampil — alasannya di getFeatureInfo. */
        var kotak = bbeksProvinsi(provinsiLokal(nama));
        var derajat = kotak
          ? Math.max(kotak[2] - kotak[0], kotak[3] - kotak[1])
          : 0;

        return this.lokasiWilayah(nama).then(
          function (titik) {
            if (!titik) return null;
            return new Promise(
              function (selesai) {
                this.getFeatureInfo(
                  titik,
                  function (fitur) {
                    var props = fitur && fitur.properties;
                    /* Namanya harus cocok. Kalau yang terjawab provinsi lain,
                       lebih baik tanpa outline daripada outline yang salah —
                       panelnya sudah menyebut nama yang benar. */
                    if (fitur && fitur.geometry && props[BIDANG_NAMA] === nama) {
                      simpananGeometri[kunci] = fitur.geometry;
                      selesai(fitur.geometry);
                    } else {
                      selesai(null);
                    }
                  },
                  derajat
                );
              }.bind(this)
            );
          }.bind(this)
        );
      },

      /* Dipakai baris daftar maupun hasil pencarian: buka panel, lalu gambar
         outline-nya begitu geometrinya didapat. */
      pilihWilayah: function (nama, pulau, peristiwa) {
        this.catatAsal(peristiwa);
        var nomor = ++nomorPermintaan;
        this.barisSorot = nama;
        this.hasil = [];
        this.cari = "";
        this.isiPanel(nama, null, pulau);
        this.hapusBatas();

        this.geometriWilayah(nama).then(
          function (geometri) {
            if (nomor !== nomorPermintaan || !geometri) return;
            this.gambarBatas(geometri);
            this.isiPanel(nama, geometri, pulau);
          }.bind(this)
        );
      },

      /* --- pencarian --- */

      muatIndeks: function () {
        if (this.indeksMuat || this.indeks.length) return;
        this.indeksMuat = true;
        var alamat =
          WFS_URL +
          "?" +
          new URLSearchParams({
            service: "WFS",
            version: "1.1.0",
            request: "GetFeature",
            typeName: WMS_LAYER,
            propertyName: BIDANG_PULAU + "," + BIDANG_NAMA, /* tanpa geometri: 38 nama */
            outputFormat: "application/json",
          }).toString();

        fetch(alamat)
          .then(function (r) {
            return r.json();
          })
          .then(
            function (data) {
              this.indeks = ((data && data.features) || [])
                .map(function (f) {
                  return {
                    nama: f.properties[BIDANG_NAMA],
                    pulau: pulauDariLayer(f.properties[BIDANG_PULAU]),
                  };
                })
                .filter(function (k) {
                  return k.nama;
                })
                .sort(function (a, b) {
                  return a.nama.localeCompare(b.nama, "id");
                });
              this.saring();
            }.bind(this)
          )
          .catch(
            function () {
              this.indeksMuat = false;
            }.bind(this)
          );
      },

      saring: function () {
        var kata = this.cari.trim().toLowerCase();
        if (kata.length < 2) {
          this.hasil = [];
          return;
        }
        var awalan = [];
        var didalam = [];
        this.indeks.forEach(function (k) {
          var n = k.nama.toLowerCase();
          var i = n.indexOf(kata);
          if (i === 0) awalan.push(k);
          else if (i > 0) didalam.push(k);
        });
        /* Yang namanya diawali kata kunci lebih dulu — itu yang biasanya dicari. */
        this.hasil = awalan.concat(didalam).slice(0, MAKS_HASIL);
      },

      /* Peta warna tidak bisa dibaca pembaca layar, jadi angka yang sama juga
         disajikan sebagai tabel (tersembunyi secara visual di kedua mode). */
      bangunTabel: function () {
        this.tabel = window.PETA_PROVINSI.features
          .map(
            function (f) {
              var nama = f.properties.nama;
              return {
                nama: nama,
                titik: this.keteranganAngka(nama),
                status: this.status(nama),
                urut: this.angka(nama) || 0,
              };
            }.bind(this)
          )
          .sort(function (a, b) {
            return b.urut - a.urut;
          });
      },

      sorotBaris: function (nama) {
        this.barisSorot = nama;
      },

      bersihkanSorot: function () {
        if (pewaktuSorot) {
          window.clearTimeout(pewaktuSorot);
          pewaktuSorot = null;
        }
        this.barisSorot = null;
      },

      /* ---------------------------- cadangan ----------------------------- */
      pantauTile: function () {
        tileTermuat = false;
        tileGagal = 0;
        if (pengawasTile) window.clearTimeout(pengawasTile);
        pengawasTile = window.setTimeout(
          function () {
            /* Diam sepenuhnya juga kegagalan: pada DNS yang dibuang ke lubang
               hitam, tileerror bisa tak pernah datang. */
            if (!tileTermuat) this.nyalakanCadangan();
          }.bind(this),
          TUNGGU_TILE
        );
      },

      nyalakanCadangan: function () {
        if (this.cadangan) return;
        this.cadangan = true;
        this.kabar = true;
        if (pengawasTile) window.clearTimeout(pengawasTile);
        peta.removeLayer(lapisWilayah); /* berhenti mencoba; tombol memutuskan */
        provinsi.setStyle(this.gaya.bind(this));
      },

      matikanCadangan: function () {
        this.cadangan = false;
        this.kabar = false;
        provinsi.setStyle(this.gaya.bind(this));
        lapisWilayah.addTo(peta);
        lapisWilayah.redraw();
        this.pantauTile();
      },

      /* ------------------------ mode & pengepasan ------------------------ */
      jadwalkanSetelMode: function () {
        if (tundaUkur) window.clearTimeout(tundaUkur);
        /* setTimeout, bukan rAF: pengepasan peta bukan pekerjaan animasi, dan
           rAF tidak dipicu di dokumen yang tersembunyi. */
        tundaUkur = window.setTimeout(
          function () {
            tundaUkur = null;
            this.setelMode();
          }.bind(this),
          60
        );
      },

      setelMode: function () {
        if (!peta) return;
        var sekarang = mode();

        if (sekarang !== modeTerpasang) {
          modeTerpasang = sekarang;
          if (sekarang === "panggung") {
            peta.dragging.enable();
            peta.doubleClickZoom.enable();
            peta.keyboard.enable();
          } else {
            /* Mode aliran: peta statis — guliran halaman lebih penting di layar
               sentuh daripada geser peta. */
            peta.dragging.disable();
            peta.doubleClickZoom.disable();
            peta.keyboard.disable();
            /* Daftar top-3 baru terlihat di mode ini; siluetnya diambil sekarang. */
            if (this.atas.length) this.ambilSiluetTiga();
          }
        }

        /* Kunci dari pengepasan sebelumnya harus dilepas lebih dulu: kalau
           minZoom masih terpatok pada zum wadah yang lebih besar, fitBounds
           tidak bisa memperkecil peta saat wadahnya menyempit. */
        peta.setMinZoom(0);
        peta.setMaxZoom(20);
        peta.setMaxBounds(null);

        peta.invalidateSize({ animate: false });
        peta.fitBounds(BATAS, { padding: [6, 6], animate: false });
        petaSiap = true; /* sejak sini koordinat sudah bisa diproyeksikan */

        peta.setMinZoom(peta.getZoom());
        peta.setMaxZoom(peta.getZoom() + 3);
        peta.setMaxBounds(peta.getBounds().pad(0.08));

        /* fitBounds di atas memang memicu moveend, tetapi pengepasan mode juga
           mengubah UKURAN wadah — dan itu sendiri tidak selalu berujung pada
           peristiwa peta. Dihitung ulang di sini supaya tidak bergantung pada
           kebetulan. */
        this.perbaruiAngka();
      },

      /* --------------------------- aksesibilitas ------------------------- */
      /* Tiap wilayah jadi sasaran fokus keyboard. Harus SETELAH peta punya
         tampilan awal — sebelum itu Leaflet belum membuat elemen <path>. */
      pasangAksesibilitas: function () {
        provinsi.eachLayer(
          function (lapis) {
            var nama = lapis.feature.properties.nama;
            var elemen = lapis.getElement();
            if (!elemen) return;

            elemen.setAttribute("tabindex", "0");
            elemen.setAttribute("role", "button");
            elemen.setAttribute(
              "aria-label",
              nama + ", " + this.keteranganAngka(nama) + " — tekan untuk artikel terkait"
            );

            /* Sorotan provinsi HANYA untuk navigasi keyboard: klik tetikus juga
               memberi fokus, dan di sana penanda yang benar adalah outline dari
               layer di titik yang ditekan — bukan poligon lokal ini. */
            var fokusKeyboard = function () {
              try {
                return elemen.matches(":focus-visible");
              } catch (_) {
                return true;
              }
            };

            elemen.addEventListener(
              "focus",
              function () {
                if (!fokusKeyboard()) return;
                lapis.setStyle({
                  weight: 2,
                  color: TINTA,
                  /* Di mode cadangan isian tidak boleh dihapus — kalau tidak,
                     provinsi yang disorot jadi lubang di choropleth. */
                  fillOpacity: this.cadangan ? 1 : 0,
                  opacity: 1,
                });
                lapis.bringToFront();
                this.catatAsal(elemen);
                this.isiPanel(nama, lapis.feature.geometry);
              }.bind(this)
            );

            elemen.addEventListener(
              "blur",
              function () {
                provinsi.resetStyle(lapis);
                this.bersihkanSorot();
              }.bind(this)
            );
          }.bind(this)
        );
      },

      /* Escape menutup panel dan melepas sorotan. Bila pop-up rincian sedang
         menumpang di atas panel ini, Escape itu miliknya — tanpa penjagaan ini
         satu tekanan menutup keduanya sekaligus. */
      tutupDenganEscape: function (e) {
        if (e.key !== "Escape") return;
        if (document.body.classList.contains("rincian-terbuka")) return;
        this.tutup();
      },
    };
  });
});
