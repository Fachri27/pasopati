@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="bg-admin-surface shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-admin-smoke">Event / Kejadian</h2>
            <a href="{{ route('events.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
                + Tambah Event
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter & Search --}}
        <form method="GET" action="{{ route('events.index') }}"
              class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <div>
                <label class="block text-sm font-medium text-admin-ash mb-1">Cari Title / Lokasi</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari event..."
                       class="admin-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-admin-ash mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="admin-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-admin-ash mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="admin-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-admin-ash mb-1">Orientation</label>
                <select name="orientation" class="admin-input">
                    <option value="">Semua</option>
                    <option value="landscape" @selected(request('orientation') === 'landscape')>Landscape</option>
                    <option value="horizontal" @selected(request('orientation') === 'horizontal')>Horizontal</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="bg-admin-raised hover:bg-admin-raised text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="{{ route('events.index') }}"
                   class="bg-admin-raised hover:bg-admin-line text-admin-smoke px-4 py-2 rounded-lg">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-admin-raised">
                    <tr class="text-left text-xs font-semibold text-admin-ash uppercase tracking-wider">
                        <th class="px-4 py-3">Thumbnail</th>
                        <th class="px-4 py-3">Title (ID)</th>
                        <th class="px-4 py-3">Title (EN)</th>
                        <th class="px-4 py-3">Tanggal Kejadian</th>
                        <th class="px-4 py-3">Lokasi</th>
                        <th class="px-4 py-3">Orientation</th>
                        <th class="px-4 py-3">Dibuat</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-admin-surface divide-y divide-gray-100 text-sm text-admin-ash">
                    @forelse ($events as $event)
                        <tr class="hover:bg-admin-raised">
                            <td class="px-4 py-3">
                                @php $media = $event->media_items; @endphp
                                @if (count($media))
                                    <div class="flex items-center gap-2 overflow-x-auto max-w-[200px] pb-1">
                                        @foreach (array_slice($media, 0, 3) as $item)
                                            @if ($item['type'] === 'video')
                                                <div class="relative shrink-0 w-16 h-10 rounded border border-admin-line bg-black overflow-hidden">
                                                    <video src="{{ $item['url'] }}" preload="metadata" class="w-full h-full object-cover"></video>
                                                    <span class="absolute inset-0 flex items-center justify-center text-[8px] font-bold text-white bg-black/40">▶</span>
                                                </div>
                                            @else
                                                <img src="{{ $item['url'] }}" alt="{{ $event->title_id }}" class="shrink-0 w-16 h-10 object-cover rounded border border-admin-line">
                                            @endif
                                        @endforeach
                                        @if (count($media) > 3)
                                            <span class="shrink-0 text-[10px] text-admin-muted bg-admin-raised px-1.5 py-0.5 rounded">+{{ count($media) - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="w-24 h-16 rounded border border-dashed border-admin-line bg-admin-raised flex items-center justify-center text-admin-muted text-xs">Tanpa gambar</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-admin-smoke">
                                {{ $event->title_id }}
                                @if ($event->has_video || collect($media)->contains('type', 'video'))
                                    <span class="ml-1 inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-red-500/10 text-red-400"
                                          title="Ada video">VIDEO</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $event->title_en }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $event->event_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 max-w-xs truncate">{{ $event->location }}</td>
                            <td class="px-4 py-3">
                                @if ($event->orientation === \App\Enums\EventOrientation::Landscape)
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-500/10 text-blue-800">Landscape</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-violet-500/10 text-purple-800">Horizontal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $event->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap space-x-1">
                                <a href="{{ route('events.show', $event) }}"
                                   class="inline-block bg-admin-raised hover:bg-admin-amber/20 text-white px-3 py-1 rounded text-xs">Detail</a>
                                <a href="{{ route('events.edit', $event) }}"
                                   class="inline-block bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs">Edit</a>
                                <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus event ini? Gambar juga akan dihapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-admin-muted">
                                Belum ada event. <a href="{{ route('events.create') }}" class="text-blue-400 underline">Buat event pertama</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
