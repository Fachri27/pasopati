<div x-data="{ lang: 'id' }">
    <x-admin.page-header :title="$petition ? 'Ubah petisi' : 'Petisi baru'"
                         :crumbs="[['Petisi', route('petition.admin.index')]]"
                         :back="route('petition.admin.index')">
        <a href="{{ route('petition.admin.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
        <button type="submit" form="form-petisi" wire:loading.attr="disabled" wire:target="save"
                class="admin-btn admin-btn--primary admin-btn--sm">
            <span wire:loading.remove wire:target="save">{{ $petition ? 'Simpan perubahan' : 'Simpan petisi' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>

        <x-slot:aside>
            {{-- Judul dan deskripsi ditulis per bahasa; isian lain berlaku
                 untuk keduanya. --}}
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
            {{-- .admin-alert ber-display flex (ikon + isi), jadi isinya dibungkus
                 satu elemen supaya judul dan daftarnya tidak berdampingan. --}}
            <div class="admin-alert mb-5" role="alert">
                <div>
                    <p class="font-medium">Petisi belum tersimpan.</p>
                    <ul class="mt-1 list-disc pl-4">
                        @foreach ($errors->all() as $galat)
                            <li>{{ $galat }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="form-petisi" wire:submit.prevent="save" class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="grid min-w-0 gap-5">
                <x-admin.card title="Isi petisi">
                    <div class="grid gap-4">
                        <div x-show="lang === 'id'">
                            <label for="petisi-judul-id" class="admin-label">Judul (Indonesia)</label>
                            <input id="petisi-judul-id" type="text" wire:model="title_id" wire:input="updateTitleId($event.target.value)"
                                   class="admin-input" @error('title_id') aria-invalid="true" @enderror>
                            @error('title_id') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="lang === 'en'" x-cloak>
                            <label for="petisi-judul-en" class="admin-label">Judul (English)</label>
                            <input id="petisi-judul-en" type="text" wire:model="title_en"
                                   class="admin-input" @error('title_en') aria-invalid="true" @enderror>
                            @error('title_en') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="lang === 'id'">
                            <label for="petisi-deskripsi-id" class="admin-label">Deskripsi (Indonesia)</label>
                            <textarea id="petisi-deskripsi-id" wire:model="description_id" rows="10" class="admin-input">{{ $description_id }}</textarea>
                        </div>
                        <div x-show="lang === 'en'" x-cloak>
                            <label for="petisi-deskripsi-en" class="admin-label">Deskripsi (English)</label>
                            <textarea id="petisi-deskripsi-en" wire:model="description_en" rows="10" class="admin-input">{{ $description_en }}</textarea>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Poin tuntutan" description="Tampil sebagai daftar di halaman petisi.">
                    {{-- Enter di kotak ini menambah poin, bukan mengirim form. --}}
                    <div class="flex gap-2">
                        <input type="text" wire:model="demandInput" wire:keydown.enter.prevent="addDemand"
                               placeholder="Tulis satu tuntutan…" aria-label="Poin tuntutan baru"
                               class="admin-input" @error('demandInput') aria-invalid="true" @enderror>
                        <button type="button" wire:click="addDemand" class="admin-btn admin-btn--ghost shrink-0">
                            <x-admin.icon name="plus" class="size-3.5" />
                            Tambah
                        </button>
                    </div>
                    @error('demandInput') <p class="admin-error">{{ $message }}</p> @enderror

                    @if (count($demands))
                        <ul class="mt-3 divide-y divide-admin-line rounded-md border border-admin-line">
                            @foreach ($demands as $index => $demand)
                                <li class="flex items-start gap-3 py-2 pr-1.5 pl-3">
                                    <span class="admin-num mt-0.5 w-5 shrink-0 text-[12px] text-admin-muted">{{ $index + 1 }}</span>
                                    <span class="min-w-0 flex-1 text-[13.5px]">{{ $demand }}</span>
                                    <button type="button" wire:click="removeDemand({{ $index }})"
                                            class="admin-icon-btn size-7 shrink-0" title="Buang poin ini" aria-label="Buang poin {{ $index + 1 }}">
                                        <x-admin.icon name="close" class="size-3.5" />
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="admin-hint">Belum ada tuntutan.</p>
                    @endif
                </x-admin.card>
            </div>

            {{-- top-[68px]: bilah atas halaman (48px) menempel di atas, jadi
                 kartu samping berhenti tepat di bawahnya. --}}
            <div class="grid gap-5 lg:sticky lg:top-[68px]">
                <x-admin.card title="Publikasi">
                    <div class="grid gap-4">
                        <div>
                            <label for="petisi-status" class="admin-label">Status</label>
                            <select id="petisi-status" wire:model="status" class="admin-input">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                                <option value="succeeded">Succeeded</option>
                            </select>
                            @error('status') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="petisi-terbit" class="admin-label">Tanggal publikasi</label>
                            <input id="petisi-terbit" type="date" wire:model="published_at" class="admin-input">
                            @error('published_at') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Sasaran">
                    <div class="grid gap-4">
                        <div>
                            <label for="petisi-kepada" class="admin-label">Ditujukan kepada</label>
                            <input id="petisi-kepada" type="text" wire:model="target_name" placeholder="Contoh: Presiden RI"
                                   class="admin-input" @error('target_name') aria-invalid="true" @enderror>
                            @error('target_name') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="petisi-target" class="admin-label">Target tanda tangan</label>
                            <input id="petisi-target" type="number" wire:model="goal_count" min="1"
                                   class="admin-input admin-num" @error('goal_count') aria-invalid="true" @enderror>
                            @error('goal_count') <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Gambar sampul">
                    @if ($cover_image)
                        <img src="{{ $cover_image->temporaryUrl() }}" alt="Pratinjau sampul baru"
                             class="mb-3 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                    @elseif ($old_cover_image)
                        <img src="{{ asset('storage/' . $old_cover_image) }}" alt="Sampul saat ini"
                             class="mb-3 aspect-[16/9] w-full rounded-md border border-admin-line object-cover">
                    @endif
                    <input type="file" wire:model="cover_image" accept="image/png,image/jpeg" aria-label="Gambar sampul" class="admin-input">
                    <p class="admin-hint" wire:loading.remove wire:target="cover_image">JPG atau PNG, paling besar 5 MB.</p>
                    <p class="admin-hint" wire:loading wire:target="cover_image">Mengunggah…</p>
                    @error('cover_image') <p class="admin-error">{{ $message }}</p> @enderror
                </x-admin.card>
            </div>
        </form>
    </div>

    <x-admin.toast />
</div>
