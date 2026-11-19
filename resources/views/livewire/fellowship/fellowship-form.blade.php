<div x-data="{ lang: 'id' }">
    <x-admin.page-header :title="isset($fellowship) ? 'Ubah fellowship' : 'Fellowship baru'"
                         :crumbs="[['Fellowship', route('fellowship.index')]]"
                         :back="route('fellowship.index')">
        <a href="{{ route('fellowship.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
        <button type="submit" form="form-fellowship" wire:loading.attr="disabled" wire:target="save"
                class="admin-btn admin-btn--primary admin-btn--sm">
            <span wire:loading.remove wire:target="save">{{ isset($fellowship) ? 'Simpan perubahan' : 'Simpan fellowship' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>

        <x-slot:aside>
            {{-- Judul, sub judul, excerpt, isi kategori, dan gambar ditulis per
                 bahasa; tanggal dan status berlaku untuk keduanya. --}}
            <div class="admin-tabs" role="group" aria-label="Bahasa isian">
                <button type="button" @click="lang = 'id'" :aria-pressed="lang === 'id' ? 'true' : 'false'">Indonesia</button>
                <button type="button" @click="lang = 'en'" :aria-pressed="lang === 'en' ? 'true' : 'false'">English</button>
            </div>
        </x-slot:aside>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        {{-- Isian dua bahasa tidak tampil bersamaan, jadi galat di bahasa yang
             sedang tersembunyi tidak akan terlihat di samping isiannya.
             Ringkasan ini menampilkan semuanya sekaligus. --}}
        @if ($errors->any())
            <div class="admin-alert mb-5" role="alert">
                <p class="font-medium">Fellowship belum tersimpan.</p>
                <ul class="mt-1 list-disc pl-4">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="form-fellowship" wire:submit.prevent="save" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="grid min-w-0 gap-5">
                <x-admin.card title="Judul">
                    <div class="grid gap-4">
                        {{-- Slug hanya pratinjau: yang tersimpan dibuat ulang
                             di server dari judul Indonesia saat disimpan. --}}
                        <div x-show="lang === 'id'" class="grid gap-4" x-data="{
                                title: @js(old('title_id', $title_id ?? '')),
                                slug: @js(old('slugId', $slugId ?? '')),
                                makeSlug(text) {
                                    return text.toLowerCase()
                                        .replace(/[^a-z0-9]+/g, '-')
                                        .replace(/^-+|-+$/g, '');
                                }
                             }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                            <div>
                                <label for="fellowship-judul-id" class="admin-label">Judul (Indonesia)</label>
                                <input id="fellowship-judul-id" type="text" wire:model="title_id" x-model="title" @input="slug = makeSlug(title)"
                                       class="admin-input" @error('title_id') aria-invalid="true" @enderror>
                                @error('title_id') <p class="admin-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="fellowship-slug" class="admin-label">Slug</label>
                                <input id="fellowship-slug" type="text" x-model="slug" readonly
                                       class="admin-input font-admin-mono bg-admin-inset text-[13px] text-admin-ash">
                                <p class="admin-hint">Dibuat otomatis dari judul Indonesia.</p>
                            </div>
                        </div>

                        <div x-show="lang === 'en'" x-cloak>
                            <label for="fellowship-judul-en" class="admin-label">Judul (English)</label>
                            <input id="fellowship-judul-en" type="text" wire:model="title_en"
                                   class="admin-input" @error('title_en') aria-invalid="true" @enderror>
                            @error('title_en') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="lang === 'id'">
                            <label for="fellowship-sub-id" class="admin-label">Sub judul (Indonesia)</label>
                            <input id="fellowship-sub-id" type="text" wire:model="sub_judul_id" class="admin-input">
                        </div>

                        <div x-show="lang === 'en'" x-cloak>
                            <label for="fellowship-sub-en" class="admin-label">Sub judul (English)</label>
                            <input id="fellowship-sub-en" type="text" wire:model="sub_judul_en" class="admin-input">
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Excerpt" description="Ringkasan pendek yang tampil di daftar fellowship.">
                    <div x-show="lang === 'id'">
                        @include('front.partials.fellowship.tinymce-excerpt-id')
                    </div>

                    <div x-show="lang === 'en'" x-cloak>
                        @include('front.partials.fellowship.tinymce-excerpt-en')
                    </div>
                </x-admin.card>

                {{-- flush: partial ini membawa piasnya sendiri. Ia juga memuat
                     isi untuk dua bahasa sekaligus dan mengikuti sakelar bahasa
                     di kop halaman. --}}
                <x-admin.card title="Kategori dan isi" description="Centang kategori untuk menulis isinya. Tiap kategori menjadi satu tab di halaman fellowship." flush>
                    @include('front.partials.fellowship.tinymce-content-id')
                </x-admin.card>
            </div>

            {{-- top-[68px]: bilah atas halaman (48px) menempel di atas, jadi
                 rel samping berhenti tepat di bawahnya. --}}
            <div class="grid gap-5 lg:sticky lg:top-[68px]">
                <x-admin.card title="Publikasi">
                    <div class="grid gap-4">
                        <div>
                            <label for="fellowship-status" class="admin-label">Status</label>
                            <select id="fellowship-status" wire:model="status" class="admin-input" @error('status') aria-invalid="true" @enderror>
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="fellowship-mulai" class="admin-label">Mulai</label>
                                <input id="fellowship-mulai" type="date" wire:model="start_date"
                                       class="admin-input" @error('start_date') aria-invalid="true" @enderror>
                            </div>
                            <div>
                                <label for="fellowship-selesai" class="admin-label">Selesai</label>
                                <input id="fellowship-selesai" type="date" wire:model="end_date" class="admin-input">
                            </div>
                            @error('start_date') <p class="admin-error col-span-2 mt-0">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-admin.card>

                {{-- Gambar disimpan per bahasa; yang tampil mengikuti sakelar
                     bahasa di kop halaman. --}}
                <x-admin.card title="Gambar">
                    <div x-show="lang === 'id'" class="grid gap-4">
                        <div>
                            <label for="fellowship-gambar-id" class="admin-label">Gambar (Indonesia)</label>
                            @if ($image_id)
                                <img src="{{ $image_id->temporaryUrl() }}" alt="Pratinjau gambar baru"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @elseif ($old_image_id)
                                <img src="{{ asset('storage/'.$old_image_id) }}" alt="Gambar saat ini"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @endif
                            <input id="fellowship-gambar-id" type="file" wire:model="image_id" accept="image/*" class="admin-input">
                            @error('image_id') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fellowship-sampul-id" class="admin-label">Gambar sampul (Indonesia)</label>
                            @if ($image_cover_id)
                                <img src="{{ $image_cover_id->temporaryUrl() }}" alt="Pratinjau sampul baru"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @elseif ($old_image_cover_id)
                                <img src="{{ asset('storage/'.$old_image_cover_id) }}" alt="Sampul saat ini"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @endif
                            <input id="fellowship-sampul-id" type="file" wire:model="image_cover_id" accept="image/*" class="admin-input">
                            @error('image_cover_id') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div x-show="lang === 'en'" x-cloak class="grid gap-4">
                        <div>
                            <label for="fellowship-gambar-en" class="admin-label">Gambar (English)</label>
                            @if ($image_en)
                                <img src="{{ $image_en->temporaryUrl() }}" alt="Pratinjau gambar baru"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @elseif ($old_image_en)
                                <img src="{{ asset('storage/'.$old_image_en) }}" alt="Gambar saat ini"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @endif
                            <input id="fellowship-gambar-en" type="file" wire:model="image_en" accept="image/*" class="admin-input">
                            @error('image_en') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fellowship-sampul-en" class="admin-label">Gambar sampul (English)</label>
                            @if ($image_cover_en)
                                <img src="{{ $image_cover_en->temporaryUrl() }}" alt="Pratinjau sampul baru"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @elseif ($old_image_cover_en)
                                <img src="{{ asset('storage/'.$old_image_cover_en) }}" alt="Sampul saat ini"
                                     class="mb-2 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                            @endif
                            <input id="fellowship-sampul-en" type="file" wire:model="image_cover_en" accept="image/*" class="admin-input">
                            @error('image_cover_en') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <p class="admin-hint" wire:loading wire:target="image_id,image_en,image_cover_id,image_cover_en">Mengunggah…</p>
                </x-admin.card>
            </div>
        </form>
    </div>

    <x-admin.toast />
</div>
