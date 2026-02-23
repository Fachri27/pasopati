<div class="flex flex-col justify-center items-center">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between ">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Daftar Halaman</h2>
            <a href="{{ route('fellowship.create') }}">
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                    🚀 Create
                </button>
            </a>
        </div>
        <div class="flex items-center justify-between mb-4">
            <input type="text" wire:model.live.debounce.100ms="search" placeholder="Search..." class="border p-2">
        </div>

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <label for="statusFilter" class="text-sm font-medium text-gray-700">Filter Status:</label>
                <select id="statusFilter" wire:model.live.debounce.100ms="status"
                    class="border-gray-300 rounded-md text-sm">
                    <option value="">Semua</option>
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label for="userFilter" class="text-sm font-medium text-gray-700">Filter Author:</label>
                <select id="userFilter" class="border-gray-300 rounded-md text-sm">
                    <option value="">Semua Penulis</option>
                    <option value="me">Saya</option>
                </select>
            </div>
            <div x-data="{
                dateRange: @entangle('dataRange').defer,
                picker: null,
                init() {
                    this.picker = flatpickr(this.$refs.dateRange, {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        locale: { rangeSeparator: ' to ' },
                        onChange: (selectedDates, dateStr) => {
                            this.dateRange = dateStr
                            $wire.set('dataRange', dateStr)
                        }
                    })
                },
                resetDate() {
                    this.$refs.dateRange._flatpickr.clear()
                    this.dateRange = ''
                    $wire.set('dataRange', null)
                }
            }">
                <input x-ref="dateRange" type="text" class="border rounded p-2 w-64" placeholder="Pilih tanggal..."
                    readonly>

                <button type="button" @click="resetDate"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-2 rounded">
                    Reset
                </button>

            </div>

            <!-- Search bawaan DataTables -->
            <div id="tableSearch" class="flex items-center gap-2"></div>
        </div>
        <div class="overflow-x-auto">
            <table id="fellowshipTable" class="min-w-full border-collapse table-fixed">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                        <th class="p-3">Title</th>
                        <th class="p-3">Image</th>
                        <th class="p-3">Start Date</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Author</th>
                        <th class="p-3">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @forelse ($fellowship as $data )
                    @php
                    $idTranslation = $data->translations->firstWhere('locale', 'id');
                    @endphp
                    <tr>
                        <td class="p-3"><span class="text-sm leading-snug text-gray-800 block max-w-xs">{{
                                $idTranslation->title }}</span></td>
                        <td class="p-3">
                            <img src="{{ asset('storage/' . $idTranslation->image) }}" alt="{{ $idTranslation->title }}"
                                class="w-30 h-15">
                        </td>
                        <td class="p-3">{{ $data->start_date }}</td>
                        <td class="p-3">
                            @if ($data->status === 'active')
                            <span
                                class="inline-block px-3 py-1 text-white text-xs font-semibold rounded-full bg-green-500">
                                active
                            </span>
                            @elseif ($data->status === 'draft')
                            <span
                                class="inline-block px-3 py-1 text-white text-xs font-semibold rounded-full bg-yellow-500">
                                draft
                            </span>
                            @else
                            <span
                                class="inline-block px-3 py-1 text-white text-xs font-semibold rounded-full bg-gray-500">
                                inactive
                            </span>
                            @endif
                        </td>
                        <td class="p-3">
                            {{auth()->user()->name}}
                        </td>
                        <td class="p-3">
                            <a href="{{ route('fellowship.preview', ['locale' => 'id', 'slug' => $data->slug]) }}"
                                target="_blank">
                                <button class="bg-gray-600 px-3 py-1 rounded text-white">Preview</button>
                            </a>
                            <a href="{{ route('fellowship.edit', $data->id) }}">
                                <button class="bg-yellow-600 px-3 py-1 rounded text-white">Edit</button>
                            </a>
                            <button wire:click='delete({{ $data->id }})'
                                class="bg-red-600 px-3 py-1 rounded text-white">delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="p-3" colspan="4">
                            Data tidak ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tbody></tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="mt-4">
            {{ $fellowship->links() }}
        </div>
    </div>
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 bg-green-400 text-white p-10 shadow-lg 
               hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
        {{ session('success') }}
    </div>
    @endif
</div>