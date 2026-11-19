<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pasopati CMS')</title>

    {{-- Sidebar menciut diingat per peramban. Dipasang di sini, sebelum
         halaman tergambar: kalau menunggu Alpine, sidebar sempat tampil lebar
         lalu menyusut di tiap pindah halaman. Huruf CMS memakai huruf sistem,
         jadi tidak ada lagi tautan Google Fonts di layout ini. --}}
    <script>
        try { if (localStorage.getItem('cms-ciut') === '1') document.documentElement.setAttribute('data-ciut', ''); } catch (e) {}

        function cmsCiut() {
            const ciut = document.documentElement.toggleAttribute('data-ciut');
            try { localStorage.setItem('cms-ciut', ciut ? '1' : '0'); } catch (e) {}
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="admin-shell antialiased">

    @php
        // Satu sumber untuk sidebar, nav mendatar di layar sempit, dan palet
        // ⌘K. `active` berisi pola nama rute supaya menu tetap menyala di
        // halaman tambah/ubah, bukan hanya di halaman daftarnya. Pola Fellowship
        // dan Deforestory ditulis satu per satu karena `fellowship.preview` dan
        // `deforestory` adalah rute PUBLIK yang kebetulan berawalan sama.
        $menu = [
            'Ringkasan' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'active' => ['dashboard']],
            ],
            'Konten' => [
                ['route' => 'pages.index', 'label' => 'Artikel', 'icon' => 'doc', 'active' => ['pages.*']],
                ['route' => 'events.index', 'label' => 'Kejadian', 'icon' => 'pin', 'active' => ['events.*']],
                ['route' => 'fellowship.index', 'label' => 'Fellowship', 'icon' => 'users', 'active' => ['fellowship.index', 'fellowship.create', 'fellowship.edit']],
                ['route' => 'petition.admin.index', 'label' => 'Petisi', 'icon' => 'pen', 'active' => ['petition.admin.*']],
                ['route' => 'deforestory.index', 'label' => 'Deforestory', 'icon' => 'tree', 'active' => ['deforestory.index', 'deforestory.laporan.index', 'deforestory.laporan.create', 'deforestory.laporan.edit', 'deforestory.subscribers']],
            ],
            'Pustaka' => [
                ['route' => 'kategori.index', 'label' => 'Kategori', 'icon' => 'tag', 'active' => ['kategori.*']],
                ['route' => 'reference.index', 'label' => 'Reference', 'icon' => 'image', 'active' => ['reference.*']],
                ['route' => 'data-visualizations.index', 'label' => 'Data & Grafik', 'icon' => 'chart', 'active' => ['data-visualizations.*']],
            ],
        ];

        $baru = [
            ['route' => 'pages.create', 'label' => 'Artikel baru'],
            ['route' => 'events.create', 'label' => 'Kejadian baru'],
            ['route' => 'fellowship.create', 'label' => 'Fellowship baru'],
            ['route' => 'petition.admin.create', 'label' => 'Petisi baru'],
            ['route' => 'kategori.create', 'label' => 'Kategori baru'],
            ['route' => 'data-visualizations.create', 'label' => 'Grafik baru'],
        ];

        if (auth()->user()->role === 'admin') {
            $menu['Pengaturan'] = [
                ['route' => 'user.index', 'label' => 'Pengguna', 'icon' => 'user', 'active' => ['user.*']],
            ];
            $baru[] = ['route' => 'user.create', 'label' => 'Pengguna baru'];
        }

        $palet = [];
        foreach ($menu as $kelompok => $butir) {
            foreach ($butir as $item) {
                $palet[] = ['group' => $kelompok, 'label' => $item['label'], 'url' => route($item['route'])];
            }
        }
        foreach ($baru as $item) {
            $palet[] = ['group' => 'Buat baru', 'label' => $item['label'], 'url' => route($item['route'])];
        }

        $pengguna = auth()->user();
    @endphp

    {{-- Gulirannya dibiarkan di viewport (min-h-screen), BUKAN dikurung di
         dalam <main> lewat h-screen + overflow-hidden.

         Sidebar tidak butuh itu untuk tetap diam: ia sudah fixed. Yang rusak
         justru sebaliknya — kontainer gulir bersarang membuat semua yang
         memposisikan diri terhadap viewport meleset: bilah atas halaman dan
         kartu samping form yang `sticky`, serta toolbar_sticky milik TinyMCE,
         yang menghitung posisinya sendiri lewat JS lalu mengambang menimpa isi
         halaman. --}}

    {{-- Sidebar (lg ke atas). Sewarna alas halaman dan hanya dipisah garis
         rambut: isi halamanlah yang naik ke permukaan putih, bukan navigasinya. --}}
    <aside class="admin-side" aria-label="Navigasi CMS">
        <div class="admin-side__row flex h-12 shrink-0 items-center justify-between gap-2 pr-2 pl-[18px]">
            <a href="{{ route('dashboard') }}" class="admin-nav-label text-[15px] font-semibold tracking-tight text-admin-smoke">
                Pasopati <span class="font-medium text-admin-muted">CMS</span>
            </a>
            <button type="button" class="admin-icon-btn" onclick="cmsCiut()" title="Ciutkan atau lebarkan sidebar" aria-label="Ciutkan atau lebarkan sidebar">
                <x-admin.icon name="panel" />
            </button>
        </div>

        <div class="shrink-0 px-2 pb-1">
            <button type="button" class="admin-nav-link w-full" title="Cari (⌘K)"
                    onclick="window.dispatchEvent(new CustomEvent('admin-palet'))">
                <x-admin.icon name="search" />
                <span class="admin-nav-label flex-1 text-left font-normal">Cari…</span>
                <kbd class="admin-kbd admin-nav-label">⌘K</kbd>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 pb-3">
            @foreach ($menu as $kelompok => $butir)
                <div class="admin-nav-group">{{ $kelompok }}</div>
                <div class="grid gap-px">
                    @foreach ($butir as $item)
                        <a href="{{ route($item['route']) }}" class="admin-nav-link" title="{{ $item['label'] }}"
                           @if (request()->routeIs(...$item['active'])) aria-current="page" @endif>
                            <x-admin.icon :name="$item['icon']" />
                            <span class="admin-nav-label">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-admin-line p-2">
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="admin-nav-link" title="Lihat situs">
                <x-admin.icon name="external" />
                <span class="admin-nav-label">Lihat situs</span>
            </a>

            <div class="admin-side__user mt-1 flex items-center gap-2.5 px-1.5 py-1.5">
                @if ($pengguna->image)
                    <img src="{{ asset('storage/'.$pengguna->image) }}" alt="" class="size-7 shrink-0 rounded-full object-cover">
                @else
                    <div class="grid size-7 shrink-0 place-items-center rounded-full bg-admin-tint-strong text-[11.5px] font-semibold text-admin-smoke">
                        {{ strtoupper(mb_substr($pengguna->name, 0, 1)) }}
                    </div>
                @endif
                <div class="admin-nav-label min-w-0 flex-1 leading-tight">
                    <p class="truncate text-[13px] font-medium text-admin-smoke">{{ $pengguna->name }}</p>
                    <p class="admin-eyebrow truncate">{{ ucfirst($pengguna->role) }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-icon-btn" title="Keluar" aria-label="Keluar">
                        <x-admin.icon name="logout" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="admin-main flex min-h-screen flex-col">
        {{-- Layar sempit: bilah merek + menu mendatar yang bisa digeser,
             menggantikan laci samping. Sengaja TIDAK menempel — yang menempel
             adalah bilah atas milik halaman (tombol kembali + Simpan), dan dua
             bilah menempel sekaligus memakan terlalu banyak layar ponsel. --}}
        <div class="border-b border-admin-line lg:hidden">
            <div class="flex h-12 items-center gap-1 pr-2 pl-4">
                <a href="{{ route('dashboard') }}" class="mr-auto text-[15px] font-semibold tracking-tight text-admin-smoke">
                    Pasopati <span class="font-medium text-admin-muted">CMS</span>
                </a>
                <button type="button" class="admin-icon-btn" title="Cari" aria-label="Cari"
                        onclick="window.dispatchEvent(new CustomEvent('admin-palet'))">
                    <x-admin.icon name="search" />
                </button>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="admin-icon-btn" title="Lihat situs" aria-label="Lihat situs">
                    <x-admin.icon name="external" />
                </a>
                <form action="{{ route('logout') }}" method="POST" class="flex">
                    @csrf
                    <button type="submit" class="admin-icon-btn" title="Keluar" aria-label="Keluar">
                        <x-admin.icon name="logout" />
                    </button>
                </form>
            </div>
            <nav class="flex gap-1 overflow-x-auto px-2 pb-2" aria-label="Menu CMS" data-nav-datar>
                @foreach ($menu as $butir)
                    @foreach ($butir as $item)
                        <a href="{{ route($item['route']) }}" class="admin-nav-link h-8 shrink-0"
                           @if (request()->routeIs(...$item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endforeach
                @endforeach
            </nav>
        </div>

        {{-- Isi halaman. Tanpa pias dan tanpa overflow sendiri: tiap view
             membawa kop (<x-admin.page-header>) dan kolomnya (.admin-wrap),
             supaya bilah atasnya bisa membentang selebar halaman. --}}
        <main class="min-w-0 flex-1">
            @if($__env->yieldContent('content'))
                @yield('content')
            @else
                {{ $slot }}
            @endif
        </main>
    </div>

    {{-- Palet perintah: ⌘K / Ctrl+K, atau tombol "Cari…" di sidebar. Hanya
         navigasi — melompat ke menu dan ke form "baru" tanpa lewat daftarnya. --}}
    <dialog class="admin-palette" aria-label="Cari halaman"
            x-data="{
                q: '',
                i: 0,
                items: @js($palet),
                get hasil() {
                    const q = this.q.trim().toLowerCase();
                    return q ? this.items.filter(x => (x.label + ' ' + x.group).toLowerCase().includes(q)) : this.items;
                },
                buka() {
                    this.q = '';
                    this.i = 0;
                    this.$root.showModal();
                    this.$nextTick(() => this.$refs.q.focus());
                },
                pergi(x) { if (x) window.location.href = x.url; },
            }"
            @admin-palet.window="buka()"
            @keydown.window="if (($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); $el.open ? $el.close() : buka(); }"
            @click.self="$el.close()">
        <div class="flex h-12 items-center gap-2.5 border-b border-admin-line px-3.5">
            <x-admin.icon name="search" class="text-admin-muted" />
            <input x-ref="q" x-model="q" type="text" autocomplete="off" placeholder="Cari halaman atau tindakan…" aria-label="Cari halaman atau tindakan"
                   class="h-full min-w-0 flex-1 bg-transparent text-[14px] outline-none placeholder:text-admin-muted"
                   @input="i = 0"
                   @keydown.down.prevent="i = Math.min(i + 1, hasil.length - 1)"
                   @keydown.up.prevent="i = Math.max(i - 1, 0)"
                   @keydown.enter.prevent="pergi(hasil[i])">
            <kbd class="admin-kbd">esc</kbd>
        </div>
        <ul class="max-h-[50vh] overflow-y-auto p-1.5" role="listbox">
            <template x-for="(x, n) in hasil" :key="x.url">
                <li>
                    <div x-show="n === 0 || hasil[n - 1].group !== x.group" class="admin-eyebrow px-2.5 pt-2 pb-1" x-text="x.group"></div>
                    <a :href="x.url" role="option" :aria-selected="n === i ? 'true' : 'false'" @mouseenter="i = n"
                       class="block rounded px-2.5 py-2 text-[13.5px] text-admin-smoke" x-text="x.label"></a>
                </li>
            </template>
            <li x-show="hasil.length === 0" class="px-2.5 py-6 text-center text-[13px] text-admin-muted">Tidak ada yang cocok.</li>
        </ul>
    </dialog>

    <script>
        // "/" melompat ke kotak cari halaman ini (elemen ber-data-cari), kecuali
        // sedang mengetik atau ada dialog terbuka. Editor TinyMCE hidup di iframe
        // sendiri, jadi ketikan di sana tidak pernah sampai ke sini.
        document.addEventListener('keydown', (e) => {
            if (e.key !== '/' || e.metaKey || e.ctrlKey || e.altKey) return;
            if (e.target.closest('input, textarea, select, [contenteditable]')) return;
            if (document.querySelector('dialog[open]')) return;

            const cari = document.querySelector('[data-cari]');
            if (!cari) return;
            e.preventDefault();
            cari.focus();
            cari.select();
        });

        // Di layar sempit menu aktif bisa berada di luar layar; geser supaya
        // terlihat tanpa menggulirkan halamannya.
        document.querySelector('[data-nav-datar] [aria-current]')?.scrollIntoView({ block: 'nearest', inline: 'center' });
    </script>

    {{-- TinyMCE dipakai form CMS lewat x-init="initEditor" (mis.
         livewire/pages/page-form.blade.php). Harus dimuat SEBELUM
         @livewireScripts: Livewire yang menyalakan Alpine, dan begitu Alpine
         menyala ia langsung menjalankan x-init — kalau berkas ini belum
         dieksekusi, yang didapat "tinymce is not defined" dan editornya tidak
         pernah muncul. --}}
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
