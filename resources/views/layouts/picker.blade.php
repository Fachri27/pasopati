<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pilih Berkas')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
{{-- Tanpa sidebar dan header CMS: halaman ini hanya muncul di dalam modal
     editor TinyMCE, dan kroma navigasi di ruang sesempit itu justru menutupi
     grid gambarnya. --}}
<body class="admin-shell antialiased">
    <main class="mx-auto max-w-6xl px-4 py-5">
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
