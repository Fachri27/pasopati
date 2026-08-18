/**
 * Section 1 — korsel berita, sebagai komponen Alpine.
 *
 * Markup-nya ada di index.html (utility Tailwind + x-for), bukan dibangun di
 * sini: itulah yang membuat section ini bisa dipindahkan apa adanya ke sebuah
 * Blade view. Yang tinggal di JS hanya keadaan dan gerakannya.
 *
 * Kartu digandakan tiga set agar pergeseran terasa tak berujung: kartu yang
 * keluar di sisi kiri selalu punya kembaran yang siap masuk dari sisi kanan.
 * Setelah animasi selesai, indeks dikembalikan diam-diam ke set tengah.
 *
 * Di Laravel: `berita` bisa datang dari Blade — x-data="korsel(@js($berita))".
 */
document.addEventListener("alpine:init", function () {
  Alpine.data("korsel", function (beritaAwal, urlDasar, slugDiminta) {
    return {
      /* --- keadaan --- */
      berita: beritaAwal || window.BERITA || [],
      /* URL dasar halaman fire (/{locale}/fire, sudah memuat host, tanpa slug
         maupun query) — dipakai urlBagikan() menyusun permalink dan
         perbaruiUrlRincian() pushState. */
      urlDasar: urlDasar || window.location.origin + window.location.pathname,
      /* Slug event yang diminta server lewat permalink path /{locale}/fire/<slug>
         (route fire.event). Dipakai pulihkanDariMasuk membuka pop-up event itu
         saat halaman dimuat. */
      slugDiminta: slugDiminta || null,
      SALINAN: 3,
      JEDA_OTOMATIS: 6000, /* ms; 0 untuk mematikan putar otomatis */
      DURASI: 550, /* selaras dengan durasi transisi pada jalur */

      aktif: 0,
      geser: 0,
      diam: true, /* true = transisi dimatikan (penempatan tanpa animasi) */
      kunci: false,
      pewaktu: null,
      pengaman: null,
      terlihat: true,
      lebarKartu: 0,
      langkah: 0,
      kurangiGerak: false,

      /* Video kartu, dikunci per kartu (k.kunci — unik lintas tiga salinan).
         Tidak bisa ditaruh di dalam `kartu` itu sendiri: getter itu membuat
         objek baru setiap kali dibaca, jadi apa pun yang disimpan di sana
         hilang pada pembacaan berikutnya. */
      durasiVideo: {}, /* kunci -> "0:42"; sisa waktu saat berjalan */
      videoUsai: {}, /* kunci -> true saat video habis dan belum diulang */

      /* Tiga set kartu berturut-turut. `asli` menandai indeks berita aslinya
         supaya kembaran tidak dibacakan dua kali oleh pembaca layar. */
      get kartu() {
        var semua = [];
        for (var s = 0; s < this.SALINAN; s++) {
          for (var i = 0; i < this.berita.length; i++) {
            semua.push({ kunci: s + "-" + i, salinan: s, asli: i, isi: this.berita[i] });
          }
        }
        return semua;
      },

      init: function () {
        if (!this.berita.length) return;
        this.kurangiGerak = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        this.aktif = this.berita.length + 1; /* set tengah, kartu ke-2 seperti desain */

        /* x-for baru merender setelah init, jadi pengukuran menunggu satu tick. */
        this.$nextTick(
          function () {
            this.ukur();
            this.terapkan(false);
            this.mulaiOtomatis();
            this.pulihkanDariMasuk();
          }.bind(this)
        );

        /* Tombol back/forward browser: sinkronkan pop-up ke URL — permalink
           path membuka rincian event itu, base /fire menutupnya. */
        window.addEventListener("popstate", this.saatPopState.bind(this));

        /* Ukur ulang saat ukuran (atau mode) berubah — lebar kartu berbeda per
           mode. setTimeout, bukan rAF: ini bukan pekerjaan animasi, dan rAF
           tidak dipicu di dokumen tersembunyi. */
        var tunda = null;
        this.$watch; /* tanpa efek; penanda bahwa keadaan lain tidak diamati */
        window.addEventListener(
          "resize",
          function () {
            if (tunda) window.clearTimeout(tunda);
            tunda = window.setTimeout(
              function () {
                tunda = null;
                this.ukur();
                this.terapkan(false);
              }.bind(this),
              60
            );
          }.bind(this)
        );

        /* Korsel hanya berjalan selama section ini tampak di viewport. */
        if ("IntersectionObserver" in window) {
          var pengamat = new IntersectionObserver(
            function (entri) {
              this.terlihat = entri[0].isIntersecting;
              if (this.terlihat) this.mulaiOtomatis();
              else this.hentikanOtomatis();
            }.bind(this),
            { threshold: 0.4 }
          );
          pengamat.observe(this.$el);
        }

        document.addEventListener(
          "visibilitychange",
          function () {
            if (document.hidden) this.hentikanOtomatis();
            else if (this.terlihat) this.mulaiOtomatis();
          }.bind(this)
        );
      },

      /* --- pengukuran --- */

      /* Lebar kartu dan jaraknya berbeda di tiap mode (token --kartu-lebar /
         --kartu-sela), jadi keduanya diukur dari DOM, bukan ditulis tetap. */
      ukur: function () {
        var jalur = this.$refs.jalur;
        /* Harus <article>, bukan firstElementChild: elemen pertama di jalur ini
           adalah <template> milik x-for, dan lebarnya nol. */
        var pertama = jalur && jalur.querySelector("article");
        if (!pertama) return;
        var gaya = window.getComputedStyle(jalur);
        var sela = parseFloat(gaya.columnGap || gaya.gap) || 0;
        /* offsetWidth: lebar tata letak — tidak terpengaruh scale kartu aktif
           maupun scale kanvas. */
        this.lebarKartu = pertama.offsetWidth;
        this.langkah = this.lebarKartu + sela;
      },

      terapkan: function (beranimasi) {
        this.diam = !beranimasi || this.kurangiGerak;
        this.geser = -(this.aktif * this.langkah + this.lebarKartu / 2);
      },

      /* --- pergeseran --- */

      normalkan: function () {
        if (this.pengaman) {
          window.clearTimeout(this.pengaman);
          this.pengaman = null;
        }
        var jumlah = this.berita.length;
        /* Kembalikan indeks ke set tengah tanpa animasi supaya geser tak habis. */
        if (this.aktif < jumlah || this.aktif >= jumlah * 2) {
          this.aktif = (this.aktif % jumlah) + jumlah;
          this.terapkan(false);
        }
        this.kunci = false;
      },

      pindah: function (arah) {
        /* Tanpa berita, markup korsel tidak dirender sama sekali (Blade
           menampilkan rak kosong): tombol panah papan ketik masih terpasang di
           window, jadi jaga di sini supaya tidak menghitung modulo nol. */
        if (!this.berita.length) return;
        if (this.kunci) return;
        this.kunci = true;
        this.aktif += arah;
        this.terapkan(true);
        if (this.kurangiGerak) {
          this.normalkan();
          return;
        }
        /* Jaring pengaman: bila transitionend tidak pernah datang (transisi
           dimatikan, tab di latar, animasi terpotong), kunci tetap dilepas. */
        this.pengaman = window.setTimeout(this.normalkan.bind(this), this.DURASI + 150);
      },

      selesaiGeser: function (e) {
        if (e.propertyName === "transform" && e.target === this.$refs.jalur) this.normalkan();
      },

      /* --- pop-up rincian ---
         `sorot` = indeks berita yang sedang dibuka, atau null. Indeks 0 itu
         sah, jadi seluruh pemeriksaan memakai `=== null`, bukan kebenaran
         nilai. */
      sorot: null,

      /* `tenang` = true menyingkirkan pushState: dipakai saat membuka pop-up
         sebagai reaksi terhadap URL yang sudah benar (permalink saat halaman
         dimuat, atau popstate dari tombol back/forward) — supaya tidak menambah
         entri riwayat dan memicu loop. */
      bukaRincian: function (indeks, tenang) {
        var jumlah = this.berita.length;
        if (!jumlah) return;

        /* Kartu digandakan tiga set, jadi indeks yang datang dari x-for perlu
           dikembalikan ke indeks berita aslinya. */
        this.sorot = ((indeks % jumlah) + jumlah) % jumlah;
        this.setelUlangVideoRincian();
        this.hentikanOtomatis();
        this.kunciGulir(true);
        if (!tenang) this.perbaruiUrlRincian();
      },

      tutupRincian: function (tenang) {
        if (this.sorot === null) return;
        this.sorot = null;
        this.kunciGulir(false);
        if (this.terlihat) this.mulaiOtomatis();
        if (!tenang) this.perbaruiUrlRincian();
      },

      pindahRincian: function (arah) {
        if (this.sorot === null) return;
        var jumlah = this.berita.length;
        this.sorot = ((this.sorot + arah) % jumlah + jumlah) % jumlah;
        this.setelUlangVideoRincian();
        this.perbaruiUrlRincian();
      },

      /* Satu elemen <video> di pop-up dipakai bergantian oleh semua laporan:
         yang berganti hanya src-nya, elemennya tetap. Jadi lencana durasi dan
         tanda "sudah habis" milik laporan sebelumnya harus dilepas sendiri —
         kalau tidak, laporan berikutnya terbuka dengan durasi yang salah dan
         tombol putar ulang menempel di video yang justru baru mulai. */
      setelUlangVideoRincian: function () {
        this.videoUsai.rincian = false;
        this.durasiVideo.rincian = "";
      },

      /* PUSHSTATE: alamat bar mengikuti pop-up yang terbuka — pola Instagram.
         Sorot null → base /{locale}/fire; sorot terisi → permalink /fire/<slug>. */
      perbaruiUrlRincian: function () {
        var url;
        if (this.sorot === null) {
          url = this.urlDasar;
        } else {
          var b = this.berita[this.sorot];
          if (!b || !b.slug) return;
          url = this.urlDasar + "/" + b.slug;
        }
        window.history.pushState({}, "", url);
      },

      /* POPSTATE: reaksi terhadap back/forward — cocokkan URL ke pop-up tanpa
         pushState ulang (pergerakan tombol itu sendiri yang mengubah URL). */
      saatPopState: function () {
        var bagian = window.location.pathname.split("/");
        var slug = bagian[bagian.length - 1] || "";
        for (var i = 0; i < this.berita.length; i++) {
          if (slug && String(this.berita[i].slug || "") === String(slug)) {
            this.bukaRincian(i, true);

            return;
          }
        }
        this.tutupRincian(true);
      },

      /* Tautan "Masuk dengan Google" untuk laporan yang sedang dibuka.
         Tujuan kembali = permalink event yang sedang dibuka (atau base /fire
         bila tidak ada) — sepulang login, mendarat di permalink itu → show()
         merender dan pop-up terbuka lagi di event yang sama.

         Intended dipakai sebagai PATH RELATIF (mis. /id/fire/<slug>), bukan URL
         absolut, supaya selalu dianggap tujuan internal oleh intendedAman() di
         controller dan tidak bergantung pada skema/host produksi. */
      tautanMasuk: function (rute) {
        var dasar = this.urlDasar;
        var m = /^https?:\/\/[^\/]+(\/.*)$/.exec(dasar);
        var path = m ? m[1] : dasar;
        var kembali = this.sorot !== null ? path + "/" + this.berita[this.sorot].slug : path;

        return rute + "?intended=" + encodeURIComponent(kembali);
      },

      /* Dipanggil sekali saat halaman dimuat. Tiga sumber pop-up awal:
         (1) permalink path /{locale}/fire/<slug> → slugDiminta dari server;
         (2) link share lama ?event=<slug> → di-upgrade ke permalink path;
         (3) login lama ?laporan=<id> → penanda dihapus. Semua dibuka tenang
         (URL sudah benar / sedang diperbaiki) supaya tidak menambah riwayat. */
      pulihkanDariMasuk: function () {
        var params = new URLSearchParams(window.location.search);
        var slug = params.get("event");
        var id = params.get("laporan");

        if (slug) {
          /* Upgrade link share lama ?event=<slug> → permalink path /fire/<slug>
             supaya address bar mencerminkan URL baru dan reload mendarat di
             show() alih-alih index(). */
          params.delete("event");
          var sisa = params.toString();
          window.history.replaceState(
            {},
            "",
            this.urlDasar + "/" + slug + (sisa ? "?" + sisa : "") + window.location.hash
          );
        } else if (id) {
          params.delete("laporan");
          var sisa2 = params.toString();
          window.history.replaceState(
            {},
            "",
            window.location.pathname + (sisa2 ? "?" + sisa2 : "") + window.location.hash
          );
        }

        /* Prioritas: query lama lebih dulu (baru saja di-upgrade/dihapus di
           atas), baru slugDiminta dari permalink path. */
        var slugBuka = slug || (this.slugDiminta || null);
        if (!slugBuka && !id) return;

        this.$nextTick(
          function () {
            if (slugBuka) this.bukaRincianSlug(slugBuka, true);
            else this.bukaRincianId(id, true);
          }.bind(this)
        );
      },

      bukaRincianSlug: function (slug, tenang) {
        for (var i = 0; i < this.berita.length; i++) {
          if (String(this.berita[i].slug || "") === String(slug)) {
            this.bukaRincian(i, tenang);

            return;
          }
        }
      },

      bukaRincianId: function (id, tenang) {
        for (var i = 0; i < this.berita.length; i++) {
          if (String(this.berita[i].id) === String(id)) {
            this.bukaRincian(i, tenang);

            return;
          }
        }
      },

      /* --- bagikan rincian yang terbuka ---
         Tautan share = permalink event yang sedang dibuka: /{locale}/fire/<slug>.
         Slug terbaca sebagai judul kejadian, alih-alih id numerik. Dibuka oleh
         pendatang, route show() merender halaman dengan pop-up event itu
         terbuka otomatis (lihat slugDiminta & pulihkanDariMasuk di atas).
         Memakai Web Share API bila tersedia (mobile → share sheet OS); kalau
         tidak, salin ke clipboard + toast. */
      tersalin: false,
      _jedaTersalin: null,

      urlBagikan: function () {
        if (this.sorot === null) return "";
        var b = this.berita[this.sorot];
        if (!b || !b.slug) return "";
        return this.urlDasar + "/" + encodeURIComponent(b.slug);
      },

      bagikan: function () {
        if (this.sorot === null) return;
        var url = this.urlBagikan();
        if (!url) return;
        var judul = (this.berita[this.sorot] && this.berita[this.sorot].judul) || "Berita karhutla — Pasopati";
        var self = this;

        if (navigator.share) {
          navigator.share({ title: judul, url: url }).catch(function () {});
          return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard
            .writeText(url)
            .then(function () {
              self.tampilkanTersalin();
            })
            .catch(function () {});
          return;
        }

        /* Fallback terakhir (browser tanpa Clipboard API): input sementara +
           execCommand. */
        var input = document.createElement("input");
        input.value = url;
        input.setAttribute("readonly", "");
        input.style.position = "absolute";
        input.style.left = "-9999px";
        document.body.appendChild(input);
        input.select();
        try {
          document.execCommand("copy");
          self.tampilkanTersalin();
        } catch (e) {}
        document.body.removeChild(input);
      },

      tampilkanTersalin: function () {
        var self = this;
        this.tersalin = true;
        if (this._jedaTersalin) window.clearTimeout(this._jedaTersalin);
        this._jedaTersalin = window.setTimeout(function () {
          self.tersalin = false;
        }, 2200);
      },

      /* Halaman di belakang pop-up tidak ikut tergulir — sama caranya dengan
         dialog peta (public/js/peta.js). Pop-up ini bisa terbuka DI ATAS dialog
         peta, jadi saat ditutup kuncinya hanya dilepas kalau dialog itu memang
         sudah tidak terbuka. */
      kunciGulir: function (kunci) {
        document.body.classList.toggle("rincian-terbuka", kunci);

        if (kunci) {
          document.body.style.overflow = "hidden";

          return;
        }

        document.body.style.overflow =
          document.body.classList.contains("peta-terbuka") ? "hidden" : "";
      },

      /* Video kartu — hanya kartu aktif yang berputar; sisanya berhenti dan
         kembali ke frame awal supaya sama dengan poster-nya. Kartu digandakan
         tiga set, jadi tanpa aturan ini ada tiga video berjalan sekaligus untuk
         satu berita yang sama.

         Dipanggil lewat x-effect, jadi cukup membaca `aktif`, `kurangiGerak`,
         dan `terlihat` di sini: Alpine yang menjalankan ulang saat salah
         satunya berubah. `preload="none"` di markup membuat berkasnya baru
         diunduh ketika kartunya benar-benar tampil. */
      setelVideo: function (el, indeks, kunci) {
        if (!el) return;

        /* Sebagian browser hanya mengizinkan putar-otomatis bila properti
           (bukan sekadar atribut) muted bernilai true. */
        el.muted = true;

        /* Saat pop-up terbuka, video di kartu berhenti: yang ditonton adalah
           salinan di dalam pop-up, bukan yang di belakangnya. */
        if (indeks === this.aktif && this.terlihat && !this.kurangiGerak && this.sorot === null) {
          /* Kartu ini kembali diputar dari awal, jadi tanda "putar ulang"
             dilepas — kalau tidak, ia menempel di video yang sedang berjalan. */
          this.videoUsai[kunci] = false;
          var janji = el.play();
          /* Putar-otomatis masih bisa ditolak (mis. mode hemat daya) — poster
             tetap tampil, jadi tidak ada yang perlu dilakukan. */
          if (janji && janji.catch) janji.catch(function () {});

          return;
        }

        el.pause();
        if (el.currentTime) el.currentTime = 0;
        this.videoUsai[kunci] = false;
      },

      /* --- lencana durasi & putar ulang ---
         Video kartu tidak diulang sendiri (tanpa atribut loop): ia berhenti di
         bingkai terakhir, lalu tombol putar ulang yang meneruskan. */

      jamVideo: function (detik) {
        if (!isFinite(detik) || detik < 0) return "";
        var menit = Math.floor(detik / 60);
        var sisa = Math.floor(detik % 60);
        return menit + ":" + (sisa < 10 ? "0" : "") + sisa;
      },

      /* Dipanggil saat metadata siap dan tiap kali waktunya berjalan. Selama
         video berjalan yang ditampilkan SISA waktunya (menghitung mundur);
         sebelum diputar dan sesudah habis, durasi penuhnya. */
      catatDurasi: function (el, kunci) {
        if (!el || !isFinite(el.duration) || el.duration <= 0) return;
        var sisa = el.paused || el.ended ? el.duration : el.duration - el.currentTime;
        this.durasiVideo[kunci] = this.jamVideo(Math.ceil(sisa));
      },

      usaiVideo: function (el, kunci) {
        this.videoUsai[kunci] = true;
        this.catatDurasi(el, kunci);
      },

      /* Tombol putar ulang. Elemen videonya dicari dari bingkai kartu yang sama,
         bukan lewat x-ref: ref di dalam x-for tidak unik antar kartu. */
      ulangVideo: function (tombol, kunci) {
        var bingkai = tombol && tombol.closest(".kartu-bingkai");
        var el = bingkai && bingkai.querySelector("video");
        if (!el) return;

        this.videoUsai[kunci] = false;
        el.currentTime = 0;
        var janji = el.play();
        if (janji && janji.catch) janji.catch(function () {});
      },

      /* Klik kartu samping menjadikannya aktif. */
      keKartu: function (indeks) {
        var selisih = indeks - this.aktif;
        if (selisih === 1 || selisih === -1) {
          this.pindah(selisih);
          this.tundaOtomatis();
        }
      },

      tombol: function (arah) {
        this.pindah(arah);
        this.tundaOtomatis();
      },

      papanTombol: function (e) {
        /* Saat pop-up terbuka, tombol panah memindahkan laporan di dalamnya —
           bukan menggeser korsel yang tertutup di belakangnya. */
        if (this.sorot !== null) {
          if (e.key === "Escape") {
            /* Pop-up ini bisa terbuka di atas dialog peta, dan dialog itu juga
               memasang pendengar Escape di window. Keduanya dijaga dua arah:
               di sini penyebaran dihentikan (komponen ini diinisialisasi lebih
               dulu karena #beranda mendahului peta di DOM), dan di peta.js ada
               penjagaan lewat kelas .rincian-terbuka kalau urutannya terbalik. */
            e.stopImmediatePropagation();
            this.tutupRincian();
          } else if (e.key === "ArrowLeft") this.pindahRincian(-1);
          else if (e.key === "ArrowRight") this.pindahRincian(1);

          return;
        }

        if (!this.terlihat) return; /* jangan bergeser saat pengguna di layar lain */
        if (e.key === "ArrowLeft") this.tombol(-1);
        else if (e.key === "ArrowRight") this.tombol(1);
      },

      /* --- sentuhan --- */

      sentuhX: null,
      sentuhY: null,

      mulaiSentuh: function (e) {
        if (e.touches.length !== 1) return;
        this.sentuhX = e.touches[0].clientX;
        this.sentuhY = e.touches[0].clientY;
      },

      akhirSentuh: function (e) {
        if (this.sentuhX === null) return;
        var akhir = e.changedTouches[0];
        var dx = akhir.clientX - this.sentuhX;
        var dy = akhir.clientY - this.sentuhY;
        this.sentuhX = null;
        this.sentuhY = null;
        /* Abaikan gerakan yang lebih condong vertikal: itu guliran halaman. */
        if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return;
        this.tombol(dx < 0 ? 1 : -1);
      },

      /* --- putar otomatis --- */

      mulaiOtomatis: function () {
        if (!this.berita.length) return; /* rak kosong: tak ada yang diputar */
        if (this.sorot !== null) return; /* pop-up terbuka: korsel diam */
        if (!this.JEDA_OTOMATIS || this.kurangiGerak) return;
        this.hentikanOtomatis();
        this.pewaktu = window.setInterval(
          function () {
            this.pindah(1);
          }.bind(this),
          this.JEDA_OTOMATIS
        );
      },

      hentikanOtomatis: function () {
        if (this.pewaktu) window.clearInterval(this.pewaktu);
        this.pewaktu = null;
      },

      tundaOtomatis: function () {
        if (this.pewaktu) this.mulaiOtomatis();
      },
    };
  });

  /**
   * Kolom komentar di dalam pop-up rincian laporan.
   *
   * Berdiri sendiri terhadap korsel: ia hanya diberi tahu id laporan mana yang
   * sedang dibuka (lewat setel(), dipanggil dari x-effect di markup), lalu
   * mengambil dan mengirim komentar ke endpoint JSON di
   * App\Http\Controllers\Fire\LaporanKomentarController.
   */
  Alpine.data("komentarLaporan", function () {
    return {
      idLaporan: null,
      daftar: [],
      memuat: false,
      mengirim: false,
      nama: "",
      email: "",
      isi: "",
      balasKe: null,      /* id komentar yang sedang dibalas */
      balasNama: "",      /* namanya, untuk label "Membalas …" */
      dibuka: [],         /* id akar yang balasannya sedang ditampilkan */
      website: "", /* umpan jebakan; hanya bot yang mengisinya */
      galat: "",
      captchaToken: "",
      captchaWidget: null,

      /* Dipanggil ulang tiap kali laporan yang dibuka berganti. Dijaga supaya
         tidak mengambil ulang data yang sama saat pengikatan lain ikut
         dievaluasi. */
      setel: function (id) {
        if (!id || id === this.idLaporan) return;
        this.idLaporan = id;
        this.daftar = [];
        this.galat = "";
        this.batalBalas();
        this.dibuka = [];
        /* Pulihkan nama & email dari localStorage supaya tidak perlu mengetik
           ulang setiap kali membuka popup. */
        try {
          this.nama = localStorage.getItem("komentar_nama") || "";
          this.email = localStorage.getItem("komentar_email") || "";
        } catch (_) { /* storage mungkin diblokir */ }
        this.ambil();
      },

      /* Akar dari sebuah komentar: id itu sendiri bila ia akar, atau id akar
         yang menaunginya bila ia balasan. */
      akarDari: function (id) {
        if (!id) return null;

        for (var i = 0; i < this.daftar.length; i++) {
          if (this.daftar[i].id === id) return this.daftar[i].id;

          var balasan = this.daftar[i].balasan || [];
          for (var j = 0; j < balasan.length; j++) {
            if (balasan[j].id === id) return this.daftar[i].id;
          }
        }

        return null;
      },

      alamat: function () {
        return "/fire/laporan/" + this.idLaporan + "/komentar";
      },

      ambil: function () {
        var idSaatIni = this.idLaporan;
        this.memuat = true;

        fetch(this.alamat(), { headers: { Accept: "application/json" } })
          .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
          .then(
            function (data) {
              /* Pengguna bisa sudah pindah laporan sebelum jawaban datang. */
              if (idSaatIni !== this.idLaporan) return;
              this.daftar = data.komentar || [];
              this.memuat = false;
            }.bind(this)
          )
          .catch(
            function () {
              if (idSaatIni !== this.idLaporan) return;
              this.memuat = false;
              this.galat = "Komentar gagal dimuat. Coba muat ulang halaman.";
            }.bind(this)
          );
      },

      /* --- captcha --- */

      /* Turnstile dipasang mode explicit, jadi widget-nya dirender sendiri di
         sini begitu skripnya siap. Site key dibaca dari atribut wadahnya —
         berkas ini tidak perlu tahu konfigurasi apa pun. */
      pasangCaptcha: function (wadah) {
        if (!wadah) return;

        if (!window.turnstile) {
          window.setTimeout(
            function () {
              this.pasangCaptcha(wadah);
            }.bind(this),
            100
          );

          return;
        }

        wadah.innerHTML = "";
        this.captchaWidget = window.turnstile.render(wadah, {
          sitekey: wadah.getAttribute("data-site-key"),

          /* Berjalan di balik layar: kotak captcha tidak ditampilkan sama
             sekali, dan baru muncul kalau Cloudflare memang menilai perlu ada
             interaksi manusia. Verifikasinya tetap jalan penuh — token tetap
             dikirim dan tetap diperiksa di server. */
          appearance: "interaction-only",
          callback: function (token) {
            this.captchaToken = token;
          }.bind(this),
          "expired-callback": function () {
            this.captchaToken = "";
          }.bind(this),
          "error-callback": function () {
            this.captchaToken = "";
          }.bind(this),
        });
      },

      /* Token Turnstile sekali pakai: setelah dikirim — berhasil atau gagal —
         widget-nya harus diminta membuat token baru. */
      ulangCaptcha: function () {
        this.captchaToken = "";

        if (window.turnstile && this.captchaWidget !== null) {
          try {
            window.turnstile.reset(this.captchaWidget);
          } catch (e) {
            /* widget sudah lepas bersama pop-up yang ditutup */
          }
        }
      },

      /* --- balasan --- */

      mulaiBalas: function (komentar) {
        this.balasKe = komentar.id;
        this.balasNama = komentar.nama;

        /* Kolom ketiknya ada di ujung lain rel, jadi fokusnya dipindah ke sana
           supaya tidak perlu dicari sendiri. */
        this.$nextTick(
          function () {
            var kolom = this.$root.querySelector(".rincian__ketik");
            if (!kolom) return;
            kolom.focus();
            kolom.setSelectionRange(kolom.value.length, kolom.value.length);
          }.bind(this)
        );
      },

      /* Sebutan diambil dari data (`sebutan`), bukan dari teks yang diketik.
         Versi sebelumnya hanya menampilkannya kalau isi komentar kebetulan
         diawali "@Nama" — jadi balasan yang diketik tanpa awalan itu tampil
         tanpa sebutan sama sekali. */
      sebutanDari: function (komentar) {
        return komentar.sebutan ? "@" + komentar.sebutan : null;
      },

      /* Kalau teksnya sendiri sudah diawali sebutan yang sama, awalan itu
         dipangkas supaya tidak tampil dua kali. */
      isiTanpaSebutan: function (komentar) {
        var awalan = this.sebutanDari(komentar);

        if (!awalan || komentar.isi.indexOf(awalan) !== 0) return komentar.isi;

        return komentar.isi.slice(awalan.length).replace(/^\s+/, "");
      },

      batalBalas: function () {
        this.balasKe = null;
        this.balasNama = "";
      },

      /* Balasan disembunyikan sampai diminta — sama seperti rujukannya, supaya
         daftar akar tetap terbaca sekilas. */
      tampilkanBalasan: function (akarId) {
        return this.dibuka.indexOf(akarId) !== -1;
      },

      alihkanBalasan: function (akarId) {
        var i = this.dibuka.indexOf(akarId);
        if (i === -1) this.dibuka.push(akarId);
        else this.dibuka.splice(i, 1);
      },

      kirim: function () {
        if (this.mengirim || !this.isi.trim()) return;
        this.mengirim = true;
        this.galat = "";

        var simpul = document.querySelector('meta[name="csrf-token"]');

        fetch(this.alamat(), {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": simpul ? simpul.getAttribute("content") : "",
          },
          body: JSON.stringify({
            nama: this.nama,
            email: this.email,
            isi: this.isi,
            balas_ke: this.balasKe,
            website: this.website,
            captcha_token: this.captchaToken,
          }),
        })
          .then(
            function (r) {
              return r.json().then(function (data) {
                return r.ok ? data : Promise.reject(data);
              });
            }
          )
          .then(
            function (data) {
              /* Balasan baru dibuka otomatis, kalau tidak kirimannya sendiri
                 tidak kelihatan karena utasnya masih tertutup. */
              var akar = this.akarDari(this.balasKe);
              this.daftar = data.komentar || [];
              this.isi = "";
              this.mengirim = false;
              this.batalBalas();
              this.ulangCaptcha();
              if (akar !== null && this.dibuka.indexOf(akar) === -1) this.dibuka.push(akar);
              try {
                localStorage.setItem("komentar_nama", this.nama);
                localStorage.setItem("komentar_email", this.email);
              } catch (_) { /* storage mungkin diblokir */ }
            }.bind(this)
          )
          .catch(
            function (data) {
              this.mengirim = false;
              this.ulangCaptcha();
              /* Pesan validasi dari Laravel dulu, lalu `message` (dipakai saat
                 sesi habis dan endpoint menolak dengan 401), baru pesan umum. */
              var pesan = null;
              if (data && data.errors) {
                pesan = Object.keys(data.errors).map(function (k) {
                  return data.errors[k][0];
                })[0];
              } else if (data && data.message) {
                pesan = data.message;
              }
              this.galat = pesan || "Komentar gagal dikirim. Coba lagi.";
            }.bind(this)
          );
      },
    };
  });

  /**
   * Kartu statistik — jalur geser mendatar (satu blok dengan korsel). Tombol
   * « » menggeser satu kartu dan hanya tampil bila sisi itu masih bisa
   * digulir; ketersediaannya dibaca ulang tiap guliran & ubah ukuran layar.
   */
  Alpine.data("statistik", function (daftarAwal) {
    return {
      daftar: daftarAwal || window.STATISTIK || [],
      bisaKiri: false,
      bisaKanan: false,
      kurangiGerak: false,

      init: function () {
        this.kurangiGerak = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        /* x-for baru merender setelah init, jadi pengukuran menunggu satu tick. */
        this.$nextTick(
          function () {
            this.tandaiGulir();
          }.bind(this)
        );
        var tunda = null;
        window.addEventListener(
          "resize",
          function () {
            if (tunda) window.clearTimeout(tunda);
            tunda = window.setTimeout(
              function () {
                tunda = null;
                this.tandaiGulir();
              }.bind(this),
              60
            );
          }.bind(this)
        );
      },

      /* Tandai sisi yang masih bisa digulir (ambang 4px untuk toleransi). */
      tandaiGulir: function () {
        var j = this.$refs.jalur;
        if (!j) return;
        this.bisaKiri = j.scrollLeft > 4;
        this.bisaKanan = j.scrollLeft < j.scrollWidth - j.clientWidth - 4;
      },

      /* Geser satu kartu ke kiri (-1) atau kanan (1); snap merapikan posisi. */
      geserStrip: function (arah) {
        var j = this.$refs.jalur;
        if (!j) return;
        var kartu = j.querySelector("article");
        var sela = parseFloat(window.getComputedStyle(j).columnGap) || 0;
        var langkah = kartu ? kartu.offsetWidth + sela : j.clientWidth;
        j.scrollBy({
          left: arah * langkah,
          behavior: this.kurangiGerak ? "auto" : "smooth",
        });
      },
    };
  });
});
