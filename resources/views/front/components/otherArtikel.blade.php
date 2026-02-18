<div class="max-w-screen-lg mx-auto">
    <section class="mt-12 px-4">

        <!-- Judul -->
        <div class="flex items-center mb-6">
            <h2 class="text-red-600 font-bold uppercase text-sm tracking-wide">
                Artikel Lainnya
            </h2>
            <div class="flex-1 h-px bg-gray-300 ml-4"></div>
        </div>

        <!-- Grid artikel -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            @foreach ($related as $item)

            @php
            $trans = $item->translations->where('locale', $locale)->first();
            $thumbnail = $item->featured_image
            ? asset('storage/' . $item->featured_image)
            : asset('/images/default.jpg');

            $title = $trans->title ?? $item->title;
            $excerpt = Str::limit(strip_tags($trans->content ?? ''), 120);
            @endphp

            <a href="{{ route('show-page', ['locale' => app()->getLocale(), 'page_type'=> $item->page_type, 'slug' => $item->slug]) }}"
                class="group block">

                <div class="w-full h-44 bg-gray-200 overflow-hidden">
                    <img src="{{ $thumbnail }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>

                <!-- Title -->
                <h3 class="mt-3 font-semibold text-gray-800 text-base group-hover:text-red-600 transition font-sans">
                    {{ $title }}
                </h3>

                <!-- Excerpt -->
                <p class="text-sm text-gray-600 mt-1 leading-relaxed font-open">
                    {{ $excerpt }}
                </p>

            </a>

            @endforeach
        </div>
    </section>
</div>