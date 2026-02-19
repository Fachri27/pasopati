<div x-data="{ lang: 'id' }">
    <div class="max-w-7xl mx-auto bg-white py-8 mb-20 px-8 rounded-xl shadow-md">

        {{-- ================= BREADCRUMB ================= --}}
        <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
            <a href="{{ route('fellowship.index') }}" class="text-gray-800 hover:text-blue-600 font-medium">
                Page Fellowship
            </a>

            <span class="text-gray-400">›</span>

            <span class="text-blue-600 font-semibold">
                {{ isset($fellowship) ? '✏️ Edit Fellowship' : '➕ Add Fellowship' }}
            </span>
        </nav>


        {{-- ================= TITLE ================= --}}
        <h1 class="text-2xl font-bold mb-8 text-gray-700">
            {{ isset($fellowship) ? '✏️ Edit Fellowship' : '➕ Add Fellowship' }}
        </h1>


        {{-- ================= FORM ================= --}}
        <form wire:submit.prevent="save">

            <div class="grid grid-cols-12 gap-6">

                {{-- =====================================================
                |  LEFT COLUMN
                ===================================================== --}}
                <div class="col-span-12 lg:col-span-4">

                    <div class="bg-gray-50 border rounded-xl p-5 space-y-5 sticky top-6">

                        {{-- ================= LANGUAGE ================= --}}
                        <div>
                            <label class="font-medium">🌐 Bahasa</label>

                            <select x-model="lang" class="w-full border rounded-lg px-3 py-2 mt-1">
                                <option value="id">Indonesia</option>
                                <option value="en">English</option>
                            </select>
                        </div>


                        {{-- ================= TITLE ID ================= --}}
                        <div x-show="lang === 'id'">
                            <label class="font-medium">Title (ID)</label>

                            <input
                                type="text"
                                wire:model="title_id"
                                class="w-full border rounded-lg px-3 py-2 mt-1">
                        </div>


                        {{-- ================= TITLE EN ================= --}}
                        <div x-show="lang === 'en'">
                            <label class="font-medium">Title (EN)</label>

                            <input
                                type="text"
                                wire:model="title_en"
                                class="w-full border rounded-lg px-3 py-2 mt-1">
                        </div>


                        {{-- ================= SUB JUDUL ================= --}}
                        <div x-show="lang === 'id'">
                            <label class="font-medium">Sub Judul (ID)</label>
                            <input type="text" wire:model="sub_judul_id" class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <div x-show="lang === 'en'">
                            <label class="font-medium">Sub Judul (EN)</label>
                            <input type="text" wire:model="sub_judul_en" class="w-full border rounded-lg px-3 py-2">
                        </div>


                        {{-- ================= DATE ================= --}}
                        <div class="grid grid-cols-2 gap-3">

                            <div>
                                <label class="text-sm">📅 Mulai</label>
                                <input type="date" wire:model="start_date" class="w-full border rounded-lg px-2 py-2">
                            </div>

                            <div>
                                <label class="text-sm">📅 Selesai</label>
                                <input type="date" wire:model="end_date" class="w-full border rounded-lg px-2 py-2">
                            </div>

                        </div>


                        {{-- ================= STATUS ================= --}}
                        <div>
                            <label class="font-medium">Status</label>

                            <select wire:model="status" class="w-full border rounded-lg px-3 py-2">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>



                        {{-- =====================================================
                        |  IMAGES (PER LOCALE)
                        ===================================================== --}}
                        <div class="border-t pt-4 space-y-5">

                            <h3 class="font-semibold text-gray-700">🖼 Images</h3>


                            {{-- ================= INDONESIA ================= --}}
                            <div x-show="lang === 'id'" class="space-y-4">

                                {{-- Image --}}
                                <div>
                                    <label class="font-medium">Image (Indonesia)</label>

                                    <input type="file" wire:model="image_id"
                                           class="w-full border rounded-lg px-3 py-2 mt-1">

                                    <div class="mt-3">
                                        @if ($image_id)
                                            <img src="{{ $image_id->temporaryUrl() }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">

                                        @elseif ($old_image_id)
                                            <img src="{{ asset('storage/'.$old_image_id) }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">
                                        @endif
                                    </div>
                                </div>


                                {{-- Cover --}}
                                <div>
                                    <label class="font-medium">Image Cover (Indonesia)</label>

                                    <input type="file" wire:model="image_cover_id"
                                           class="w-full border rounded-lg px-3 py-2 mt-1">

                                    <div class="mt-3">
                                        @if ($image_cover_id)
                                            <img src="{{ $image_cover_id->temporaryUrl() }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">

                                        @elseif ($old_image_cover_id)
                                            <img src="{{ asset('storage/'.$old_image_cover_id) }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">
                                        @endif
                                    </div>
                                </div>

                            </div>



                            {{-- ================= ENGLISH ================= --}}
                            <div x-show="lang === 'en'" class="space-y-4">

                                {{-- Image --}}
                                <div>
                                    <label class="font-medium">Image (English)</label>

                                    <input type="file" wire:model="image_en"
                                           class="w-full border rounded-lg px-3 py-2 mt-1">

                                    <div class="mt-3">
                                        @if ($image_en)
                                            <img src="{{ $image_en->temporaryUrl() }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">

                                        @elseif ($old_image_en)
                                            <img src="{{ asset('storage/'.$old_image_en) }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">
                                        @endif
                                    </div>
                                </div>


                                {{-- Cover --}}
                                <div>
                                    <label class="font-medium">Image Cover (English)</label>

                                    <input type="file" wire:model="image_cover_en"
                                           class="w-full border rounded-lg px-3 py-2 mt-1">

                                    <div class="mt-3">
                                        @if ($image_cover_en)
                                            <img src="{{ $image_cover_en->temporaryUrl() }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">

                                        @elseif ($old_image_cover_en)
                                            <img src="{{ asset('storage/'.$old_image_cover_en) }}"
                                                 class="w-24 h-24 object-cover rounded-lg border">
                                        @endif
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>



                {{-- =====================================================
                |  RIGHT COLUMN
                ===================================================== --}}
                <div class="col-span-12 lg:col-span-8 space-y-6">

                    {{-- Excerpt --}}
                    <div class="bg-white border rounded-xl p-4">
                        <h3 class="font-semibold mb-3">Excerpt</h3>

                        <div x-show="lang === 'id'">
                            @include('front.partials.fellowship.tinymce-excerpt-id')
                        </div>

                        <div x-show="lang === 'en'">
                            @include('front.partials.fellowship.tinymce-excerpt-en')
                        </div>
                    </div>


                    {{-- Content --}}
                    <div class="bg-white border rounded-xl p-4">
                        <h3 class="font-semibold mb-3">Content</h3>

                        @include('front.partials.fellowship.tinymce-content-id')
                    </div>

                </div>



                {{-- ================= SAVE BUTTON ================= --}}
                <div class="col-span-12 sticky bottom-0 bg-white border-t p-4 flex justify-end">

                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60
                                   text-white px-6 py-2 rounded-lg font-medium">

                        {{ isset($fellowship) ? '💾 Update' : '🚀 Create' }}
                    </button>

                </div>

            </div>

        </form>
    </div>
</div>
