<div>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-sm text-gray-500">Petisi Aktif</p>
                <p class="text-3xl font-bold text-blue-600">{{ $totalActive }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-sm text-gray-500">Total Tanda Tangan</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($totalSignaturesAll) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <p class="text-sm text-gray-500">Tanda Tangan Bulan Ini</p>
                <p class="text-3xl font-bold text-amber-600">{{ number_format($totalSignaturesThisMonth) }}</p>
            </div>
        </div>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Petisi</h1>
            <a href="{{ route('petition.admin.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                + Tambah Petisi
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari petisi..."
                        class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <select wire:model.live="filterStatus" class="border rounded-lg px-3 py-2">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                        <option value="succeeded">Succeeded</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                        <th class="px-6 py-3">Judul</th>
                        <th class="px-6 py-3">Target</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Progress</th>
                        <th class="px-6 py-3">Ttd</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($petitions as $petition)
                        @php
                            $locale = app()->getLocale();
                            $title = $petition->translation($locale)?->title ?? $petition->slug;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $title }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $petition->target_name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                    @switch($petition->status)
                                        @case('draft') bg-gray-200 text-gray-700 @break
                                        @case('active') bg-green-100 text-green-700 @break
                                        @case('closed') bg-red-100 text-red-700 @break
                                        @case('succeeded') bg-blue-100 text-blue-700 @break
                                    @endswitch">
                                    {{ ucfirst($petition->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $petition->progressPercent() }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $petition->progressPercent() }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($petition->signatureCount()) }}</td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('petition.admin.edit', $petition->id) }}"
                                    class="text-yellow-600 hover:text-yellow-800">Edit</a>
                                <a href="{{ route('petition.admin.signatures', $petition->id) }}"
                                    class="text-blue-600 hover:text-blue-800">Ttd</a>
                                <a href="{{ route('petition.admin.export-pdf', $petition->id) }}"
                                    class="text-green-600 hover:text-green-800">PDF</a>
                                <button wire:click="delete({{ $petition->id }})"
                                    wire:confirm="Hapus petisi ini?"
                                    class="text-red-600 hover:text-red-800">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                Belum ada petisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $petitions->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
