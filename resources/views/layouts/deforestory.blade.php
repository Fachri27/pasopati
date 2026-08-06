<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="https://pasopati.id/theme/webmag/img/auriga2.png">

    @php
        $meta = seo()->all();
        $locale = $meta['locale'];
        $currentLocale = app()->getLocale();
        $alternate = $locale === 'id' ? 'en' : 'id';
        $alternateUrl = url(str_replace("/{$locale}/", "/{$alternate}/", request()->getRequestUri()));
    @endphp

    <title>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">
    <meta name="title" content="{{ $meta['title'] }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta itemprop="name" content="{{ $meta['title'] }}">
    <meta itemprop="description" content="{{ $meta['description'] }}">
    <meta itemprop="image" content="{{ $meta['image'] }}">

    <meta property="og:locale" content="{{ app()->getLocale() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="{{ $meta['type'] ?? 'website' }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:image" content="{{ $meta['image'] }}">
    <meta property="og:image:alt" content="{{ $meta['title'] }}">
    <meta property="og:image:width" content="300">
    <meta property="og:image:height" content="150">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@{{ config('app.twitter_handle') ?? 'yourhandle' }}">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['description'] }}">
    <meta name="twitter:image" content="{{ $meta['image'] }}">
    <meta name="twitter:image:alt" content="{{ $meta['title'] }}">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5WC19K3Y9D"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-5WC19K3Y9D');
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if (! empty(config('services.turnstile.site_key')))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    @endif
</head>

<body id="top" class="flex flex-col min-h-screen bg-paper text-ink antialiased leading-relaxed">

    {{-- NAVBAR (sama dengan home — komponen navbar-user) --}}
    @include('front.components.navbar-user')

    {{-- PAGE CONTENT --}}
    <main class="flex-grow">
        @yield('content')
    </main>

    {{-- FOOTER (referensi design Deforestory) --}}
    <footer>
        <div class="bg-soft px-5 py-14 md:py-16">
            <div class="max-w-[1200px] mx-auto text-center space-y-5">
                <p class="text-[.95rem] leading-[1.75] text-ink max-w-[78ch] mx-auto">
                    {{ $currentLocale === 'id'
                        ? 'Pasopati Project dirancang sebagai sebuah situs yang menampilkan informasi, data, dan analisis isu-isu kehutanan, persawitan, dan pertambangan. Situs ini fokus menyampaikan suara kritis pada tema-tema tersebut, termasuk mengenai pelakunya dan kebijakan-kebijakan terkait.'
                        : 'The Pasopati Project is designed as a platform to present information, data, and analysis regarding issues related to forestry, oil palm, and mining in Indonesia. This website focuses on delivering critical perspectives and insights on these issues, including related actors and government policies.' }}
                </p>
                <p class="text-[.95rem] leading-[1.75] text-ink max-w-[78ch] mx-auto">
                    {{ $currentLocale === 'id'
                        ? 'Pasopati Project didedikasikan untuk mencapai salah satu tujuan Auriga, yakni mengelimininir aksi-aksi destruktif terhadap sumberdaya alam.'
                        : 'The Pasopati Project website is intended to fulfill one of Yayasan Auriga’s goals: to eliminate destructive actions related to natural resource exploitation in Indonesia.' }}
                </p>
                <p class="text-[.95rem] leading-[1.75] text-ink max-w-[78ch] mx-auto">
                    {{ $currentLocale === 'id'
                        ? 'Situs ini dikelola oleh Auriga. Namun demikian ekspose-ekspose tertentu dalam situs ini dilakukan bersama jejaring.'
                        : 'The site is managed by Auriga, with particular analyses conducted in conjunction with civil society coalitions.' }}
                </p>
            </div>
        </div>
        <div class="bg-ink text-white px-5 py-6">
            <div class="max-w-[1200px] mx-auto flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm font-medium tracking-[.02em]">© AURIGA NUSANTARA. ALL RIGHTS RESERVED.</p>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold tracking-[.04em]">FOLLOW :</span>
                    <div class="flex items-center gap-3">
                        <a href="https://x.com/AURIGA_ID" target="_blank" rel="noopener" aria-label="Twitter" class="text-white hover:text-pasopati transition-colors">
                            <svg viewBox="-143 145 512 512" class="w-[22px] h-[22px]"><path fill="currentColor" d="M113,145c-141.4,0-256,114.6-256,256s114.6,256,256,256s256-114.6,256-256S254.4,145,113,145z M215.2,361.2c0.1,2.2,0.1,4.5,0.1,6.8c0,69.5-52.9,149.7-149.7,149.7c-29.7,0-57.4-8.7-80.6-23.6c4.1,0.5,8.3,0.7,12.6,0.7c24.6,0,47.3-8.4,65.3-22.5c-23-0.4-42.5-15.6-49.1-36.5c3.2,0.6,6.5,0.9,9.9,0.9c4.8,0,9.5-0.6,13.9-1.9C13.5,430-4.6,408.7-4.6,383.2v-0.6c7.1,3.9,15.2,6.3,23.8,6.6c-14.1-9.4-23.4-25.6-23.4-43.8c0-9.6,2.6-18.7,7.1-26.5c26,31.9,64.7,52.8,108.4,55c-0.9-3.8-1.4-7.8-1.4-12c0-29,23.6-52.6,52.6-52.6c15.1,0,28.8,6.4,38.4,16.6c12-2.4,23.2-6.7,33.4-12.8c-3.9,12.3-12.3,22.6-23.1,29.1c10.6-1.3,20.8-4.1,30.2-8.3C234.4,344.5,225.5,353.7,215.2,361.2z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/nusantaraauriga/" target="_blank" rel="noopener" aria-label="Facebook" class="text-white hover:text-pasopati transition-colors">
                            <svg viewBox="0 0 32 32" class="w-[22px] h-[22px]"><path fill="currentColor" d="M30.996 16.091c-0.001-8.281-6.714-14.994-14.996-14.994s-14.996 6.714-14.996 14.996c0 7.455 5.44 13.639 12.566 14.8l0.086 0.012v-10.478h-3.808v-4.336h3.808v-3.302c-0.019-0.167-0.029-0.361-0.029-0.557 0-2.923 2.37-5.293 5.293-5.293 0.141 0 0.281 0.006 0.42 0.016l-0.018-0.001c1.199 0.017 2.359 0.123 3.491 0.312l-0.134-0.019v3.69h-1.892c-0.086-0.012-0.185-0.019-0.285-0.019-1.197 0-2.168 0.97-2.168 2.168 0 0.068 0.003 0.135 0.009 0.202l-0.001-0.009v2.812h4.159l-0.665 4.336h-3.494v10.478c7.213-1.174 12.653-7.359 12.654-14.814v-0z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/auriga_id/" target="_blank" rel="noopener" aria-label="Instagram" class="text-white hover:text-pasopati transition-colors">
                            <svg viewBox="-143 145 512 512" class="w-[22px] h-[22px]"><g fill="currentColor"><path d="M113,446c24.8,0,45.1-20.2,45.1-45.1c0-9.8-3.2-18.9-8.5-26.3c-8.2-11.3-21.5-18.8-36.5-18.8s-28.3,7.4-36.5,18.8c-5.3,7.4-8.5,16.5-8.5,26.3C68,425.8,88.2,446,113,446z"/><polygon points="211.4,345.9 211.4,308.1 211.4,302.5 205.8,302.5 168,302.6 168.2,346"/><path d="M183,401c0,38.6-31.4,70-70,70c-38.6,0-70-31.4-70-70c0-9.3,1.9-18.2,5.2-26.3H10v104.8C10,493,21,504,34.5,504h157c13.5,0,24.5-11,24.5-24.5V374.7h-38.2C181.2,382.8,183,391.7,183,401z"/><path d="M113,145c-141.4,0-256,114.6-256,256s114.6,256,256,256s256-114.6,256-256S254.4,145,113,145z M241,374.7v104.8c0,27.3-22.2,49.5-49.5-49.5h-157C7.2,529-15,506.8-15,479.5V374.7v-52.3c0-27.3,22.2-49.5,49.5-49.5h157c27.3,0,49.5,22.2,49.5,49.5V374.7z"/></g></svg>
                        </a>
                        <a href="#" aria-label="LinkedIn" class="text-white hover:text-pasopati transition-colors">
                            <svg viewBox="0 0 32 32" class="w-[22px] h-[22px]"><g fill="currentColor"><path d="M26.49,30H5.5A3.35,3.35,0,0,1,3,29a3.35,3.35,0,0,1-1-2.48V5.5A3.35,3.35,0,0,1,3,3,3.35,3.35,0,0,1,5.5,2h21A3.35,3.35,0,0,1,29,3,3.35,3.35,0,0,1,30,5.5v21A3.52,3.52,0,0,1,26.49,30ZM9.11,11.39a2.22,2.22,0,0,0,1.6-.58,1.83,1.83,0,0,0,.6-1.38A2.09,2.09,0,0,0,10.68,8a2.14,2.14,0,0,0-1.51-.55A2.3,2.3,0,0,0,7.57,8,1.87,1.87,0,0,0,7,9.43a1.88,1.88,0,0,0,.57,1.38A2.1,2.1,0,0,0,9.11,11.39ZM11,13H7.19V24.54H11Zm13.85,4.94a5.49,5.49,0,0,0-1.24-4,4.22,4.22,0,0,0-3.15-1.27,3.44,3.44,0,0,0-2.34.66A6,6,0,0,0,17,14.64V13H13.19V24.54H17V17.59a.83.83,0,0,1,.1-.43,2.73,2.73,0,0,1,.7-1,1.81,1.81,0,0,1,1.28-.44,1.59,1.59,0,0,1,1.49.75,3.68,3.68,0,0,1,.44,1.9v6.15h3.85ZM17,14.7a.05.05,0,0,1,.06-.06v.06Z"/></g></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <a href="#top" aria-label="Kembali ke atas" class="fixed right-5 bottom-5 z-40 w-12 h-12 rounded-full bg-pasopati text-white flex items-center justify-center shadow-lg hover:bg-pasopati-d transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
        </a>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>

</html>
