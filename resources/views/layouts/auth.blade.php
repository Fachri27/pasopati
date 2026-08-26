<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk — Pasopati CMS')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-admin-base text-admin-smoke font-admin antialiased">

    {{-- Full-screen thermal gradient background --}}
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden">
        {{-- Ambient glow: thermal signature --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-1/4 -left-1/4 w-[800px] h-[800px] rounded-full opacity-[0.07]"
                 style="background: radial-gradient(circle, #f59e0b 0%, transparent 70%);"></div>
            <div class="absolute -bottom-1/4 -right-1/4 w-[600px] h-[600px] rounded-full opacity-[0.05]"
                 style="background: radial-gradient(circle, #dc2626 0%, transparent 70%);"></div>
            {{-- Top thermal scan line --}}
            <div class="absolute top-0 left-0 right-0 h-[3px]"
                 style="background: linear-gradient(90deg, #f59e0b 0%, #ea580c 35%, #dc2626 65%, #0f1117 100%);"></div>
        </div>

        <main class="relative z-10 w-full max-w-md mx-4">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
