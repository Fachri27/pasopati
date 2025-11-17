<div class="bg-gray-100 mb-8 py-6 mt-8">
    <!-- Header Judul -->
    <div class="flex items-center justify-center mb-4">
        <div class="flex-grow border-t border-gray-400"></div>
        <span class="mx-2 text-gray-500">&laquo;&laquo;</span>
        <a href="{{ route('ngopini.index') }}">
            <h2 class="uppercase text-red-700 font-bold md:text-4xl text-2xl mx-4 tracking-wide">
                ngopini-hutan
            </h2>
        </a>
        <span class="mx-2 text-gray-500">&raquo;&raquo;</span>
        <div class="flex-grow border-t border-gray-400"></div>
    </div>

    <!-- Deskripsi -->
    <div class="flex flex-col items-center py-2 px-4">
        @php
            $locale = app()->getLocale();
        @endphp
        @if ($locale === 'id')      
            <p class="max-w-2xl text-center text-base md:text-lg text-gray-800 leading-relaxed">
                Ngopini-Hutan digagas Auriga Nusantara sebagai ruang dialog antar-pemangku kepentingan kehutanan.
                Mengundang kehadiran dan partisipasi akademisi, praktisi, pemerintah, masyarakat sipil, dan media.
            </p>
        @else
            <p class="max-w-2xl text-center text-base md:text-lg text-gray-800 leading-relaxed">
                Ngopini-Hutan was initiated by Auriga Nusantara as a space for dialogue among forestry stakeholders. It invites the presence and participation of academics, practitioners, government representatives, civil society, and the media.
            </p>
        @endif
    </div>

    <!-- Grid Konten -->
    <div class="max-w-screen-xl mx-auto px-4 py-10 grid grid-cols-1 lg:grid-cols-3 gap-10">

        <!-- Kiri: Artikel Utama -->
        <div class="lg:col-span-2">
            @if ($mainNgopini)
            @php
            $translation = $mainNgopini->translations->first();
            @endphp

            <div class="bg-white shadow-md rounded overflow-hidden">
                <img src="{{ asset('storage/' . $mainNgopini->featured_image) }}" alt="{{ $mainNgopini->title }}"
                    class="w-full h-auto object-contain">
            </div>

            <div class="flex items-center justify-center my-6">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="mx-3 text-gray-500">&laquo;&laquo;</span>
                <p class="text-red-500 font-semibold text-base md:text-lg mx-2 tracking-wide">
                    {{ \Carbon\Carbon::parse($mainNgopini->published_at)->translatedFormat('F Y') }}
                </p>
                <span class="mx-3 text-gray-500">&raquo;&raquo;</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>

            <div class="text-center space-y-3">
                <a href="{{ route('ngopini-show', $mainNgopini->slug) }}"
                    class="font-bold text-xl md:text-2xl text-[#212631] hover:text-red-600 transition leading-snug block">
                    {{ $translation->title }}
                </a>
                <p class="text-gray-600 text-sm md:text-base leading-relaxed">
                    {!! $translation->excerpt !!}
                </p>
            </div>
            @else
            <div class="bg-gray-300 w-full h-[250px] md:h-[400px] flex items-center justify-center text-gray-500">
                <p>Belum ada artikel utama.</p>
            </div>
            @endif
        </div>

        <!-- Kolom Kanan (Artikel Lainnya) -->
        <div class="space-y-6">
            @foreach ($otherNgopini as $item)
            @php
            $translation = $item->translations->first();
            @endphp

            <div class="overflow-hidden transition">
                <img src="{{ asset('storage/' . $item->featured_image) }}"
                    alt="{{ $translation ? $translation->title : $item->title }}" class="w-full h-[200px] object-cover">
                <div class="p-3">
                    <h3 class="text-lg font-bold text-[#212631] hover:text-red-600 transition mb-1">
                        <a href="{{ route('ngopini-show', $item->slug) }}">
                            {{ $translation ? $translation->title : $item->title }}
                        </a>
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $translation
                        ? Str::limit(strip_tags($translation->excerpt), 100, '...')
                        : Str::limit(strip_tags($item->content), 100, '...')
                        }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>


    </div>
</div>