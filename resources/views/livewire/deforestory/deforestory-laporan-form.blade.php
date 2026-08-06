<div>
    <div class="my-6" x-data="{ lang: 'id' }">
        <div class="max-w-7xl mx-auto bg-white py-8 mb-20 px-8 rounded-xl shadow-md">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
                <a href="{{ route('deforestory.index') }}" class="text-gray-800 hover:text-blue-600 font-medium">Deforestory</a>
                <span class="text-gray-400">›</span>
                <a href="{{ route('deforestory.laporan.index', ['caseSlug' => $caseSlug]) }}" class="text-gray-800 hover:text-blue-600 font-medium">Laporan /{{ $caseSlug }}</a>
                <span class="text-gray-400">›</span>
                <span class="text-blue-600 font-semibold">{{ $laporan ? '✏️ Edit' : '➕ Tambah' }}</span>
            </nav>

            <h1 class="text-2xl font-bold mb-8 text-gray-700">
                {{ $laporan ? '✏️ Edit Laporan' : '➕ Tambah Laporan' }}
                <span class="text-gray-400 font-normal text-base">— kasus /{{ $caseSlug }}</span>
            </h1>

            <form wire:submit.prevent="save">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="grid grid-cols-12 gap-6">

                    {{-- ================= LEFT COLUMN ================= --}}
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

                            {{-- Title ID + Slug --}}
                            <div x-show="lang === 'id'" x-data="{
                                title: @js(old('title_id', $title_id ?? '')),
                                slug: @js(old('slug', $slug ?? '')),
                                makeSlug(text) {
                                    return text.toLowerCase()
                                        .replace(/[^a-z0-9]+/g, '-')
                                        .replace(/^-+|-+$/g, '');
                                }
                            }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                                <label class="font-medium">Judul Laporan (ID)</label>
                                <input type="text" wire:model="title_id" x-model="title" @input="slug = makeSlug(title)"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                                @error('title_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                <label class="text-sm mt-2 block">Slug (URL detail)</label>
                                <input type="text" x-model="slug" wire:model="slug" readonly
                                    class="w-full bg-gray-100 border rounded-lg px-3 py-2 font-mono text-sm">
                                @error('slug') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <p class="text-xs text-gray-400 mt-1">URL: /deforestory/{{ $caseSlug }}/<span x-text="slug"></span></p>
                            </div>

                            {{-- Title EN --}}
                            <div x-show="lang === 'en'">
                                <label class="font-medium">Report Title (EN)</label>
                                <input type="text" wire:model="title_en"
                                    class="w-full border rounded-lg px-3 py-2 mt-1">
                                @error('title_en') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <p class="text-xs text-gray-400 mt-2">Slug mengikuti judul ID (kiri).</p>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label class="font-medium">Status</label>
                                <select wire:model="status" class="w-full border rounded-lg px-3 py-2">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            {{-- Sort --}}
                            <div>
                                <label class="text-sm">Urutan</label>
                                <input type="number" wire:model="sort" class="w-full border rounded-lg px-3 py-2">
                            </div>

                            {{-- Tanggal publikasi --}}
                            <div>
                                <label class="text-sm">Tanggal Publikasi</label>
                                <input type="date" wire:model="published_at"
                                    class="w-full border rounded-lg px-3 py-2">
                                @error('published_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <p class="text-xs text-gray-400 mt-1">Tanggal terbit laporan (tampil di API &amp; halaman detail).</p>
                            </div>

                            {{-- Image (per-locale: id + en) --}}
                            <div>
                                <label class="font-medium">Gambar Laporan (per bahasa)</label>

                                <div x-show="lang === 'id'">
                                    <input type="file" wire:model="image_id"
                                        class="w-full border rounded-lg px-2 py-2 mt-1">
                                    <div class="mt-3">
                                        @if ($image_id)
                                            <img src="{{ $image_id->temporaryUrl() }}"
                                                class="w-20 h-20 rounded-lg object-cover border">
                                        @elseif ($old_image_id)
                                            <img src="{{ asset('storage/' . $old_image_id) }}"
                                                class="w-20 h-20 rounded-lg object-cover border">
                                        @endif
                                    </div>
                                </div>

                                <div x-show="lang === 'en'">
                                    <input type="file" wire:model="image_en"
                                        class="w-full border rounded-lg px-2 py-2 mt-1">
                                    <div class="mt-3">
                                        @if ($image_en)
                                            <img src="{{ $image_en->temporaryUrl() }}"
                                                class="w-20 h-20 rounded-lg object-cover border">
                                        @elseif ($old_image_en)
                                            <img src="{{ asset('storage/' . $old_image_en) }}"
                                                class="w-20 h-20 rounded-lg object-cover border">
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ================= RIGHT COLUMN ================= --}}
                    <div class="col-span-12 lg:col-span-8 space-y-6">

                        {{-- Excerpt --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Excerpt / Deskripsi</h3>
                            <p class="text-xs text-gray-500 mb-2">Ringkasan singkat yang tampil di kartu arsip kasus.</p>

                            <div x-show="lang === 'id'">
                                @includeWhen(true, 'front.partials.tinymce-excerpt-id')
                            </div>
                            <div x-show="lang === 'en'">
                                @includeWhen(true, 'front.partials.tinymce-excerpt-en')
                            </div>
                        </div>

                        {{-- Isi Laporan --}}
                        <div class="bg-white border rounded-xl p-4">
                            <h3 class="font-semibold mb-3">Isi Laporan</h3>
                            <p class="text-xs text-gray-500 mb-2">Konten utama laporan. Tampil di halaman detail.</p>

                            <div x-show="lang === 'id'">
                                @includeWhen(true, 'front.partials.tinymce-deforestory-laporan-id')
                            </div>
                            <div x-show="lang === 'en'">
                                @includeWhen(true, 'front.partials.tinymce-deforestory-laporan-en')
                            </div>
                        </div>

                    </div>

                    {{-- SAVE --}}
                    <div class="col-span-12 sticky bottom-0 bg-white border-t p-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" class="relative bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed
               text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">

                            <svg wire:loading wire:target="save" class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>

                            <span wire:loading.remove wire:target="save">
                                {{ $laporan ? '💾 Update' : '🚀 Create' }}
                            </span>

                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>