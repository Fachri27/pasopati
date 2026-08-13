<!-- ===================== Layar 1 — Beranda =====================
         Section ini mandiri: seluruh gayanya menempel pada markup sebagai
         utility Tailwind, dan perilakunya ada di komponen Alpine "korsel"
         (js/beranda.js). Varian panggung: = kanvas 1920x1080 presisi desain;
         tanpa varian = tata letak mengalir untuk layar sempit/pendek.
         ============================================================== -->
    <section
      id="beranda"
      aria-label="Beranda"
      x-data="korsel(<?php echo \Illuminate\Support\Js::from($berita ?? [])->toHtml() ?>)"
      x-on:mouseenter="hentikanOtomatis()"
      x-on:mouseleave="mulaiOtomatis()"
      x-on:focusin="hentikanOtomatis()"
      x-on:keydown.window="papanTombol($event)"
      x-on:buka-laporan.window="bukaRincianId($event.detail.id)"
      class="sticky top-0 z-[1] flex min-h-[100svh] flex-col justify-center overflow-hidden
             px-[var(--pias)] pt-[calc(4rem+clamp(18px,5vw,56px))] pb-[clamp(20px,5vw,56px)]
             pendek:static pendek:z-auto pendek:min-h-0
             panggung:block panggung:h-screen panggung:min-h-0
             panggung:sticky panggung:top-0 panggung:z-[1] panggung:p-0"
    >
      <!-- Foto latar hero. Hero di-pin (sticky) di atas viewport, section 2
           menggulir naik menutupinya — pola "hero diam, section berikut masuk"
           simontini. Gambar di-oversize (180% tinggi, top -40%) supaya bisa
           digeser parallax mengikuti scrollY tanpa menyingkap tepi; geseran
           diatur parallax.js lewat data-pahlawan-parallax. -->
      <img
        src="<?php echo e(asset('assets/img/bg-karhutla.jpg')); ?>"
        alt=""
        aria-hidden="true"
        fetchpriority="high"
        class="absolute left-0 top-[-40%] h-[180%] w-full object-cover"
      />

      <div
        data-kanvas
        class="relative mx-auto w-full max-w-[940px]
               panggung:absolute panggung:top-1/2 panggung:left-1/2 panggung:mx-0
               panggung:mt-[-540px] panggung:ml-[-960px] panggung:h-[1080px]
               panggung:w-[1920px] panggung:max-w-none panggung:origin-center
               panggung:scale-[var(--skala,1)] panggung:overflow-hidden"
      >
        <!-- Judul bagian: penanda arah, hanya perlu saat isi ditumpuk. -->
        <div class="mb-[clamp(14px,4vw,22px)] grid gap-[2px] panggung:hidden">
          <p
            class="text-[length:var(--ukuran-eyebrow)] font-medium tracking-[0.14em] uppercase
                   text-[rgb(26_25_25/0.72)]"
          >
            Laporan lapangan
          </p>
          <h2
            class="text-[length:var(--ukuran-bagian)] leading-[1.15] font-bold
                   [text-shadow:0_1px_12px_rgb(255_255_255/0.6)]"
          >
            Berita terkini
          </h2>
        </div>

        <?php if(empty($berita)): ?>
          <!-- Belum ada Event/Kejadian di CMS. Alih-alih korsel kosong: "rak
               kosong" — tiga slot berukuran sama persis dengan kartu berita
               (memakai token --kartu-*), jadi tata letaknya sendiri sudah
               memperlihatkan apa yang akan muncul di sini. Gayanya ada di
               public/css/pantauan-kosong.css, bukan sebagai utility Tailwind:
               public/dist/style.css adalah hasil build tanpa sumber di repo,
               jadi kelas utility baru tidak akan ter-generate. -->
          <div role="status" class="pantauan-kosong">
            <div class="pantauan-kosong__jendela">
              <div class="pantauan-kosong__rak">
                <div aria-hidden="true" class="pantauan-kosong__slot pantauan-kosong__slot--sisi"></div>

                <article class="pantauan-kosong__slot pantauan-kosong__slot--utama">
                  <div class="pantauan-kosong__isi">
                    <p class="pantauan-kosong__judul">Belum ada laporan</p>
                    <p class="pantauan-kosong__catatan">
                      Laporan lapangan karhutla akan muncul di kartu ini begitu
                      yang pertama tercatat.
                    </p>

                    <?php if($bisaTambahKejadian ?? false): ?>
                      <a href="<?php echo e(route('events.create')); ?>" class="pantauan-kosong__aksi">
                        Tambah kejadian
                        <svg viewBox="0 0 20 16" aria-hidden="true" fill="none">
                          <path
                            d="M2 8h15M11 2l6 6-6 6"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                      </a>
                    <?php endif; ?>
                  </div>

                  <div aria-hidden="true" class="pantauan-kosong__bingkai">
                    <div class="pantauan-kosong__sapuan"></div>
                  </div>

                  <p class="pantauan-kosong__rel">
                    <span>0 entri</span>
                    <span class="pantauan-kosong__stempel">Per <?php echo e($tanggalPantauan ?? ''); ?></span>
                  </p>
                </article>

                <div aria-hidden="true" class="pantauan-kosong__slot pantauan-kosong__slot--sisi"></div>
              </div>
            </div>
          </div>
        <?php else: ?>

        <section
          aria-roledescription="korsel"
          aria-label="Berita karhutla terkini"
          class="relative panggung:absolute panggung:inset-0"
        >
          <!-- Jendela: menyembunyikan kartu kembaran di luar kartu utama. -->
          <div
            x-on:touchstart.passive="mulaiSentuh($event)"
            x-on:touchend.passive="akhirSentuh($event)"
            class="relative w-full overflow-hidden pt-[10px] pb-[18px]
                   panggung:absolute panggung:top-[140px] panggung:left-[70px]
                   panggung:h-[680px] panggung:w-[1780px] panggung:p-0"
          >
            <div
              x-ref="jalur"
              x-on:transitionend="selesaiGeser($event)"
              :style="`transform: translateX(${geser}px)`"
              :class="diam && 'transition-none'"
              class="relative left-1/2 flex items-stretch gap-[var(--kartu-sela)]
                     transition-transform duration-[550ms] ease-[cubic-bezier(0.4,0,0.2,1)]
                     panggung:absolute panggung:top-[40px]"
            >
              <?php
                // Kelas media dipakai bersama oleh <img> dan <video> pada tiap
                // varian kartu: sebuah event boleh berisi foto ATAU video, dan
                // keduanya harus menempati kotak yang sama persis. Ditaruh di
                // variabel supaya keduanya tidak bisa berbeda diam-diam.
                $mediaVertikal = 'absolute inset-0 h-full w-full object-cover transition-[filter] duration-[550ms]';
                $mediaLanskap = 'mt-[var(--gambar-jarak)] aspect-[3/2] h-auto max-h-[var(--gambar-tinggi-maks)] w-[var(--gambar-lebar)] cursor-pointer self-center rounded-[10px] object-cover ring-1 ring-black/[0.08] shadow-[0_8px_20px_rgb(0_0_0/0.2)] transition-[filter] duration-[550ms] aliran:aspect-auto aliran:min-h-0 aliran:flex-1';
              ?>

              <template x-for="(k, i) in kartu" :key="k.kunci">
                <article
                  x-on:click="keKartu(i)"
                  :aria-hidden="i === aktif ? 'false' : 'true'"
                  :class="[
                    i === aktif
                      ? 'z-10 opacity-100 translate-y-[6px] scale-[1.0557]'
                      : 'opacity-45',
                    k.isi.vertikal
                      ? 'bg-[#f5f5f5] overflow-hidden shadow-[0_10px_30px_rgb(0_0_0/0.28)]'
                      : (i === aktif
                          ? 'bg-white/[0.88] shadow-[10px_12px_28px_rgb(0_0_0/0.32)]'
                          : 'bg-white/90 shadow-[6px_6px_14px_rgb(0_0_0/0.22)]')
                  ]"
                  class="relative flex h-[var(--kartu-tinggi)] w-[var(--kartu-lebar)] shrink-0
                         flex-col rounded-[12px] p-[var(--kartu-pias)] text-center
                         ring-1 ring-white/60 backdrop-blur-[7px]
                         transition-[transform,opacity,background-color]
                         duration-[550ms] ease-[cubic-bezier(0.4,0,0.2,1)] will-change-transform"
                >
                  <!-- ===== Varian vertikal: bingkai putih, foto memenuhi bingkai,
                       teks putih menumpang di atasnya. ===== -->
                  <template x-if="k.isi.vertikal">
                    <div class="absolute inset-[var(--kartu-pias)] overflow-hidden rounded-[8px]">
                      <div aria-hidden="true" class="absolute inset-0 bg-white"></div>
                      <template x-if="!k.isi.video">
                        <img
                          x-on:click.stop="bukaRincian(k.asli)"
                          :src="k.isi.gambar"
                          :alt="k.isi.alt || ''"
                          loading="eager"
                          decoding="async"
                          :class="i === aktif ? 'grayscale-0' : 'grayscale-[0.65]'"
                          class="<?php echo e($mediaVertikal); ?>"
                        />
                      </template>

                      <template x-if="k.isi.video">
                        <video
                          x-on:click.stop="bukaRincian(k.asli)"
                          :src="k.isi.video"
                          :poster="k.isi.gambar"
                          :aria-label="k.isi.alt || ''"
                          :controls="kurangiGerak"
                          x-effect="setelVideo($el, i)"
                          muted
                          loop
                          playsinline
                          preload="none"
                          :class="i === aktif ? 'grayscale-0' : 'grayscale-[0.65]'"
                          class="<?php echo e($mediaVertikal); ?>"
                        ></video>
                      </template>
                      <div aria-hidden="true" class="absolute inset-0 bg-[linear-gradient(to_bottom,rgb(0_0_0/0.32)_0%,transparent_34%,transparent_50%,rgb(0_0_0/0.62)_100%)]"></div>
                      <div class="absolute inset-0 flex flex-col justify-between p-[var(--kartu-pias)] text-left text-white">
                        <h3 class="text-[length:var(--ukuran-pulau)] leading-[1.2] font-bold" x-text="k.isi.pulau"></h3>
                        <div>
                          <p class="text-[length:var(--ukuran-tanggal)] leading-[1.2] font-normal" x-text="`• ${k.isi.tanggal} •`"></p>
                          <p
                            x-on:click.stop="bukaRincian(k.asli)"
                            class="mt-[var(--kartu-judul-jarak)] cursor-pointer text-[length:var(--ukuran-judul)] leading-[1.2] font-bold"
                            x-text="k.isi.judul"
                          ></p>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- ===== Varian bawaan: kartu kaca putih, teks gelap di atas,
                       foto lanskap 3:2 bawah dengan sudut membulat. ===== -->
                  <template x-if="!k.isi.vertikal">
                    <div class="contents">
                      <h3 class="text-left text-[length:var(--ukuran-pulau)] leading-[1.2] font-bold" x-text="k.isi.pulau"></h3>
                      <p class="mt-[12px] text-[length:var(--ukuran-tanggal)] leading-[1.2] font-normal" x-text="`• ${k.isi.tanggal} •`"></p>
                      <p
                        x-on:click.stop="bukaRincian(k.asli)"
                        class="mx-auto mt-[var(--kartu-judul-jarak)] max-w-[var(--kartu-judul-lebar)] cursor-pointer text-[length:var(--ukuran-judul)] leading-[1.2] font-bold"
                        x-text="k.isi.judul"
                      ></p>
                      <div aria-hidden="true" class="flex-1 aliran:hidden"></div>
                      <template x-if="!k.isi.video">
                        <img
                          x-on:click.stop="bukaRincian(k.asli)"
                          :src="k.isi.gambar"
                          :alt="k.isi.alt || ''"
                          loading="eager"
                          decoding="async"
                          width="462"
                          height="308"
                          :class="i === aktif ? 'grayscale-0' : 'grayscale-[0.65]'"
                          class="<?php echo e($mediaLanskap); ?>"
                        />
                      </template>

                      <template x-if="k.isi.video">
                        <video
                          x-on:click.stop="bukaRincian(k.asli)"
                          :src="k.isi.video"
                          :poster="k.isi.gambar"
                          :aria-label="k.isi.alt || ''"
                          :controls="kurangiGerak"
                          x-effect="setelVideo($el, i)"
                          muted
                          loop
                          playsinline
                          preload="none"
                          width="462"
                          height="308"
                          :class="i === aktif ? 'grayscale-0' : 'grayscale-[0.65]'"
                          class="<?php echo e($mediaLanskap); ?>"
                        ></video>
                      </template>
                    </div>
                  </template>
                </article>
              </template>
            </div>
          </div>

          <?php if(count($berita ?? []) > 1): ?>
          <button
            type="button"
            aria-label="Berita sebelumnya"
            x-on:click="tombol(-1)"
            class="absolute top-1/2 left-0 z-20 grid size-[var(--panah-ukuran)] -translate-y-1/2
                   place-items-center rounded-full bg-black/30 text-white backdrop-blur-[6px]
                   transition-colors hover:bg-black/55
                   panggung:top-[468px] panggung:left-[9px] panggung:bg-transparent
                   panggung:backdrop-blur-none panggung:drop-shadow-[0_2px_6px_rgb(0_0_0/0.45)]
                   panggung:hover:bg-transparent panggung:hover:opacity-75"
          >
            <svg
              viewBox="0 0 27 45"
              aria-hidden="true"
              fill="none"
              class="h-[var(--panah-ikon)] w-auto"
            >
              <path
                d="M13.5 3 3.5 22.5 13.5 42M25 3 15 22.5 25 42"
                stroke="currentColor"
                stroke-width="5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>

          <button
            type="button"
            aria-label="Berita berikutnya"
            x-on:click="tombol(1)"
            class="absolute top-1/2 right-0 z-20 grid size-[var(--panah-ukuran)] -translate-y-1/2
                   place-items-center rounded-full bg-black/30 text-white backdrop-blur-[6px]
                   transition-colors hover:bg-black/55
                   panggung:top-[468px] panggung:right-[9px] panggung:bg-transparent
                   panggung:backdrop-blur-none panggung:drop-shadow-[0_2px_6px_rgb(0_0_0/0.45)]
                   panggung:hover:bg-transparent panggung:hover:opacity-75"
          >
            <svg
              viewBox="0 0 27 45"
              aria-hidden="true"
              fill="none"
              class="h-[var(--panah-ikon)] w-auto"
            >
              <path
                d="M13.5 3 23.5 22.5 13.5 42M2 3 12 22.5 2 42"
                stroke="currentColor"
                stroke-width="5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>
          <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- Statistik menempel pada layar yang sama dengan korsel di kedua mode.
             Panggung: lima kartu 307px dengan sisa 95px di kedua tepi (y=836),
             sama dengan desain. Aliran: satu jalur geser mendatar berkeping
             ringkas di bawah korsel — kartu berikutnya sengaja terpotong di tepi
             kanan sebagai tanda masih ada yang bisa digeser. -->
        <section
          aria-label="Statistik karhutla"
          x-data="statistik()"
          class="relative mt-[clamp(14px,3.6vw,20px)]
                 panggung:absolute panggung:top-[836px] panggung:right-[95px]
                 panggung:left-[95px] panggung:mt-0"
        >
          <p
            class="mb-[6px] text-[length:var(--ukuran-eyebrow)] font-medium tracking-[0.14em]
                   uppercase text-[rgb(26_25_25/0.72)]
                   [text-shadow:0_1px_10px_rgb(255_255_255/0.75)] panggung:hidden"
          >
            Angka hari ini
          </p>

          <div
            x-ref="jalur"
            x-on:scroll.passive="tandaiGulir()"
            class="tanpa-bilah-gulir flex snap-x snap-mandatory gap-[var(--sela)] overflow-x-auto
                   panggung:justify-between panggung:gap-0 panggung:overflow-visible"
          >
            <template x-for="item in daftar" :key="item.label">
              <article
                class="w-[var(--statistik-lebar)] shrink-0 snap-start bg-white
                       p-[var(--statistik-pias)] text-left
                       panggung:h-[208px] panggung:w-[307px]"
              >
                <p
                  class="text-[length:var(--ukuran-tanggal)] leading-[1.2] font-normal whitespace-nowrap"
                  x-text="`• ${item.tanggal} •`"
                ></p>
                <h3
                  class="mt-[var(--statistik-jarak-label)] text-[length:var(--ukuran-label)]
                         leading-[1.2] font-bold"
                  x-text="item.label"
                ></h3>
                <p
                  x-show="item.nilai"
                  class="mt-2 text-[length:var(--ukuran-nilai)] leading-[1.1] font-bold
                         text-[var(--color-api)]"
                  x-text="item.nilai"
                ></p>
                <p
                  x-show="item.keterangan"
                  class="mt-1 text-[length:var(--ukuran-catatan)] leading-[1.3] font-normal
                         text-[rgb(26_25_25/0.6)]"
                  x-text="item.keterangan"
                ></p>
              </article>
            </template>
          </div>
        </section>
      </div>

      <!-- ===== Pop-up rincian laporan =====
           Terbuka saat judul atau gambar kartu diklik. Markupnya tinggal di
           dalam <section> ini supaya ikut satu blok dan mewarisi lingkup
           komponen "korsel", tetapi x-teleport memindahkannya ke <body> saat
           berjalan: [data-kanvas] memakai transform (panggung:scale), dan
           elemen position:fixed di dalam elemen ber-transform ikut terpotong
           mengikuti kanvas, bukan viewport.

           x-if, bukan x-show — alasannya sama dengan dialog peta: pada versi
           Alpine yang dipakai di sini x-show tidak selalu menerapkan kembali
           display:none pada elemen yang subtree-nya ikut berubah.

           Gayanya di public/css/rincian-laporan.css. -->
      <template x-teleport="body">
        <template x-if="sorot !== null">
          <div
            class="rincian"
            x-on:click.self="tutupRincian()"
          >
            <div
              role="dialog"
              aria-modal="true"
              aria-label="Rincian laporan karhutla"
              tabindex="-1"
              x-init="$el.focus()"
              class="rincian__panel"
            >
              <div class="rincian__media">
                <template x-if="!berita[sorot].video">
                  <img :src="berita[sorot].gambar" :alt="berita[sorot].alt || ''" />
                </template>

                <template x-if="berita[sorot].video">
                  <video
                    :src="berita[sorot].video"
                    :poster="berita[sorot].gambar"
                    :aria-label="berita[sorot].alt || ''"
                    :autoplay="!kurangiGerak"
                    controls
                    loop
                    playsinline
                  ></video>
                </template>
              </div>

              <!-- Satu instans komentarLaporan() untuk seluruh rel: daftar
                   komentar dan kolom kirim ada di dua ujung kolom yang berbeda,
                   dan keduanya harus berbagi keadaan yang sama supaya komentar
                   yang baru dikirim langsung muncul di daftarnya. -->
              <div
                class="rincian__rel"
                x-data="komentarLaporan()"
                x-effect="setel(berita[sorot].id)"
              >
                <!-- Kepala: keping bundar + nama pulau + tanggal, sejajar
                     dengan baris profil pada rujukan. -->
                <div class="rincian__kepala">
                  <img class="rincian__keping" :src="berita[sorot].gambar" alt="" aria-hidden="true" />
                  <div class="rincian__kapsi-isi">
                    <p class="rincian__pulau" x-text="berita[sorot].pulau || 'Indonesia'"></p>
                    <p class="rincian__tanggal" x-text="berita[sorot].tanggal"></p>
                  </div>
                </div>

                
                <div class="rincian__badan <?php if(auth()->guard()->guest()): ?> rincian__badan--ringkas <?php endif; ?>">
                  <!-- Kapsi: keping + nama pulau tebal disambung judul, sama
                       seperti baris keterangan pada rujukan. -->
                  <div class="rincian__kapsi">
                    <img class="rincian__keping" :src="berita[sorot].gambar" alt="" aria-hidden="true" />
                    <div class="rincian__kapsi-isi">
                      <p class="rincian__judul">
                        <strong x-text="berita[sorot].pulau || 'Indonesia'"></strong>
                        <span x-text="' ' + berita[sorot].judul"></span>
                      </p>

                      <div class="rincian__data">
                        <div>
                          <p class="rincian__label">Lokasi</p>
                          <p class="rincian__nilai" x-text="berita[sorot].lokasi || '—'"></p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Kolom komentar. Komponen Alpine tersendiri: ia hanya
                       diberi id laporan lewat x-effect, lalu mengurus
                       pengambilan/pengiriman ke endpoint JSON-nya sendiri. -->
                  <section aria-label="Komentar laporan">
                    <p class="rincian__kosong" x-show="!memuat && !daftar.length">
                      Belum ada komentar. Jadi yang pertama.
                    </p>

                    <ul class="rincian__utas">
                      <template x-for="k in daftar" :key="k.id">
                        <li class="rincian__komen">
                          <template x-if="k.avatar">
                            <img class="rincian__keping" :src="k.avatar" alt="" aria-hidden="true" />
                          </template>
                          <template x-if="!k.avatar">
                            <span class="rincian__inisial" aria-hidden="true" x-text="(k.nama || '?').charAt(0)"></span>
                          </template>

                          <div class="rincian__komen-isi">
                            <p class="rincian__komen-teks">
                              <span class="rincian__komen-nama" x-text="k.nama"></span>
                              <span x-text="k.isi"></span>
                            </p>

                            <p class="rincian__komen-kaki">
                              <span x-text="k.waktu"></span>
                              <button type="button" class="rincian__balas" x-on:click="mulaiBalas(k)">Balas</button>
                            </p>

                            <template x-if="k.balasan.length">
                              <div>
                                <!-- Garis pendek sebelum labelnya menandai
                                     cabang yang sedang dilipat, seperti pada
                                     rujukan. -->
                                <button
                                  type="button"
                                  class="rincian__lihat"
                                  :aria-expanded="tampilkanBalasan(k.id) ? 'true' : 'false'"
                                  x-on:click="alihkanBalasan(k.id)"
                                >
                                  <span class="rincian__lihat-garis" aria-hidden="true"></span>
                                  <span x-text="tampilkanBalasan(k.id)
                                    ? 'Sembunyikan balasan'
                                    : `Lihat balasan (${k.balasan.length})`"></span>
                                </button>

                                <ul
                                  class="rincian__balasan"
                                  :class="!tampilkanBalasan(k.id) && 'rincian__balasan--tutup'"
                                >
                                  <template x-for="b in k.balasan" :key="b.id">
                                    <li class="rincian__komen">
                                      <template x-if="b.avatar">
                                        <img class="rincian__keping" :src="b.avatar" alt="" aria-hidden="true" />
                                      </template>
                                      <template x-if="!b.avatar">
                                        <span class="rincian__inisial" aria-hidden="true" x-text="(b.nama || '?').charAt(0)"></span>
                                      </template>

                                      <div class="rincian__komen-isi">
                                        <p class="rincian__komen-teks">
                                          <span class="rincian__komen-nama" x-text="b.nama"></span>
                                          <template x-if="sebutanDari(b)">
                                            <span class="rincian__sebutan" x-text="sebutanDari(b)"></span>
                                          </template>
                                          <span x-text="isiTanpaSebutan(b)"></span>
                                        </p>

                                        <p class="rincian__komen-kaki">
                                          <span x-text="b.waktu"></span>
                                          <button type="button" class="rincian__balas" x-on:click="mulaiBalas(b)">Balas</button>
                                        </p>
                                      </div>
                                    </li>
                                  </template>
                                </ul>
                              </div>
                            </template>
                          </div>
                        </li>
                      </template>
                    </ul>

                    <p class="rincian__galat" x-show="galat" x-text="galat"></p>
                  </section>
                </div>

                <!-- Kolom kirim dipatok di dasar rel, seperti rujukan.
                     Berkomentar wajib masuk lewat Google: nama dan foto ikut
                     akun, jadi tak ada isian nama yang bisa dipalsukan.
                     Endpoint-nya juga menolak tamu, bukan cuma markup ini. -->
                <?php if(auth()->guard()->check()): ?>
                  <?php
                    $penggunaKomentar = auth()->user();
                    $avatarKomentar = $penggunaKomentar->image
                      ? (Illuminate\Support\Str::startsWith($penggunaKomentar->image, ['http://', 'https://'])
                          ? $penggunaKomentar->image
                          : asset('storage/'.$penggunaKomentar->image))
                      : null;
                  ?>

                  <form class="rincian__kirim" x-on:submit.prevent="kirim()">
                    <div class="rincian__jebakan" aria-hidden="true">
                      <label>Website<input type="text" tabindex="-1" autocomplete="off" x-model="website" /></label>
                    </div>

                    <p class="rincian__membalas" x-show="balasKe" x-cloak>
                      <span>Membalas <strong x-text="balasNama"></strong></span>
                      <button type="button" class="rincian__batal" x-on:click="batalBalas()">Batal</button>
                    </p>

                    
                    <?php if(! empty(config('services.turnstile.site_key'))): ?>
                      <div
                        class="rincian__captcha"
                        data-site-key="<?php echo e(config('services.turnstile.site_key')); ?>"
                        x-init="pasangCaptcha($el)"
                      ></div>
                    <?php endif; ?>

                    <div class="rincian__baris">
                      <?php if($avatarKomentar): ?>
                        <img class="rincian__keping rincian__keping--kecil" src="<?php echo e($avatarKomentar); ?>" alt="" aria-hidden="true" />
                      <?php else: ?>
                        <span class="rincian__inisial rincian__inisial--kecil" aria-hidden="true"><?php echo e(mb_substr($penggunaKomentar->name, 0, 1)); ?></span>
                      <?php endif; ?>

                      <label class="rincian__ketik-bungkus">
                        <span class="sr-only">Komentar sebagai <?php echo e($penggunaKomentar->name); ?></span>
                        <textarea
                          class="rincian__ketik"
                          x-model="isi"
                          rows="1"
                          maxlength="2000"
                          placeholder="Tambahkan komentar…"
                          x-on:keydown.enter.prevent="kirim()"
                        ></textarea>
                      </label>

                      <button
                        type="submit"
                        class="rincian__tombol-kirim"
                        :disabled="mengirim || !isi.trim()"
                        x-text="mengirim ? 'Mengirim…' : 'Kirim'"
                      ></button>
                    </div>
                  </form>
                <?php else: ?>
                  <div class="rincian__masuk">
                    <p class="rincian__masuk-teks">Masuk dulu untuk ikut berkomentar.</p>

                    <a
                      class="rincian__masuk-tombol"
                      :href="tautanMasuk(<?php echo \Illuminate\Support\Js::from(route('comment.google.login'))->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(request()->getRequestUri())->toHtml() ?>)"
                    >
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.5-.2-2.2H12v4.3h5.4a4.6 4.6 0 0 1-2 3v2.8h3.5c2-1.9 3.2-4.6 3.2-7.9z" />
                        <path fill="#34A853" d="M12 22c2.9 0 5.3-.9 7-2.6l-3.5-2.8a6.4 6.4 0 0 1-9.6-3.4H2.3V16A10 10 0 0 0 12 22z" />
                        <path fill="#FBBC05" d="M5.9 13.2A6 6 0 0 1 5.6 12c0-.4.1-.8.2-1.2V8H2.3A10 10 0 0 0 2 12c0 1.4.3 2.8.8 4z" />
                        <path fill="#EA4335" d="M12 5.6c1.6 0 3 .5 4.1 1.6l3.1-3A10 10 0 0 0 2.3 8l3.6 2.8A6 6 0 0 1 12 5.6z" />
                      </svg>
                      Masuk dengan Google
                    </a>
                  </div>
                <?php endif; ?>
              </div>

              <button
                type="button"
                aria-label="Tutup rincian"
                x-on:click="tutupRincian()"
                class="rincian__tutup"
              >
                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M6 6 18 18M6 18 18 6" />
                </svg>
              </button>
            </div>
          </div>
        </template>
      </template>
    </section>

    <?php /**PATH /Users/aiti/pasopati/resources/views/pasopati/beranda.blade.php ENDPATH**/ ?>