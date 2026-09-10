<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pilih Berkas')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
{{-- Tanpa sidebar dan header CMS: halaman ini hanya muncul di dalam modal
     editor TinyMCE, dan kroma navigasi di ruang sesempit itu justru menutupi
     grid gambarnya. --}}
<body class="bg-admin-base text-admin-smoke font-admin antialiased">
    <main class="mx-auto max-w-6xl px-4 py-5">
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
