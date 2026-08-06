<div>
    <div class="my-6" x-data="{ lang: 'id' }">
        <div class="max-w-7xl mx-auto bg-white py-8 mb-20 px-8 rounded-xl shadow-md">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
                <a href="{{ route('petition.admin.index') }}" class="text-gray-800 hover:text-blue-600 font-medium">
                    Petisi
                </a>
                <span class="text-gray-400">›</span>
                <span class="text-blue-600 font-semibold">
                    {{ $petition ? 'Edit Petisi' : 'Tambah Petisi' }}
                </span>
            </nav>

            <h1 class="text-2xl font-bold mb-8 text-gray-700">
                {{ $petition ? '✏️ Edit Petisi' : '➕ Tambah Petisi' }}
            </h1>

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-12 gap-6">

                    {{-- LEFT COLUMN --}}
                    <div class="col-span-12 lg:col-span-4">
                        <div class="bg-gray-50 border rounded-xl p-5 space-y-4 sticky top-6">

                            {{-- Language --}}
                            <div>
                                <label class="font-medium">🌐 Bahasa</label>
                                <select x-model="lang" class="w-full border rounded-lg px-3 py-2 mt-1">
                                    <option value="id">Indonesia</option>
                                    <option value="en">English</option>
                                </select>
                            </div>

                            {{-- Title ID --}}
                            <div x-show="lang === 'id'">
                                <label class="font-medium">Judul (ID)</label>
                                <input type="text" wire:model="title_id" wire:input="updateTitleId($event.target.value)"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                            </div>

                            {{-- Title EN --}}
                            <div x-show="lang === 'en'">
                                <label class="font-medium">Judul (EN)</label>
                                <input type="text" wire:model="title_en"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                            </div>

                            {{-- Target Name --}}
                            <div>
                                <label class="font-medium">Ditujukan Kepada</label>
                                <input type="text" wire:model="target_name" placeholder="Contoh: Presiden RI"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                            </div>

                            {{-- Goal Count --}}
                            <div>
                                <label class="font-medium">Target Tanda Tangan</label>
                                <input type="number" wire:model="goal_count" min="1"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                            </div>

                            {{-- Publish Date --}}
                            <div>
                                <label class="text-sm">📅 Tanggal Publikasi</label>
                                <input type="date" wire:model="published_at"
                                    class="w-full border rounded-lg px-2 py-2 mt-1">
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="font-medium">Status</label>
                                <select wire:model="status" class="w-full border rounded-lg px-3 py-2 mt-1">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="closed">Closed</option>
                                    <option value="succeeded">Succeeded</option>
                                </select>
                            </div>

                            {{-- Cover Image --}}
                            <div>
                                <label class="font-medium">Gambar Sampul</label>
                                <input type="file" wire:model="cover_image"
                                    class="w-full border rounded-lg px-2 py-2 mt-1">
                                <div class="mt-3">
                                    @if ($cover_image)
                                        <img src="{{ $cover_image->temporaryUrl() }}"
                                            class="w-full h-32 rounded-lg object-cover border">
                                    @elseif ($old_cover_image)
                                        <img src="{{ asset('storage/' . $old_cover_image) }}"
                                            class="w-full h-32 rounded-lg object-cover border">
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="col-span-12 lg:col-span-8 space-y-6">

                        {{-- Description --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Deskripsi</h3>

                            <div x-show="lang === 'id'">
                                <textarea wire:model="description_id" rows="10"
                                    class="w-full border rounded-lg px-3 py-2">{{ $description_id }}</textarea>
                            </div>
                            <div x-show="lang === 'en'">
                                <textarea wire:model="description_en" rows="10"
                                    class="w-full border rounded-lg px-3 py-2">{{ $description_en }}</textarea>
                            </div>
                        </div>

                        {{-- Demands --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Poin Tuntutan</h3>

                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="demandInput" wire:keydown.enter="addDemand"
                                    placeholder="Tambah poin tuntutan..."
                                    class="w-full border rounded-lg px-3 py-2">
                                <button type="button" wire:click="addDemand"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    +
                                </button>
                            </div>

                            <ul class="space-y-2">
                                @foreach ($demands as $index => $demand)
                                    <li class="flex items-start gap-2 bg-gray-50 p-2 rounded">
                                        <span class="text-blue-600 mt-1">•</span>
                                        <span class="flex-1 text-sm">{{ $demand }}</span>
                                        <button type="button" wire:click="removeDemand({{ $index }})"
                                            class="text-red-500 hover:text-red-700 text-sm">✕</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>

                    {{-- SAVE --}}
                    <div class="col-span-12 sticky bottom-0 bg-white border-t p-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                            class="relative bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">

                            <svg wire:loading wire:target="save" class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>

                            <span wire:loading.remove wire:target="save">
                                {{ $petition ? '💾 Update' : '🚀 Create' }}
                            </span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
