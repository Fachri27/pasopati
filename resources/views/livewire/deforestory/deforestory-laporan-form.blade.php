<div x-data="{ lang: 'id' }">
    <x-admin.page-header :title="$laporan ? 'Ubah laporan' : 'Laporan baru'"
                         :eyebrow="'Kasus /'.$caseSlug"
                         :crumbs="[
                             ['Deforestory', route('deforestory.index')],
                             ['Laporan /'.$caseSlug, route('deforestory.laporan.index', ['caseSlug' => $caseSlug])],
                         ]"
                         :current="$laporan ? 'Ubah' : 'Tambah'"
                         :back="route('deforestory.laporan.index', ['caseSlug' => $caseSlug])">
        <a href="{{ route('deforestory.laporan.index', ['caseSlug' => $caseSlug]) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
        <button type="submit" form="form-laporan" wire:loading.attr="disabled" wire:target="save"
                class="admin-btn admin-btn--primary admin-btn--sm">
            <span wire:loading.remove wire:target="save">{{ $laporan ? 'Simpan perubahan' : 'Simpan laporan' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>

        <x-slot:aside>
            {{-- Judul, gambar, ringkasan, dan isi ditulis per bahasa; isian
                 lain berlaku untuk keduanya. --}}
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
                <p class="font-medium">Laporan belum tersimpan.</p>
                <ul class="mt-1 list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="form-laporan" wire:submit.prevent="save" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="grid min-w-0 gap-5">
                <x-admin.card title="Judul">
                    {{-- Judul ID + slug --}}
                    <div class="grid gap-4" x-show="lang === 'id'" x-data="{
                        title: @js(old('title_id', $title_id ?? '')),
                        slug: @js(old('slug', $slug ?? '')),
                        makeSlug(text) {
                            return text.toLowerCase()
                                .replace(/[^a-z0-9]+/g, '-')
                                .replace(/^-+|-+$/g, '');
                        }
                    }" x-init="if(title && !slug){ slug = makeSlug(title) }">
                        <div>
                            <label for="laporan-judul-id" class="admin-label">Judul laporan (Indonesia)</label>
                            <input id="laporan-judul-id" type="text" wire:model="title_id" x-model="title" @input="slug = makeSlug(title)"
                                   class="admin-input" @error('title_id') aria-invalid="true" @enderror>
                            @error('title_id') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="laporan-slug" class="admin-label">Slug (URL detail)</label>
                            <input id="laporan-slug" type="text" x-model="slug" wire:model="slug" readonly
                                   class="admin-input bg-admin-inset font-admin-mono text-[13px]" @error('slug') aria-invalid="true" @enderror>
                            @error('slug') <p class="admin-error">{{ $message }}</p> @enderror
                            <p class="admin-hint break-all">Dibuat dari judul Indonesia. URL: <span class="font-admin-mono">/deforestory/{{ $caseSlug }}/<span x-text="slug"></span></span></p>
                        </div>
                    </div>

                    {{-- Judul EN --}}
                    <div x-show="lang === 'en'" x-cloak>
                        <label for="laporan-judul-en" class="admin-label">Judul laporan (English)</label>
                        <input id="laporan-judul-en" type="text" wire:model="title_en"
                               class="admin-input" @error('title_en') aria-invalid="true" @enderror>
                        @error('title_en') <p class="admin-error">{{ $message }}</p> @enderror
                        <p class="admin-hint">Slug mengikuti judul Indonesia.</p>
                    </div>
                </x-admin.card>

                <x-admin.card title="Ringkasan" description="Ringkasan singkat yang tampil di kartu arsip kasus.">
                    <div x-show="lang === 'id'">
                        @includeWhen(true, 'front.partials.tinymce-excerpt-id')
                    </div>
                    <div x-show="lang === 'en'">
                        @includeWhen(true, 'front.partials.tinymce-excerpt-en')
                    </div>
                </x-admin.card>

                <x-admin.card title="Isi laporan" description="Konten utama laporan. Tampil di halaman detail.">
                    {{-- wire:key mengikat editor ke bahasanya. Tanpa itu Livewire
                         memakai ulang simpul yang sama saat pilihan bahasa
                         berganti, dan isi ID sempat terbawa ke editor EN. --}}
                    <div x-show="lang === 'id'" wire:key="laporan-content-id">
                        <x-tinymce-editor
                            wire:model="laporan_content_id"
                            :value="$laporan_content_id"
                            hint="HTML lengkap dengan TinyMCE dan Source Code"
                        />
                    </div>
                    <div x-show="lang === 'en'" wire:key="laporan-content-en">
                        <x-tinymce-editor
                            wire:model="laporan_content_en"
                            :value="$laporan_content_en"
                            hint="Full custom HTML with TinyMCE and Source Code"
                        />
                    </div>
                </x-admin.card>
            </div>

            {{-- top-[68px]: bilah atas halaman (48px) menempel di atas, jadi
                 kartu samping berhenti tepat di bawahnya. --}}
            <div class="grid gap-5 lg:sticky lg:top-[68px]">
                <x-admin.card title="Publikasi">
                    <div class="grid gap-4">
                        <div>
                            <label for="laporan-status" class="admin-label">Status</label>
                            <select id="laporan-status" wire:model="status" class="admin-input">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-[minmax(0,1fr)_88px] gap-3">
                            <div>
                                <label for="laporan-terbit" class="admin-label">Tanggal publikasi</label>
                                <input id="laporan-terbit" type="date" wire:model="published_at"
                                       class="admin-input" @error('published_at') aria-invalid="true" @enderror>
                            </div>
                            <div>
                                <label for="laporan-urutan" class="admin-label">Urutan</label>
                                <input id="laporan-urutan" type="number" wire:model="sort"
                                       class="admin-input admin-num" @error('sort') aria-invalid="true" @enderror>
                            </div>
                            <div class="col-span-2 -mt-2">
                                @error('published_at') <p class="admin-error">{{ $message }}</p> @enderror
                                @error('sort') <p class="admin-error">{{ $message }}</p> @enderror
                                <p class="admin-hint">Tanggal terbit tampil di API dan halaman detail.</p>
                            </div>
                        </div>

                        {{-- Kunci pratinjau --}}
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-md border border-admin-line bg-admin-inset p-3">
                            <input type="checkbox" wire:model="is_locked" class="mt-0.5 size-4 shrink-0">
                            <span>
                                <span class="block text-[13.5px] font-medium text-admin-smoke">Kunci pratinjau</span>
                                <span class="mt-1 block text-[12.5px] leading-[1.55] text-admin-muted">
                                    Peninjau luar harus memasukkan password pratinjau Deforestory (diatur sekali
                                    di halaman daftar kasus) sebelum membuka tautan pratinjau laporan ini. Yang
                                    sedang login CMS tetap bisa membukanya langsung. Tidak memengaruhi halaman
                                    publik: laporan berstatus Active tetap terbuka penuh bagi pembaca.
                                </span>
                            </span>
                        </label>
                    </div>
                </x-admin.card>

                {{-- Gambar per bahasa (id + en) --}}
                <x-admin.card title="Gambar laporan" description="Satu gambar untuk tiap bahasa.">
                    <div x-show="lang === 'id'">
                        @if ($image_id)
                            <img src="{{ $image_id->temporaryUrl() }}" alt="Pratinjau gambar baru (Indonesia)"
                                 class="mb-3 size-20 rounded-md border border-admin-line object-cover">
                        @elseif ($old_image_id)
                            <img src="{{ asset('storage/' . $old_image_id) }}" alt="Gambar saat ini (Indonesia)"
                                 class="mb-3 size-20 rounded-md border border-admin-line object-cover">
                        @endif
                        <input type="file" wire:model="image_id" accept="image/*" aria-label="Gambar laporan (Indonesia)" class="admin-input">
                        <p class="admin-hint" wire:loading.remove wire:target="image_id">Gambar untuk versi Indonesia.</p>
                        <p class="admin-hint" wire:loading wire:target="image_id">Mengunggah…</p>
                        @error('image_id') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="lang === 'en'" x-cloak>
                        @if ($image_en)
                            <img src="{{ $image_en->temporaryUrl() }}" alt="Pratinjau gambar baru (English)"
                                 class="mb-3 size-20 rounded-md border border-admin-line object-cover">
                        @elseif ($old_image_en)
                            <img src="{{ asset('storage/' . $old_image_en) }}" alt="Gambar saat ini (English)"
                                 class="mb-3 size-20 rounded-md border border-admin-line object-cover">
                        @endif
                        <input type="file" wire:model="image_en" accept="image/*" aria-label="Gambar laporan (English)" class="admin-input">
                        <p class="admin-hint" wire:loading.remove wire:target="image_en">Gambar untuk versi English.</p>
                        <p class="admin-hint" wire:loading wire:target="image_en">Mengunggah…</p>
                        @error('image_en') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </x-admin.card>
            </div>
        </form>
    </div>

    <x-admin.toast />
</div>
