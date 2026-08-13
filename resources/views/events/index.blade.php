@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Event / Kejadian</h2>
            <a href="{{ route('events.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
                + Tambah Event
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-green-100 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter & Search --}}
        <form method="GET" action="{{ route('events.index') }}"
              class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Title / Lokasi</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari event..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Orientation</label>
                <select name="orientation" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Semua</option>
                    <option value="landscape" @selected(request('orientation') === 'landscape')>Landscape</option>
                    <option value="horizontal" @selected(request('orientation') === 'horizontal')>Horizontal</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg">Filter</button>
                <a href="{{ route('events.index') }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
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
                <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if ($event->image_id_url)
                                    <img src="{{ $event->image_id_url }}" alt="{{ $event->title_id }}"
                                         class="w-24 h-16 object-cover rounded border border-gray-200">
                                @else
                                    <div class="w-24 h-16 rounded border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400 text-xs">Tanpa gambar</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $event->title_id }}
                                @if ($event->has_video)
                                    <span class="ml-1 inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700"
                                          title="Ada video">VIDEO</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $event->title_en }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $event->event_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 max-w-xs truncate">{{ $event->location }}</td>
                            <td class="px-4 py-3">
                                @if ($event->orientation === \App\Enums\EventOrientation::Landscape)
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Landscape</span>
                                @else
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Horizontal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $event->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap space-x-1">
                                <a href="{{ route('events.show', $event) }}"
                                   class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">Detail</a>
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
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                Belum ada event. <a href="{{ route('events.create') }}" class="text-blue-600 underline">Buat event pertama</a>.
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
