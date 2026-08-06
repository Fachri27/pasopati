@php $ptop = $data['mt'] ?? null; $pbot = $data['mb'] ?? null; @endphp
<div @if($ptop || $pbot) style="margin-top: {{ $ptop }}px; margin-bottom: {{ $pbot }}px;" @endif
     class="max-w-2xl mx-auto px-5 my-8">
    <div class="border-2 border-red-500 rounded-xl p-6 bg-red-50">
        <h3 class="text-xl font-bold text-red-800 mb-4 uppercase tracking-wide">Info Acara</h3>
        <table class="w-full text-sm">
            <tr>
                <td class="font-semibold text-gray-700 w-28 align-top py-1">Format</td>
                <td class="py-1">{{ $data['format'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="font-semibold text-gray-700 align-top py-1">Tanggal</td>
                <td class="py-1">{{ $data['date'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="font-semibold text-gray-700 align-top py-1">Waktu</td>
                <td class="py-1">{{ $data['time'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="font-semibold text-gray-700 align-top py-1">Tempat</td>
                <td class="py-1">{{ $data['venue'] ?? '-' }}</td>
            </tr>
            @if (!empty($data['registration_links']))
            <tr>
                <td class="font-semibold text-gray-700 align-top py-1">Registrasi</td>
                <td class="py-1">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($data['registration_links'] as $link)
                            <li><a href="{{ $link['url'] ?? '#' }}" target="_blank" class="text-blue-600 underline">{{ $link['day'] ?? 'Registrasi' }}</a></li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endif
        </table>
        @if (!empty($data['notes']))
            <div class="mt-3 pt-3 border-t border-red-200 text-sm text-gray-600">
                <strong>Catatan:</strong> {{ $data['notes'] }}
            </div>
        @endif
    </div>
</div>
