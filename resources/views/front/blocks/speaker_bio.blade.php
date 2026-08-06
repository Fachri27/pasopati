@php $ptop = $data['mt'] ?? null; $pbot = $data['mb'] ?? null; @endphp
<div @if($ptop || $pbot) style="margin-top: {{ $ptop }}px; margin-bottom: {{ $pbot }}px;" @endif
     class="max-w-2xl mx-auto px-5 my-8">
    <div class="flex gap-5 items-start bg-gray-50 rounded-xl p-5 border">
        @if (!empty($data['photo']))
            <div class="flex-shrink-0">
                <img src="{{ asset('storage/' . $data['photo']) }}"
                     alt="{{ $data['name'] ?? '' }}"
                     class="w-24 h-24 rounded-full object-cover border-2 border-white shadow">
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <h4 class="text-lg font-bold text-gray-900">{{ $data['name'] ?? '' }}</h4>
            @if (!empty($data['title']))
                <p class="text-sm text-red-700 font-semibold">{{ $data['title'] }}</p>
            @endif
            @if (!empty($data['bio']))
                <p class="text-sm text-gray-600 mt-2 leading-relaxed">{{ $data['bio'] }}</p>
            @endif
        </div>
    </div>
</div>
