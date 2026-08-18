<!-- ===================== Layar 2 — Peta Sebaran =====================
         Sama seperti section 1: gaya menempel pada markup, perilaku ada di
         komponen Alpine "peta" (js/peta.js). Panel, daftar wilayah rawan, kabar
         kegagalan, dan tabel setara semuanya template di sini — bukan dibangun
         dengan createElement — supaya bisa dipindahkan ke Blade.
         ============================================================== -->
    <section
      id="peta"
      aria-label="Peta sebaran"
      x-data="peta(@js($berita ?? []))"
      x-on:keydown.window="tutupDenganEscape($event)"
      data-kabur-tepi
      class="tepi-lunak relative z-[2] flex min-h-[100svh] flex-col justify-center overflow-hidden
             px-[var(--pias)] pt-[calc(4rem+clamp(32px,8vw,56px))] pb-[clamp(32px,8vw,56px)]
             pendek:z-auto pendek:min-h-0
             panggung:block panggung:h-screen panggung:min-h-0
             panggung:relative panggung:z-[2] panggung:p-0"
    >
      <img
        src="{{ asset('assets/img/bg-karhutla.jpg') }}"
        alt=""
        aria-hidden="true"
        loading="lazy"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div class="kabur-tepi" aria-hidden="true"></div>
      <div aria-hidden="true" class="kabut-api absolute inset-0"></div>

      <div
        data-kanvas
        class="relative mx-auto w-full max-w-[940px]
               panggung:absolute panggung:top-1/2 panggung:left-1/2 panggung:mx-0
               panggung:mt-[-540px] panggung:ml-[-960px] panggung:h-[1080px]
               panggung:w-[1920px] panggung:max-w-none panggung:origin-center
               panggung:scale-[var(--skala,1)] panggung:overflow-hidden"
      >
        <!-- Kotak peta. isolate menahan z-index panel Leaflet (sampai 800) agar
             tidak naik ke atas navbar tetap yang ber-z-index 50. Warna "laut"
             wadahnya disetel di penimpaan .leaflet-container (src/input.css). -->
        <div
          class="relative isolate aspect-[2.5] w-full
                 panggung:absolute panggung:top-[280.4px] panggung:left-[193.1px]
                 panggung:aspect-auto panggung:h-[575.8px] panggung:w-[1533.8px]"
        >
          <div x-ref="kotak" class="h-full w-full"></div>

          <!-- Kabar saat layanan peta luar gagal. pointer-events dilepas agar
               peta di bawahnya tetap bisa ditekan; hanya tombolnya menangkap. -->
          <div role="status" class="contents">
          <template x-if="kabar">
          <div
            class="pointer-events-none absolute bottom-[10px] left-1/2 z-[900] flex
                   max-w-[min(560px,calc(100%-20px))] -translate-x-1/2 flex-wrap
                   items-center justify-center gap-x-3 gap-y-1.5 rounded-[10px]
                   bg-[rgb(26_25_25/0.82)] p-[10px_14px] text-center
                   text-[length:var(--ukuran-catatan)] leading-[1.4] text-white
                   backdrop-blur-[6px]"
          >
            <p>
              Warna per provinsi diambil dari layanan luar (aws.simontini.id) dan
              sekarang tidak terjangkau. Peta menampilkan data contoh.
            </p>
            <button
              type="button"
              x-on:click="matikanCadangan()"
              class="pointer-events-auto shrink-0 cursor-pointer rounded-full border
                     border-white/35 bg-white/10 px-3 py-[5px] font-semibold
                     transition-colors hover:bg-white/25"
            >
              Coba muat lagi
            </button>
          </div>
          </template>
          </div>
        </div>


        <!-- Pencarian + tiga provinsi teratas (mode aliran).

             Di lebar ponsel peta hanya sekitar 150px tinggi, jadi menekan satu
             provinsi di peta hampir tak mungkin — kotak cari ini jalan masuk
             yang sebenarnya, dan daftarnya memberi tiga yang paling penting
             tanpa perlu mencari.

             Angka di sini nyata: diambil dari layer yang sama dengan yang
             mewarnai peta (gaya layer-nya bernama "Choropleth Deforestasi per
             Provinsi"), diurutkan server berdasarkan luas deforestasi. -->
        <div class="mt-[clamp(18px,5vw,28px)] panggung:hidden">
          <label class="relative block">
            <span class="sr-only">Cari provinsi</span>
            <svg
              viewBox="0 0 24 24"
              aria-hidden="true"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              class="pointer-events-none absolute top-1/2 left-[14px] size-[18px]
                     -translate-y-1/2 text-[rgb(26_25_25/0.45)]"
            >
              <circle cx="11" cy="11" r="7" />
              <path d="m20 20-3.5-3.5" />
            </svg>
            <input
              type="search"
              placeholder="Cari provinsi…"
              autocomplete="off"
              x-model="cari"
              x-on:focus="muatIndeks()"
              x-on:input="muatIndeks(); saring()"
              x-on:keydown.escape.stop="cari = ''; hasil = []"
              class="w-full rounded-[10px] border border-[rgb(26_25_25/0.1)] bg-white/95
                     py-[11px] pr-[14px] pl-[40px] text-[length:var(--ukuran-catatan)]
                     text-[var(--color-tinta)] placeholder:text-[rgb(26_25_25/0.45)]
                     focus-visible:border-[var(--color-bara)] focus-visible:outline-none
                     [&::-webkit-search-cancel-button]:appearance-none"
            />
          </label>

          <!-- Hasil pencarian -->
          <template x-if="hasil.length">
            <ul
              class="mt-[6px] overflow-hidden rounded-[10px] bg-white/95
                     divide-y divide-[rgb(26_25_25/0.07)]"
            >
              <template x-for="k in hasil" :key="k.nama">
                <li>
                  <button
                    type="button"
                    x-on:click="pilihWilayah(k.nama, k.pulau, $event)"
                    class="flex w-full items-baseline justify-between gap-3
                           px-[14px] py-[10px] text-left transition-colors
                           hover:bg-[rgb(26_25_25/0.04)]"
                  >
                    <span
                      class="text-[length:var(--ukuran-catatan)] font-semibold
                             text-[var(--color-tinta)]"
                      x-text="k.nama"
                    ></span>
                    <span
                      class="shrink-0 text-[length:var(--ukuran-catatan)]
                             text-[rgb(26_25_25/0.55)]"
                      x-text="k.pulau"
                    ></span>
                  </button>
                </li>
              </template>
            </ul>
          </template>

          <!-- Sedang mengambil indeks nama, dan tak ada hasil -->
          <template x-if="cari.trim().length >= 2 && !hasil.length">
            <p
              class="mt-[6px] rounded-[10px] bg-white/80 px-[14px] py-[10px]
                     text-[length:var(--ukuran-catatan)] text-[rgb(26_25_25/0.6)]"
              x-text="indeks.length
                ? 'Tidak ada provinsi yang namanya cocok.'
                : 'Memuat daftar provinsi…'"
            ></p>
          </template>

          <!-- Tiga teratas -->
          <p
            class="mt-[clamp(14px,4vw,20px)] text-[length:var(--ukuran-eyebrow)] font-medium
                   tracking-[0.14em] uppercase text-[rgb(255_255_255/0.85)]
                   [text-shadow:0_1px_6px_rgb(0_0_0/0.45)]"
          >
            3 provinsi dengan deforestasi terluas
          </p>

          <template x-if="atasGagal">
            <p
              class="mt-[6px] rounded-[10px] bg-white/80 px-[14px] py-[10px]
                     text-[length:var(--ukuran-catatan)] text-[rgb(26_25_25/0.6)]"
            >
              Daftar tidak bisa dimuat — angkanya datang dari layanan luar
              (aws.simontini.id) yang sekarang tidak terjangkau.
            </p>
          </template>

          <ul class="mt-[8px] grid gap-[var(--sela)]">
            <template x-for="k in atas" :key="k.nama">
              <li>
                <button
                  type="button"
                  x-on:click="pilihWilayah(k.nama, k.pulau, $event)"
                  x-on:mouseenter="barisSorot = k.nama"
                  x-on:mouseleave="barisSorot = null"
                  :class="barisSorot === k.nama && 'ring-2 ring-[var(--color-api)]'"
                  class="grid w-full grid-cols-[auto_clamp(46px,13vw,64px)_1fr] items-center
                         gap-[clamp(10px,3vw,16px)] bg-white p-[clamp(12px,3.4vw,16px)]
                         text-left transition-shadow"
                >
                  <!-- Nomor bermakna: daftarnya memang peringkat luas -->
                  <span
                    class="text-[length:var(--ukuran-nama)] font-bold
                           text-[rgb(26_25_25/0.25)] tabular-nums"
                    x-text="k.peringkat"
                  ></span>

                  <svg
                    viewBox="0 0 100 100"
                    aria-hidden="true"
                    :class="!k.siluet && 'opacity-0'"
                    class="block h-auto w-full overflow-visible transition-opacity"
                  >
                    <path
                      :d="k.siluet?.d"
                      class="fill-[var(--color-api)] stroke-[var(--color-garis)]
                             [stroke-width:1.2] [vector-effect:non-scaling-stroke]"
                    />
                  </svg>

                  <span class="min-w-0">
                    <span
                      class="block text-[length:var(--ukuran-nama)] leading-[1.2] font-bold"
                      x-text="k.nama"
                    ></span>
                    <span
                      class="mt-[2px] block text-[length:var(--ukuran-catatan)]
                             text-[rgb(26_25_25/0.55)]"
                      x-text="k.pulau"
                    ></span>
                    <span class="mt-[6px] flex justify-between gap-4">
                      <span
                        class="text-[length:var(--ukuran-catatan)] text-[rgb(26_25_25/0.6)]"
                      >
                        Luas deforestasi
                      </span>
                      <span
                        class="text-[length:var(--ukuran-catatan)] font-semibold"
                        x-text="k.luas + ' ha'"
                      ></span>
                    </span>
                  </span>
                </button>
              </li>
            </template>
          </ul>
        </div>

        <!-- Tabel setara: peta warna tidak bisa dibaca pembaca layar. -->
        <table class="sr-only">
          <caption>
            Titik panas karhutla per provinsi (data contoh, bukan data resmi)
          </caption>
          <tr>
            <th scope="col">Provinsi</th>
            <th scope="col">Titik panas</th>
            <th scope="col">Status</th>
          </tr>
          <template x-for="p in tabel" :key="p.nama">
            <tr>
              <th scope="row" x-text="p.nama"></th>
              <td x-text="p.titik"></td>
              <td x-text="p.status"></td>
            </tr>
          </template>
        </table>
      </div>

      <!-- Popup berita wilayah.

           Menutupi peta hampir seluas layar section ini: begitu satu wilayah
           ditekan, yang dibaca adalah beritanya — petanya sudah selesai
           tugasnya. Wilayah yang ditekan menentukan tab pulau mana yang
           terbuka lebih dulu; sesudah itu popup jadi jalan masuk ke seluruh
           berita, jadi tabnya bebas dipindah dan rentang tanggal di kiri
           menyaring daftarnya.

           Dipasang fixed ke viewport (bukan absolut di dalam kanvas) supaya
           ukurannya sama-sama satu layar di kedua mode. Markupnya tetap tinggal
           di dalam <section> ini supaya section tetap satu blok saat dipindah
           ke Blade, tetapi x-teleport memindahkannya ke <body> saat berjalan:
           section ini memakai masker tepi-lunak, dan masker berlaku untuk
           seluruh subtree — popup yang tertinggal di dalamnya ikut memudar
           tembus pandang setiap kali halaman digulir.

           x-if, bukan x-show: pada versi Alpine yang dipakai di sini, x-show
           di elemen yang subtree-nya ikut berubah tidak selalu menerapkan
           kembali display:none — popupnya tersisa sebagai kotak putih kosong.
           Kondisi yang lebih halus (satu baris teks) memakai pengikatan kelas
           'hidden', yang selalu ikut berubah. -->
      <template x-teleport="body">
      <template x-if="pilihan || memuat">
        <div
          role="dialog"
          aria-label="Berita karhutla wilayah terpilih"
          tabindex="-1"
          x-init="$el.focus(); pasangAsal($el)"
          class="peta-popup fixed inset-x-[clamp(10px,4vw,190px)] top-[calc(4rem+clamp(10px,2.4vw,26px))]
                 bottom-[clamp(10px,2.4vw,26px)] z-[45] flex flex-col overflow-hidden
                 rounded-[14px] bg-white text-[var(--color-tinta)]
                 shadow-[0_26px_70px_rgb(0_0_0/0.45)] outline-none
                 panggung:inset-x-[7vw] panggung:top-[calc(4rem+3vh)]
                 panggung:bottom-[5vh] panggung:rounded-[16px]"
        >
          <!-- Kepala: wilayah yang ditekan. Siluetnya dipotong dari geometri
               yang sama dengan outline di peta — satu-satunya sisa bentuk
               wilayah setelah petanya tertutup. -->
          <div
            class="flex shrink-0 items-start gap-[clamp(10px,2.6vw,14px)]
                   border-b border-[rgb(26_25_25/0.1)] p-[clamp(14px,3.4vw,20px)]
                   pr-[54px] panggung:p-[22px_28px] panggung:pr-[76px]"
          >
            <template x-if="memuat">
              <div>
                <p
                  role="status"
                  class="text-[length:var(--ukuran-catatan)] font-medium tracking-[0.1em]
                         uppercase text-[var(--color-bara)]"
                >
                  Mengidentifikasi wilayah…
                </p>
                <p class="mt-2 text-[length:var(--ukuran-catatan)] text-[rgb(26_25_25/0.65)]">
                  Memuat berita terkait…
                </p>
              </div>
            </template>

            <template x-if="pilihan">
              <div class="contents">
                <svg
                  :class="!pilihan?.siluet && 'hidden'"
                  viewBox="0 0 100 100"
                  aria-hidden="true"
                  class="mt-[2px] size-[clamp(34px,8vw,46px)] shrink-0 overflow-visible"
                >
                  <path
                    :d="pilihan?.siluet?.d"
                    class="fill-[var(--color-api)] stroke-[var(--color-garis)]
                           [stroke-width:1.2] [vector-effect:non-scaling-stroke]"
                  />
                </svg>

                <div class="grid min-w-0 flex-1 gap-[2px]">
                  <p
                    class="text-[length:var(--ukuran-rincian-nama)] leading-[1.1] font-bold
                           tracking-[-0.01em]"
                    x-text="pilihan?.nama"
                  ></p>
                  <p
                    :class="!pilihan?.meta && 'hidden'"
                    class="text-[length:var(--ukuran-catatan)] font-medium tracking-[0.1em]
                           uppercase text-[var(--color-bara)]"
                    x-text="pilihan?.meta"
                  ></p>
                  <!-- Judul panel kini provinsi itu sendiri, jadi kalimat ini tidak
                       perlu menyebut namanya lagi. -->
                  <p
                    :class="!pilihan?.angka && 'hidden'"
                    class="mt-1 text-[length:var(--ukuran-catatan)] text-[rgb(26_25_25/0.7)]"
                  >
                    <span
                      class="font-bold text-[var(--color-tinta)]"
                      x-text="pilihan?.angka"
                    ></span>
                    <span>titik panas tercatat</span>
                  </p>
                </div>
              </div>
            </template>

            <button
              type="button"
              aria-label="Tutup berita wilayah"
              x-on:click="tutup()"
              class="absolute top-[clamp(12px,3vw,18px)] right-[clamp(12px,3vw,18px)] z-[1]
                     grid size-[32px] cursor-pointer place-items-center rounded-full
                     border border-[rgb(26_25_25/0.12)] bg-[rgb(26_25_25/0.05)]
                     text-[var(--color-tinta)] transition
                     hover:rotate-90 hover:bg-[rgb(26_25_25/0.12)]
                     focus-visible:outline-2 focus-visible:outline-offset-2
                     focus-visible:outline-[var(--color-api)]
                     panggung:size-[34px]"
            >
              <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="size-[15px]"
              >
                <path d="M6 6 18 18M6 18 18 6" />
              </svg>
            </button>
          </div>

          <template x-if="pilihan">
            <div class="flex min-h-0 flex-1 flex-col">
              <!-- Penyaring: rentang tanggal menempel di kiri, tab pulau
                   didorong ke tepi kanan lewat justify-between.
                   Di layar sempit barisnya membungkus, jalur tab tetap satu
                   baris yang bisa digeser mendatar. -->
              <div
                class="flex shrink-0 flex-wrap items-center justify-between
                       gap-x-[clamp(14px,3vw,32px)]
                       gap-y-[10px] border-b border-[rgb(26_25_25/0.1)]
                       px-[clamp(14px,3.4vw,20px)] py-[clamp(10px,2.4vw,14px)]
                       panggung:px-[28px] panggung:py-[16px]"
              >
                <div class="flex shrink-0 items-center gap-[8px]">
                  {{-- Satu kolom untuk rentang tanggal, bukan dua. flatpickr
                       mode range yang mengisinya; komponen Alpine tetap
                       menyimpan `dari` dan `sampai` terpisah karena
                       beritaTampil() menyaring memakai keduanya.

                       size=23, bukan kelas lebar: "2026-08-01 – 2026-08-13"
                       panjangnya 23 karakter, dan lebar sebesar itu tidak ada
                       di dist/style.css yang cuma memuat kelas terpakai. --}}
                  <label>
                    <span class="sr-only">Rentang tanggal berita</span>
                    <input
                      type="text"
                      size="23"
                      readonly
                      placeholder="Pilih rentang tanggal"
                      x-init="pasangKalender($el)"
                      class="rounded-[8px] border border-[rgb(26_25_25/0.16)] bg-white
                             px-[9px] py-[6px] text-[length:var(--ukuran-catatan)]
                             leading-[1.2] text-[var(--color-tinta)]
                             focus-visible:border-[var(--color-bara)]
                             focus-visible:outline-none panggung:text-[14px]"
                    />
                  </label>

                  <!-- Ikon, bukan tautan berteks: tombol berteks melebarkan
                       kelompok penyaring dan mendorong jalur tab ke baris
                       kedua begitu rentangnya diisi. -->
                  <button
                    type="button"
                    aria-label="Hapus rentang tanggal"
                    x-on:click="hapusTanggal()"
                    :class="!(dari || sampai) && 'invisible'"
                    class="grid size-[28px] shrink-0 cursor-pointer place-items-center
                           rounded-full border border-[rgb(26_25_25/0.12)]
                           bg-[rgb(26_25_25/0.05)] text-[var(--color-bara)]
                           transition hover:bg-[rgb(26_25_25/0.12)]
                           focus-visible:outline-2 focus-visible:outline-offset-2
                           focus-visible:outline-[var(--color-api)]"
                  >
                    <svg
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.2"
                      stroke-linecap="round"
                      class="size-[13px]"
                    >
                      <path d="M6 6 18 18M6 18 18 6" />
                    </svg>
                  </button>
                </div>

                <div
                  role="tablist"
                  aria-label="Pulau"
                  {{-- Tanpa flex-1: kalau jalur ini ikut memuai, ia menghabiskan
                       sisa ruang dan justify-between pada induknya tidak punya
                       apa pun untuk didorong. Selebar isinya saja, lalu
                       terdorong ke kanan. Di layar sempit basis-full membuatnya
                       turun ke baris sendiri, jadi tetap penuh dan bisa
                       digeser. --}}
                  class="tanpa-bilah-gulir flex min-w-0 basis-full items-center
                         gap-[clamp(14px,2.8vw,26px)] overflow-x-auto
                         panggung:basis-auto panggung:gap-[14px]"
                >
                  <template x-for="t in tabPulau" :key="t.kunci">
                    <button
                      type="button"
                      role="tab"
                      :aria-selected="tabAktif === t.kunci ? 'true' : 'false'"
                      x-on:click="tabAktif = t.kunci"
                      :class="tabAktif === t.kunci
                        ? 'text-[var(--color-tinta)]'
                        : 'text-[rgb(26_25_25/0.6)] hover:text-[rgb(26_25_25/0.85)]'"
                      class="shrink-0 cursor-pointer border-none bg-transparent
                             whitespace-nowrap text-[length:clamp(14px,3.6vw,17px)]
                             leading-[1.2] font-bold transition-colors
                             focus-visible:outline-2 focus-visible:outline-offset-4
                             focus-visible:outline-[var(--color-api)]
                             panggung:text-[18px]"
                      x-text="t.label"
                    ></button>
                  </template>
                </div>
              </div>

              <!-- Daftar berita: foto lanskap di kiri, tanggal dan judul di
                   kanan, garis rambut sebagai pemisah baris. -->
              <div
                role="tabpanel"
                data-lenis-prevent
                :aria-label="labelTab()"
                class="min-h-0 flex-1 overflow-y-auto px-[clamp(14px,3.4vw,20px)]
                       panggung:px-[28px]"
              >
                <ul :class="!beritaTampil().length && 'hidden'">
                  <template x-for="b in beritaTampil()" :key="b.judul">
                    <li class="border-b border-[rgb(26_25_25/0.1)] last:border-b-0">
                      <article
                        class="flex items-center gap-[clamp(14px,4vw,44px)]
                               py-[clamp(12px,3vw,20px)] panggung:gap-[64px]
                               panggung:py-[24px]"
                      >
                        <!-- Judul dan foto membuka pop-up rincian yang sama
                             dengan kartu korsel. Pop-up itu tinggal di komponen
                             "korsel" (#beranda), jadi di sini cukup disiarkan
                             id laporannya lewat window event — kedua komponen
                             tidak perlu saling mengenal.

                             Laporan bervideo memakai <video>, bukan <img>:
                             `gambar` punya cadangan satu foto bawaan yang sama
                             untuk semua laporan tanpa thumbnail, jadi barisnya
                             akan menampilkan gambar yang tidak ada
                             hubungannya dengan videonya. Dengan <video> tanpa
                             poster, peramban menggambar frame pertama video
                             itu sendiri. Kelasnya dipakai bersama supaya kedua
                             cabang tidak bisa berbeda ukuran. -->
                        @php
                          $mediaBerita = 'aspect-[3/2] w-[clamp(104px,29vw,180px)] shrink-0 cursor-pointer object-cover panggung:w-[290px]';
                        @endphp

                        <template x-if="!b.video">
                          <img
                            x-on:click="$dispatch('buka-laporan', { id: b.id })"
                            :src="b.gambar"
                            :alt="b.alt || ''"
                            loading="lazy"
                            decoding="async"
                            class="{{ $mediaBerita }}"
                          />
                        </template>

                        <template x-if="b.video">
                          <video
                            x-on:click="$dispatch('buka-laporan', { id: b.id })"
                            :src="b.video"
                            :poster="b.poster"
                            :aria-label="b.alt || ''"
                            :preload="b.poster ? 'none' : 'metadata'"
                            muted
                            playsinline
                            class="{{ $mediaBerita }}"
                          ></video>
                        </template>
                        <div class="min-w-0">
                          <p
                            class="text-[length:var(--ukuran-tanggal)] leading-[1.2] font-normal"
                            x-text="`• ${b.tanggal} •`"
                          ></p>
                          <p
                            x-on:click="$dispatch('buka-laporan', { id: b.id })"
                            class="mt-[clamp(8px,2.2vw,14px)] cursor-pointer
                                   text-[length:var(--ukuran-judul)]
                                   leading-[1.25] font-bold panggung:mt-[16px]
                                   panggung:text-[26px]"
                            x-text="b.judul"
                          ></p>
                        </div>
                      </article>
                    </li>
                  </template>
                </ul>

                <div
                  :class="beritaTampil().length && 'hidden'"
                  class="py-[clamp(24px,7vw,48px)] text-[length:var(--ukuran-catatan)]
                         leading-[1.5] text-[rgb(26_25_25/0.7)]"
                >
                  <p>
                    Belum ada data untuk
                    <span class="font-semibold" x-text="labelTab()"></span>.
                  </p>
                  <button
                    type="button"
                    x-on:click="hapusTanggal()"
                    :class="!(dari || sampai) && 'hidden'"
                    class="mt-2 cursor-pointer font-semibold text-[var(--color-bara)]
                           underline underline-offset-2 transition-colors
                           hover:text-[var(--color-api)]"
                  >
                    Hapus rentang tanggal
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </template>
      </template>
    </section>

    