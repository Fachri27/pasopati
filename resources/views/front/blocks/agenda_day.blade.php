@php $ptop = $data['mt'] ?? null; $pbot = $data['mb'] ?? null; @endphp
<div @if($ptop || $pbot) style="margin-top: {{ $ptop }}px; margin-bottom: {{ $pbot }}px;" @endif
     class="max-w-2xl mx-auto px-5 my-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
        Agenda {{ \Carbon\Carbon::parse($data['day'] ?? '')->locale('id')->isoFormat('dddd, D MMMM YYYY') ?? $data['day'] ?? '' }}
    </h3>
    <div class="space-y-4">
        @foreach ($data['sessions'] ?? [] as $session)
            <div class="border-l-4 border-blue-600 pl-4 py-2">
                <div class="text-sm font-mono text-blue-700 font-semibold">{{ $session['time'] ?? '' }}</div>
                <h4 class="text-base font-bold text-gray-900">{{ $session['title'] ?? '' }}</h4>
                @if (!empty($session['description']))
                    <p class="text-sm text-gray-600 mt-1">{{ $session['description'] }}</p>
                @endif
                <div class="text-xs text-gray-500 mt-2 space-y-0.5">
                    @if (!empty($session['moderator']))
                        <div><strong>Moderator:</strong> {{ $session['moderator'] }}</div>
                    @endif
                    @if (!empty($session['commentator']))
                        <div><strong>Komentator:</strong> {{ $session['commentator'] }}</div>
                    @endif
                    @if (!empty($session['speakers']))
                        <div><strong>Pembicara:</strong> {{ implode(', ', (array) $session['speakers']) }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
