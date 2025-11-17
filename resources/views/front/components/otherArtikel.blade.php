    <div class="max-w-screen-lg mx-auto">
    <section class="mt-12 mx-4">
        <!-- Judul -->
        <div class="flex items-center mb-6">
            <h2 class="text-red-600 font-bold uppercase text-sm tracking-wide">
                Artikel Lainnya
            </h2>
            <div class="flex-1 h-px bg-gray-300 ml-4"></div>
        </div>

        <!-- Grid artikel -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($related as $item)

            @php
                $trans = $item->translations->where('locale', $locale)->first();
                $thumbnail = $item->featured_image 
                    ? asset('storage/' . $item->featured_image) 
                    : '/images/default.jpg';

                $title = $trans->title ?? $item->title;
                $excerpt = Str::limit(strip_tags($trans->content ?? ''), 130);
            @endphp
            <div class="flex flex-col">
                <div class="bg-gray-200">
                    <img src="{{ asset('/storage'.$item->featured_image) }}" alt="Artikel 1" class="w-full h-40 object-cover">
                </div>
                <div class="mt-2 text-sm font-semibold">
                    {!! $excerpt !!}
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>