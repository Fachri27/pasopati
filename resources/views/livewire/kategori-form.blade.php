<div>
    <div x-data="{lang:'id'}" class="">
        <div class="max-w-4xl mx-auto bg-admin-surface p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-admin-ash">
                {{ $isEdit ? '✏️ Edit Kategori' : '➕ Tambah Kategori' }}
            </h1>

            <div class="mb-6">
                <label class="font-medium text-admin-ash">🌐 Pilih Bahasa</label>
                <select x-model="lang" class="border pr-8 py-2 rounded-lg ml-3">
                    <option value="id">Indonesia</option>
                    <option value="en">English</option>
                </select>
            </div>

            <form wire:submit.prevent="save" class="space-y-6">
                {{-- Kategori Name (ID) --}}
                <div x-show="lang === 'id'">
                    <label class="block font-medium mb-1">Kategori Name (ID)</label>
                    <input type="text" wire:model="kategori_name_id"
                        class="w-full border px-3 py-2 rounded-lg">
                    @error('kategori_name_id') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                </div>

                {{-- Kategori Name (EN) --}}
                <div x-show="lang === 'en'">
                    <label class="block font-medium mb-1">Kategori Name (EN)</label>
                    <input type="text" wire:model="kategori_name_en"
                        class="w-full border px-3 py-2 rounded-lg">
                    @error('kategori_name_en') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                </div>

                <div>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                        {{ $isEdit ? '💾 Update' : '🚀 Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>