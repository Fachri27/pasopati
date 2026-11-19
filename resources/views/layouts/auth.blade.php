<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk — Pasopati CMS')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
{{-- admin-shell: halaman masuk memakai token dan komponen yang sama dengan
     CMS. Tata letaknya (dua kolom) dibawa view-nya sendiri, jadi layout ini
     tidak membungkus apa pun. --}}
<body class="admin-shell antialiased">
    @if($__env->yieldContent('content'))
        @yield('content')
    @else
        {{ $slot }}
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>
