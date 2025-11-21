<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel' }}</title>

    {{-- Vite CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire Styles --}}
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100">
    @php
    $user = auth()->user()->role;
    @endphp
    <div x-data="{ openMenu: false }" class="min-h-screen flex flex-col">
        {{-- Navbar --}}
        <nav class="bg-gray-900 text-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    {{-- Logo / Judul --}}
                    <div class="flex items-center space-x-2">
                        <span class="text-xl font-bold">
                            CMS Admin
                        </span>
                    </div>

                    {{-- Menu desktop --}}
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded hover:bg-gray-800">Dashboard</a>
                        <a href="{{ route('pages.index') }}" class="px-3 py-2 rounded hover:bg-gray-800">Artikel</a>
                        <a href="{{ route('fellowship.index') }}" class="px-3 py-2 rounded hover:bg-gray-800">Fellowship</a>
                        <a href="{{ route('kategori.index') }}" class="px-3 py-2 rounded hover:bg-gray-800">Kategori</a>
                        @if ($user === 'admin')
                        <a href="{{ route('user.index') }}" class="px-3 py-2 rounded hover:bg-gray-800">Users</a>
                        @endif
                    </div>

                    {{-- Profil + tombol burger --}}
                    <div class="flex items-center space-x-3">
                        {{-- Tombol Burger Mobile --}}
                        <button @click="openMenu = !openMenu" class="md:hidden focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {{-- Profil Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center focus:outline-none">
                                <span class="text-gray-200">{{ auth()->user()->name }}</span>
                                <img src="{{ asset('storage/' . auth()->user()->image) }}" class="w-10 h-10 rounded-full ml-3 border-2 border-gray-700" alt="profile">
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-48 bg-white border shadow-lg z-50"
                                style="display: none !important">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100">
                                        🚪 Logout
                                    </button>
                                </form>
                                <a href="{{ route('user.edit', auth()->user()->id) }}" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                                    👤 Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Menu mobile --}}
            <div x-show="openMenu" x-transition class="md:hidden bg-gray-800 border-t border-gray-700" style="display: none !important">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Dashboard</a>
                    <a href="{{ route('pages.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Artikel</a>
                    <a href="{{ route('fellowship.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Fellowship</a>
                    <a href="{{ route('kategori.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Kategori</a>
                    @if ($user === 'admin')
                    <a href="{{ route('user.index') }}" class="block px-3 py-2 rounded hover:bg-gray-700">Users</a>
                    @endif
                </div>
            </div>
        </nav>
        <main class="mt-10">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>

    

    {{-- Livewire Scripts --}}
    @livewireScripts
    {{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> --}}

    @stack('scripts')
    <script src="/js/tinymce/tinymce.min.js"></script>
</body>

</html>