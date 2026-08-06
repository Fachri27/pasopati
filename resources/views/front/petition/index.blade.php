@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-5 py-10 pt-20">
    <h1 class="text-3xl font-bold text-gray-900 mb-2 font-serif">
        {{ $locale === 'id' ? 'Petisi' : 'Petitions' }}
    </h1>
    <p class="text-gray-500 mb-10">
        {{ $locale === 'id' ? 'Tanda tangani petisi dan buat perubahan' : 'Sign petitions and make a change' }}
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($petitions as $petition)
            @php
                $trans = $petition->translation($locale);
                $title = $trans?->title ?? $petition->slug;
                $desc = $trans?->description ?? '';
                $progress = $petition->progressPercent();
                $count = $petition->signatureCount();
            @endphp
            <a href="{{ route('petition.show', ['locale' => $locale, 'slug' => $petition->slug]) }}"
                class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                @if ($petition->cover_image)
                    <div class="aspect-[16/9] overflow-hidden">
                        <img src="{{ asset('storage/' . $petition->cover_image) }}" alt="{{ $title }}"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </div>
                @endif
                <div class="p-5">
                    <h2 class="font-semibold text-lg text-gray-900 group-hover:text-[#2B5343] transition">{{ $title }}</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $locale === 'id' ? 'Ditujukan kepada' : 'Addressed to' }}: <span class="font-medium text-gray-700">{{ $petition->target_name }}</span>
                    </p>
                    @if ($desc)
                        <p class="text-sm text-gray-600 mt-3 line-clamp-2">{{ strip_tags($desc) }}</p>
                    @endif
                    <div class="mt-4">
                        <div class="flex justify-between text-sm text-gray-500 mb-1">
                            <span>{{ number_format($count) }} / {{ number_format($petition->goal_count) }}</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-[#2B5343] h-2.5 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-20 text-gray-400">
                <p class="text-lg">{{ $locale === 'id' ? 'Belum ada petisi aktif.' : 'No active petitions yet.' }}</p>
            </div>
        @endforelse
    </div>

    @if ($petitions->hasPages())
        <div class="mt-10">
            {{ $petitions->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>
@endsection
