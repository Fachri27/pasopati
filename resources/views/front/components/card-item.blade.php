@foreach ($pages as $page)
<div class="flex flex-col gap-2">
    <time class="text-xs text-gray-500">
        {{ \Carbon\Carbon::parse($page->published_at)->translatedFormat('d F Y') }}
    </time>
    <article class="group bg-[#e3061c] shadow-sm hover:shadow-xl flex flex-col min-h-[28rem]">
        <div class="overflow-hidden aspect-[16/9]">
            @if ($page->featured_image)
            <img src="{{ asset('storage/'. $page->featured_image) }}"
                alt="{{ $page->translations->first()->title ?? '' }}"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
            @else
            <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500">
                No Image
            </div>
            @endif
        </div>
        <div class="p-4 flex flex-col flex-grow text-white">
            <a
                href="{{ route('show-page', ['locale' => app()->getLocale(), 'page_type' => $page->page_type, 'slug' => $page->slug]) }}">
                <h3 class="text-xl font-semibold mb-2 leading-snug font-sans">
                    {{ $page->translations->first()->title ?? '' }}
                </h3>
            </a>
            <div
                class="prose prose-sm text-white max-w-none opacity-90 prose-p:leading-snug prose-p:my-1 font-open flex-grow">
                {!! $page->translations->first()->excerpt ?? '' !!}
            </div>
        </div>
    </article>
</div>
@endforeach