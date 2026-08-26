<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pasopati CMS')</title>

    {{-- Fonts: Space Grotesk (display) + DM Sans (body) + IBM Plex Mono (data) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-admin-base text-admin-smoke font-admin antialiased">

    {{-- Mobile sidebar overlay --}}
    {{-- Gulirannya dibiarkan di viewport (min-h-screen), BUKAN dikurung di
         dalam <main> lewat h-screen + overflow-hidden.

         Sidebar tidak butuh itu untuk tetap diam: ia sudah lg:fixed lg:inset-y-0,
         dan versi mobile-nya juga fixed. Yang rusak justru sebaliknya —
         kontainer gulir bersarang membuat semua yang memposisikan diri terhadap
         viewport meleset: `sticky top-6` dan `sticky bottom-0` pada form
         fellowship, dan toolbar_sticky milik TinyMCE di tujuh partial, yang
         menghitung posisinya sendiri lewat JS lalu mengambang menimpa isi
         halaman. --}}
    <div x-data="{ sisi: false }" class="flex min-h-screen">

        {{-- Sidebar (desktop: fixed, mobile: slide-over) --}}
        {{-- Desktop --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-60 lg:fixed lg:inset-y-0 bg-admin-surface border-r border-admin-line z-30">
            <div class="admin-thermobar"></div>

            {{-- Brand --}}
            <div class="flex items-center gap-2.5 px-5 py-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-admin-amber to-admin-red flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-admin-display font-bold text-admin-smoke text-sm tracking-tight">Pasopati</span>
                    <span class="block text-[10px] text-admin-muted font-medium uppercase tracking-widest">CMS</span>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-0.5">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                        ['route' => 'pages.index', 'label' => 'Artikel', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>'],
                        ['route' => 'events.index', 'label' => 'Kejadian', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                        ['route' => 'fellowship.index', 'label' => 'Fellowship', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                        ['route' => 'petition.admin.index', 'label' => 'Petisi', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                        ['route' => 'deforestory.index', 'label' => 'Deforestory', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                        ['route' => 'kategori.index', 'label' => 'Kategori', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="admin-nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach

                @if (auth()->user()->role === 'admin')
                    <div class="pt-3 mt-3 border-t border-admin-line">
                        <a href="{{ route('user.index') }}"
                           class="admin-nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Pengguna</span>
                        </a>
                    </div>
                @endif
            </nav>

            {{-- Footer: user info --}}
            <div class="border-t border-admin-line px-3 py-3">
                <div class="flex items-center gap-3">
                    @if (auth()->user()->image)
                        <img src="{{ asset('storage/'.auth()->user()->image) }}" alt="" class="w-8 h-8 rounded-full object-cover">
                    @else
                        <div class="w-8 h-8 rounded-full bg-admin-raised flex items-center justify-center text-xs font-bold text-admin-amber">
                            {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-admin-smoke truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-admin-muted truncate">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-admin-muted hover:text-admin-smoke transition" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Mobile sidebar --}}
        <div x-show="sisi" x-cloak class="lg:hidden fixed inset-0 z-40">
            <div x-show="sisi" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="sisi = false" class="fixed inset-0 bg-black/60"></div>
            <aside x-show="sisi" x-transition:enter="transition-transform duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition-transform duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                   class="fixed inset-y-0 left-0 w-64 bg-admin-surface border-r border-admin-line z-50 flex flex-col">
                <div class="admin-thermobar"></div>
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-admin-amber to-admin-red flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <span class="font-admin-display font-bold text-admin-smoke text-sm">Pasopati</span>
                    </div>
                    <button @click="sisi = false" class="text-admin-muted hover:text-admin-smoke">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-0.5">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}" @click="sisi = false"
                           class="admin-nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                    @if (auth()->user()->role === 'admin')
                        <div class="pt-3 mt-3 border-t border-admin-line">
                            <a href="{{ route('user.index') }}" @click="sisi = false"
                               class="admin-nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Pengguna</span>
                            </a>
                        </div>
                    @endif
                </nav>
            </aside>
        </div>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col lg:pl-60 min-h-screen">
            {{-- Top bar (mobile) --}}
            <header class="lg:hidden flex items-center gap-3 px-4 h-14 bg-admin-surface border-b border-admin-line sticky top-0 z-20">
                <button @click="sisi = true" class="text-admin-ash hover:text-admin-smoke">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="font-admin-display font-bold text-admin-smoke text-sm">Pasopati CMS</span>
            </header>

            {{-- Page content. Tanpa overflow sendiri — lihat catatan gulir di
                 pembungkus terluar. --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if($__env->yieldContent('content'))
                    @yield('content')
                @else
                    {{ $slot }}
                @endif
            </main>
        </div>
    </div>

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
