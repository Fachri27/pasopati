<div>
    <x-admin.page-header :title="$isEdit ? 'Ubah kategori' : 'Kategori baru'"
                         :crumbs="[['Kategori', route('kategori.index')]]"
                         :back="route('kategori.index')"
                         narrow>
        <a href="{{ route('kategori.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
        <button type="submit" form="form-kategori" wire:loading.attr="disabled" wire:target="save"
                class="admin-btn admin-btn--primary admin-btn--sm">
            <span wire:loading.remove wire:target="save">{{ $isEdit ? 'Simpan perubahan' : 'Simpan kategori' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>
    </x-admin.page-header>

    <div class="admin-wrap admin-wrap--narrow admin-body">
        {{-- Dua nama tampil berdampingan, tanpa sakelar bahasa: keduanya wajib
             diisi, dan dengan sakelar galat di bahasa yang sedang tersembunyi
             tidak terlihat sehingga form tampak menolak disimpan tanpa sebab. --}}
        <form id="form-kategori" wire:submit.prevent="save">
            <x-admin.card title="Nama kategori" description="Nama tampil sesuai bahasa yang dipilih pembaca di situs.">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="kategori-nama-id" class="admin-label">Nama (Indonesia)</label>
                        <input id="kategori-nama-id" type="text" wire:model="kategori_name_id" maxlength="100"
                               class="admin-input" @error('kategori_name_id') aria-invalid="true" @enderror>
                        @error('kategori_name_id') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="kategori-nama-en" class="admin-label">Nama (English)</label>
                        <input id="kategori-nama-en" type="text" wire:model="kategori_name_en" maxlength="100"
                               class="admin-input" @error('kategori_name_en') aria-invalid="true" @enderror>
                        @error('kategori_name_en') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-admin.card>
        </form>
    </div>

    <x-admin.toast />
</div>
