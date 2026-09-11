<div>
    <div class="flex flex-col justify-center items-center">
        <div class="bg-admin-surface shadow rounded-lg p-6 overflow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-admin-smoke mb-4">Daftar Halaman</h2>
                <a href="{{ route('kategori.create') }}">
                    <button class="admin-btn admin-btn--primary">
                        Create
                    </button>
                </a>
            </div>
            <div class="flex items-center justify-between mb-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="border p-2">
            </div>
            <table id="kategoriTable" class="admin-table w-full border-collapse">
                <thead>
                    <tr class="bg-admin-raised text-left text-sm font-medium text-admin-ash">
                        <th class="p-3">No</th>
                        <th class="p-3">Nama Kategori (ID)</th>
                        <th class="p-3">Nama Kategori (EN)</th>
                        <th class="p-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kategori as $item => $index)
                    @php
                    $idKategori = $index->translations->firstWhere('locale', 'id')->kategori_name ?? "-";
                    $enKategori = $index->translations->firstWhere('locale', 'en')->kategori_name ?? "-";
                    @endphp
                <tbody>
                    <tr>
                        <td class="p-3">{{ $item + 1 }}</td>
                        <td class="p-3">{{ $idKategori }}</td>
                        <td class="p-3">{{ $enKategori }}</td>
                        <td class="p-3">
                            <a href="{{ route('kategori.edit', $index->id) }}"
                                class="bg-admin-ember px-3 py-1 rounded text-white">Edit</a>
                            <button wire:click='delete({{ $index->id }})'
                                class="admin-btn admin-btn--danger admin-btn--sm">Delete</button>
                        </td>
                    </tr>
                </tbody>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{ $kategori->links('vendor.pagination.admin') }}
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
        class="fixed bottom-6 right-6 bg-admin-amber text-white p-3 shadow-lg hover:bg-admin-red/85 focus:outline-none focus:ring-2 focus:ring-admin-amber focus:ring-offset-2">
        {{ session('success') }}
    </div>
    @endif
</div>