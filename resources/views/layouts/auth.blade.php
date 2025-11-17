<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'My App')</title>
    @vite(['resources/css/app.css','resources/js/app.js']) 
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900">

    <div class="flex justify-center">
        <!-- Main Content -->
        <main class="mt-10">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
