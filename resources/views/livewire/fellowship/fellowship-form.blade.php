<div x-data="{ lang: 'id' }">
    <div class="max-w-7xl mx-auto bg-white py-8 mb-20 px-8 rounded-xl shadow-md">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-600 mb-6 flex items-center gap-2">
            <a href="{{ route('fellowship.index') }}" class="text-gray-800 hover:text-blue-600 font-medium">
                Page Fellowship
            </a>
            <span class="text-gray-400">›</span>
            <span class="text-blue-600 font-semibold">
                {{ isset($fellowship) ? '✏️ Edit Fellowship' : '➕ Add Fellowship' }}
            </span>
        </nav>

        <h1 class="text-2xl font-bold mb-8 text-gray-700">
            {{ isset($fellowship) ? '✏️ Edit Fellowship' : '➕ Add Fellowship' }}
        </h1>

        <form wire:submit.prevent="save">
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

                        {{-- Title ID --}}
                        <div x-show="lang === 'id'" x-data="{
                                title: @js(old('title_id', $title_id ?? '')),
                                slug: @js(old('slug', $slug ?? '')),
                                makeSlug(text) {
                                    return text.toLowerCase()
                                        .replace(/[^a-z0-9]+/g, '-')
                                        .replace(/^-+|-+$/g, '');
                                }
                             }" x-init="if(title && !slug){ slug = makeSlug(title) }">

                            <label class="font-medium">Title (ID)</label>
                            <input type="text" wire:model="title_id" x-model="title" @input="slug = makeSlug(title)"
                                class="w-full border rounded-lg px-3 py-2 mt-1">

                            <label class="text-sm mt-2 block">Slug</label>
                            <input type="text" x-model="slug" readonly
                                class="w-full bg-gray-100 border rounded-lg px-3 py-2">
                        </div>

                        {{-- Title EN --}}
                        <div x-show="lang === 'en'">
                            <label class="font-medium">Title (EN)</label>
                            <input type="text" wire:model="title_en" class="w-full border rounded-lg px-3 py-2 mt-1">
                        </div>

                        {{-- Sub Judul --}}
                        <div x-show="lang === 'id'">
                            <label class="font-medium">Sub Judul (ID)</label>
                            <input type="text" wire:model="sub_judul_id" class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <div x-show="lang === 'en'">
                            <label class="font-medium">Sub Judul (EN)</label>
                            <input type="text" wire:model="sub_judul_en" class="w-full border rounded-lg px-3 py-2">
                        </div>

                        {{-- Date --}}
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

                        {{-- Status --}}
                        <div>
                            <label class="font-medium">Status</label>
                            <select wire:model="status" class="w-full border rounded-lg px-3 py-2">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        {{-- Image --}}
                        <div>
                            <label class="font-medium">Image</label>
                            <input type="file" wire:model="image" class="w-full border rounded-lg px-3 py-2 mt-1">

                            <div class="mt-3">
                                @if (is_object($image) && $image)
                                <img src="{{ $image->temporaryUrl() }}"
                                    class="w-20 h-20 rounded-lg object-cover border">
                                @elseif (!empty($old_image))
                                <img src="{{ asset('storage/'.$old_image) }}"
                                    class="w-20 h-20 rounded-lg object-cover border">
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================= RIGHT COLUMN ================= --}}
                <div class="col-span-12 lg:col-span-8 space-y-6">

                    {{-- Excerpt --}}
                    <div class="bg-white border rounded-xl p-4">
                        <h3 class="font-semibold mb-3">Excerpt</h3>

                        <div x-show="lang === 'id'">
                            {{-- excerpt_editor_id --}}
                            @includeWhen(true,'front.partials.fellowship.tinymce-excerpt-id')
                        </div>

                        <div x-show="lang === 'en'">
                            {{-- excerpt_editor_en --}}
                            @includeWhen(true,'front.partials.fellowship.tinymce-excerpt-en')
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="bg-white border rounded-xl p-4">
                        <h3 class="font-semibold mb-3">Content</h3>

                        <div>
                            {{-- editor_id --}}
                            @includeWhen(true,'front.partials.fellowship.tinymce-content-id')
                        </div>
                    </div>
                </div>

                {{-- SAVE --}}
                <div class="col-span-12 sticky bottom-0 bg-white border-t p-4 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60
                                   text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2">

                        <svg wire:loading class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>

                        <span wire:loading.remove>
                            {{ isset($fellowship) ? '💾 Update' : '🚀 Create' }}
                        </span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>

            </div>
        </form>
    </div>

    @include('front.components.floating')
</div>