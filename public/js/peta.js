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
 * choropleth WMS Simontini (KABUPATEN_STADI_2025), lalu poligon provinsi
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
    var lapisKabupaten = null;
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
    var WMS_LAYER = "proteus:KABUPATEN_STADI_2025";
    var MAKS_HASIL = 8; /* hasil pencarian yang ditampilkan sekaligus */
    var JEDA_SOROT = 2400; /* ms; lama baris daftar menyala setelah diketuk */
    var TUNGGU_TILE = 8000; /* ms; tanpa satu pun tile termuat setelah ini = gagal */

    /* Kotak pembatas Indonesia — dipakai mengepaskan peta ke wadahnya. */
    var BATAS = [
      [-11.2, 94.7],
      [6.4, 141.3],
    ];

    /* Provinsi -> pulau, supaya artikel (di-key per pulau di data/konten.js)
       bisa diambil dari kabupaten/provinsi yang diklik. */
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
      /* Cadangan: kabupaten kadang menulis "Sumatera" lengkap, cek awalan. */
      var kunci = Object.keys(PROVINSI_KE_PULAU).find(function (k) {
        return nama.indexOf(k) === 0 || k.indexOf(nama) === 0;
      });
      return kunci ? PROVINSI_KE_PULAU[kunci] : null;
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
       pencarian saat melokasikan kabupaten lewat gambar (lihat lokasiKabupaten). */
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

    /* Nama provinsi di layer kabupaten kadang beda ejaan dengan data lokal
       (mis. provinsi Papua hasil pemecahan 2022 belum ada di data 34 provinsi). */
    function provinsiLokal(nama) {
      if (!nama) return null;
      var semua = (window.PETA_PROVINSI.features || []).map(function (f) {
        return f.properties.nama;
      });
      if (semua.indexOf(nama) > -1) return nama;
      var cocok = semua.filter(function (n) {
        return nama.indexOf(n) === 0 || n.indexOf(nama) === 0;
      });
      return cocok[0] || null;
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
      barisSorot: null, /* nama kabupaten yang barisnya menyala */
      /* Tiga kabupaten dengan luas deforestasi terbesar — data nyata dari layer
         yang sama dengan yang mewarnai peta (gaya layer: "Choropleth Deforestasi
         per Kabupaten"), diambil lewat WFS dengan sortBy=luas. */
      atas: [],
      atasGagal: false,
      /* Pencarian kabupaten: indeks 515 nama diambil sekali (67 KB, tanpa
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

        peta.createPane("kabupatenPane");
        peta.getPane("kabupatenPane").style.zIndex = 350;

        /* Pane batas kabupaten terpilih, DI BAWAH poligon provinsi (overlayPane
           z-index 400) supaya tidak menahan klik. Provinsi sendiri tembus
           pandang, jadi outline di bawahnya tetap kelihatan. */
        peta.createPane("batasPane");
        peta.getPane("batasPane").style.zIndex = 380;
        peta.getPane("batasPane").style.pointerEvents = "none";

        lapisKabupaten = L.tileLayer.wms(WMS_URL, {
          layers: WMS_LAYER,
          format: "image/png",
          transparent: true,
          version: "1.1.0",
          pane: "kabupatenPane",
        });
        lapisKabupaten.addTo(peta);

        lapisKabupaten.on("tileload", function () {
          tileTermuat = true;
          if (pengawasTile) window.clearTimeout(pengawasTile);
        });

        lapisKabupaten.on(
          "tileerror",
          function () {
            tileGagal++;
            /* Satu tile gagal bisa kebetulan; tiga tanpa satu pun berhasil tidak. */
            if (!tileTermuat && tileGagal >= 3) this.nyalakanCadangan();
          }.bind(this)
        );

        /* Outline kabupaten terpilih: halo putih tebal di bawah, garis tinta di
           atas — garis hitam tunggal hilang di atas kabupaten yang gelap. */
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
                this.pilihDariTitik(e.latlng, nama, lapis.feature.geometry);
              }.bind(this),
            });
          }.bind(this),
        }).addTo(peta);
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
        var n = (window.TITIK_PANAS || {})[nama];
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

      /* ------------------------------ panel ------------------------------ */
      pilihDariTitik: function (latlng, namaProvinsi, geometriProvinsi) {

        if (!latlng) {
          this.hapusBatas();
          this.isiPanel(namaProvinsi, null, geometriProvinsi);
          return;
        }

        /* Sudah pernah dibuka? Gambar seketika, tanpa menyentuh jaringan. */
        var tersimpan = this.cariSimpanan(latlng);
        if (tersimpan) {
          nomorPermintaan++;
          this.gambarBatas(tersimpan.geometri);
          this.isiPanel(tersimpan.level_3, tersimpan.level_4, tersimpan.geometri);
          return;
        }

        /* Belum tersimpan: outline menunggu geometri kabupaten (~0,2 detik) —
           tidak digambar outline provinsi sebagai pengganti sementara, karena
           yang ditandai harus kabupaten. Panel yang berubah jadi penandanya. */
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
            if (props && props.level_4) {
              this.barisSorot = props.level_4; /* baris top-3 menyala bila kena */
              this.isiPanel(props.level_3 || namaProvinsi, props.level_4, fitur.geometry);
              this.simpanBatas(props.level_3 || namaProvinsi, props.level_4, fitur.geometry);
            } else {
              /* GFI gagal/kosong (mis. ditekan di laut) — panel jatuh ke
                 provinsi, dan tak ada outline: tak ada kabupaten untuk ditandai. */
              this.isiPanel(namaProvinsi, null, geometriProvinsi);
              this.hapusBatas();
            }
          }.bind(this)
        );
      },

      isiPanel: function (namaProvinsi, kabupaten, geometri) {
        var pulau = pulauDariProvinsi(namaProvinsi);
        var jumlah = this.angka(namaProvinsi);
        var tab = tabDariPulau(pulau);
        if (tab) this.tabAktif = tab;

        this.pilihan = {
          /* Panel selalu punya judul: bila kabupaten tak terkenali, provinsinya
             yang naik jadi judul — dan tidak diulang pada baris meta. */
          nama: kabupaten || namaProvinsi || "Wilayah ini",
          meta: (kabupaten ? [namaProvinsi, pulau] : [pulau]).filter(Boolean).join(" · "),
          /* Angkanya berskala provinsi, jadi kalimatnya menyebut provinsi —
             tanpa itu akan terbaca sebagai angka kabupaten yang diklik. */
          angka: jumlah === null ? null : jumlah.toLocaleString("id-ID"),
          provinsi: namaProvinsi,
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

      /* Identifikasi kabupaten di titik tekan via WMS GetFeatureInfo.
         Responsnya membawa atribut DAN geometri (1,5–13 KB), jadi satu
         permintaan ini cukup untuk mengisi panel sekaligus menggambar outline —
         bukan permintaan lagi berupa belasan tile ber-filter. */
      getFeatureInfo: function (latlng, kembali) {
        var ukuran = peta.getSize();
        var titik = peta.latLngToContainerPoint(latlng);
        var params = new URLSearchParams({
          service: "WMS",
          version: "1.1.0",
          request: "GetFeatureInfo",
          layers: WMS_LAYER,
          query_layers: WMS_LAYER,
          info_format: "application/json",
          feature_count: "1",
          srs: "EPSG:4326",
          bbox: peta.getBounds().toBBoxString(),
          width: String(ukuran.x),
          height: String(ukuran.y),
          x: String(Math.round(titik.x)),
          y: String(Math.round(titik.y)),
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
            var bagian = kunci[i].split("|");
            return {
              level_3: bagian[0],
              level_4: bagian[1],
              geometri: simpananGeometri[kunci[i]],
            };
          }
        }
        return null;
      },

      simpanBatas: function (level_3, level_4, geometri) {
        if (!level_4) return;
        var kunci = String(level_3) + "|" + String(level_4);
        if (geometri) simpananGeometri[kunci] = geometri;
        this.gambarBatas(simpananGeometri[kunci]);
      },

      hapusBatas: function () {
        if (!garisHalo) return;
        garisHalo.clearLayers();
        garisTerpilih.clearLayers();
      },

      /* Hanya batas kabupaten yang digambar di sini: yang ditekan pengguna
         adalah kabupaten, dan dua outline sekaligus membuat penandanya ambigu. */
      gambarBatas: function (geometri) {
        this.hapusBatas();
        if (!geometri) return;
        garisHalo.addData(geometri);
        garisTerpilih.addData(geometri);
      },

      /* --------------------- tiga teratas & pencarian --------------------- */

      /* Tiga kabupaten dengan luas deforestasi terbesar. sortBy=luas D dengan
         null dikecualikan — tanpa filter itu server menaruh nilai kosong lebih
         dulu, dan yang muncul justru kabupaten tanpa data. */
      ambilTiga: function () {
        var alamat =
          WFS_URL +
          "?" +
          new URLSearchParams({
            service: "WFS",
            version: "1.1.0",
            request: "GetFeature",
            typeName: WMS_LAYER,
            propertyName: "level_3,level_4,luas",
            sortBy: "luas D",
            maxFeatures: "3",
            outputFormat: "application/json",
            CQL_FILTER: "luas IS NOT NULL",
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
                  nama: f.properties.level_4,
                  provinsi: f.properties.level_3,
                  luas: Math.round(f.properties.luas).toLocaleString("id-ID"),
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
            this.geometriKabupaten(baris.nama, baris.provinsi).then(
              function (geometri) {
                if (geometri) baris.siluet = siluet(geometri);
              }
            );
          }.bind(this)
        );
      },

      /* Melokasikan kabupaten dari NAMANYA, tanpa mengunduh geometri penuh.

         Geometri kabupaten lewat WFS berukuran ~520 KB dan tidak bisa diminta
         dalam bentuk sederhana. Jalan yang murah: minta GetMap kecil (120x120,
         ~2 KB) yang hanya menggambar kabupaten itu di dalam kotak provinsinya,
         lalu baca pikselnya — piksel tergambar yang paling dekat pusat massa
         pasti berada di dalam wilayahnya. Titik itu lalu dipakai pada
         GetFeatureInfo, yang mengembalikan geometri versi sederhana (~3-5 KB). */
      lokasiKabupaten: function (nama, provinsi) {
        var kotak = bbeksProvinsi(provinsiLokal(provinsi));
        if (!kotak) return Promise.resolve(null);

        var LEBAR = 120;
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
            CQL_FILTER: "level_4='" + String(nama).replace(/'/g, "''") + "'",
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
      },

      /* Geometri kabupaten: dari simpanan bila ada, kalau tidak lokasikan dulu
         lalu tanya GetFeatureInfo di titik itu. */
      geometriKabupaten: function (nama, provinsi) {
        var kunci = String(provinsi) + "|" + String(nama);
        if (simpananGeometri[kunci]) return Promise.resolve(simpananGeometri[kunci]);

        return this.lokasiKabupaten(nama, provinsi).then(
          function (titik) {
            if (!titik) return null;
            return new Promise(
              function (selesai) {
                this.getFeatureInfo(titik, function (fitur) {
                  if (fitur && fitur.geometry) {
                    simpananGeometri[kunci] = fitur.geometry;
                    selesai(fitur.geometry);
                  } else {
                    selesai(null);
                  }
                });
              }.bind(this)
            );
          }.bind(this)
        );
      },

      /* Dipakai baris daftar maupun hasil pencarian: buka panel, lalu gambar
         outline-nya begitu geometrinya didapat. */
      pilihKabupaten: function (nama, provinsi) {
        var nomor = ++nomorPermintaan;
        this.barisSorot = nama;
        this.hasil = [];
        this.cari = "";
        this.isiPanel(provinsi, nama, null);
        this.hapusBatas();

        this.geometriKabupaten(nama, provinsi).then(
          function (geometri) {
            if (nomor !== nomorPermintaan || !geometri) return;
            this.gambarBatas(geometri);
            this.isiPanel(provinsi, nama, geometri);
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
            propertyName: "level_3,level_4", /* tanpa geometri: 515 nama, ~67 KB */
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
                  return { nama: f.properties.level_4, provinsi: f.properties.level_3 };
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
        peta.removeLayer(lapisKabupaten); /* berhenti mencoba; tombol memutuskan */
        provinsi.setStyle(this.gaya.bind(this));
      },

      matikanCadangan: function () {
        this.cadangan = false;
        this.kabar = false;
        provinsi.setStyle(this.gaya.bind(this));
        lapisKabupaten.addTo(peta);
        lapisKabupaten.redraw();
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

        peta.setMinZoom(peta.getZoom());
        peta.setMaxZoom(peta.getZoom() + 3);
        peta.setMaxBounds(peta.getBounds().pad(0.08));
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
               memberi fokus, dan di sana penanda yang benar adalah outline
               kabupaten di titik yang ditekan. */
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
                this.isiPanel(nama, null, lapis.feature.geometry);
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
