@php $ptop = $data['mt'] ?? null; $pbot = $data['mb'] ?? null; @endphp
<div @if($ptop || $pbot) style="margin-top: {{ $ptop }}px; margin-bottom: {{ $pbot }}px;" @endif
     class="max-w-2xl mx-auto px-5 my-10">
    <blockquote class="border-l-4 border-red-500 pl-6 py-2 italic text-lg text-gray-700 leading-relaxed">
        <p>"{{ $data['text'] ?? '' }}"</p>
        @if (!empty($data['source']))
            <cite class="text-sm not-italic text-gray-500 block mt-2">— {{ $data['source'] }}</cite>
        @endif
    </blockquote>
</div>
