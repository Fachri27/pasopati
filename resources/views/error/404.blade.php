@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-admin-raised px-6">

    <div class="text-center">
        <h1 class="text-9xl font-extrabold text-gray-300 tracking-widest">404</h1>

        <div class="bg-admin-amber px-3 py-1 text-sm text-white rounded rotate-12 inline-block mt-4">
            Halaman Tidak Ditemukan
        </div>

        <p class="text-gray-600 mt-6 max-w-md mx-auto">
            Maaf, halaman yang kamu cari tidak tersedia atau sudah dipindahkan.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/"
                class="px-6 py-3 bg-admin-amber hover:bg-admin-amber/85 text-white rounded-lg transition shadow">
                Kembali ke Beranda
            </a>

            <a href="javascript:history.back()"
                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                Kembali ke Halaman Sebelumnya
            </a>
        </div>
    </div>

</div>
@endsection