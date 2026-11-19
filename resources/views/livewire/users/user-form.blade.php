<div>
    @php
        // Halaman ubah juga bisa dibuka editor, sedangkan daftar pengguna
        // hanya untuk admin. Remah, tombol kembali, dan Batal karena itu
        // mengarah ke daftar hanya bila pemakainya memang bisa membukanya.
        $bisaKeDaftar = auth()->user()->role === 'admin';
        $kembali = $bisaKeDaftar ? route('user.index') : route('dashboard');
        $judul = ! $userId ? 'Pengguna baru' : ((int) $userId === auth()->id() ? 'Profil saya' : 'Ubah pengguna');

        // Akun komentar yang masuk lewat Google menyimpan alamat avatar utuh,
        // bukan path di storage.
        $fotoLama = $existing_image
            ? (\Illuminate\Support\Str::startsWith($existing_image, ['http://', 'https://']) ? $existing_image : asset('storage/' . $existing_image))
            : null;
    @endphp

    <x-admin.page-header :title="$judul"
                         :crumbs="$bisaKeDaftar ? [['Pengguna', route('user.index')]] : []"
                         :back="$kembali"
                         narrow>
        <a href="{{ $kembali }}" class="admin-btn admin-btn--quiet admin-btn--sm">Batal</a>
        <button type="submit" form="form-pengguna" wire:loading.attr="disabled" wire:target="save"
                class="admin-btn admin-btn--primary admin-btn--sm">
            <span wire:loading.remove wire:target="save">{{ $userId ? 'Simpan perubahan' : 'Simpan pengguna' }}</span>
            <span wire:loading wire:target="save">Menyimpan…</span>
        </button>
    </x-admin.page-header>

    <div class="admin-wrap admin-wrap--narrow admin-body">
        <form id="form-pengguna" wire:submit.prevent="save" class="grid gap-5">
            <x-admin.card title="Akun">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="pengguna-nama" class="admin-label">Nama</label>
                        <input id="pengguna-nama" type="text" wire:model.defer="name" autocomplete="name"
                               class="admin-input" @error('name') aria-invalid="true" @enderror>
                        @error('name') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="pengguna-email" class="admin-label">Email</label>
                        <input id="pengguna-email" type="email" wire:model.defer="email" autocomplete="email"
                               class="admin-input" @error('email') aria-invalid="true" @enderror>
                        @error('email') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="pengguna-peran" class="admin-label">Peran</label>
                        <select id="pengguna-peran" wire:model="role" required
                                @disabled(auth()->user()->role !== 'admin')
                                class="admin-input sm:max-w-xs" @error('role') aria-invalid="true" @enderror>
                            <option value="">Pilih peran</option>
                            @foreach (['admin', 'editor'] as $r)
                                <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                        <p class="admin-hint">Admin bisa mengelola pengguna; editor hanya mengelola konten.</p>
                        {{-- Pilihan peran hanya admin dan editor, jadi akun
                             komentar tampil tanpa peran terpilih. Tanpa
                             keterangan ini form tampak rusak. --}}
                        @if ($userId && ! in_array($role, ['admin', 'editor'], true))
                            <p class="admin-hint">Akun ini berperan {{ $role ?: 'kosong' }} (akun komentar pembaca). Menyimpan form ini akan memberinya akses CMS sesuai peran yang dipilih.</p>
                        @endif
                        @error('role') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Password"
                          :description="$userId ? 'Kosongkan keduanya bila password tidak diganti.' : 'Paling sedikit 6 karakter.'">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="pengguna-password" class="admin-label">{{ $userId ? 'Password baru' : 'Password' }}</label>
                        <input id="pengguna-password" type="password" wire:model.defer="password" autocomplete="new-password"
                               class="admin-input" @error('password') aria-invalid="true" @enderror>
                        @error('password') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="pengguna-password-ulang" class="admin-label">Ulangi password</label>
                        <input id="pengguna-password-ulang" type="password" wire:model.defer="password_confirmation" autocomplete="new-password"
                               class="admin-input">
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Foto profil" description="Tampil di sidebar CMS. Tanpa foto, yang tampil huruf depan nama.">
                <div class="flex items-center gap-4">
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" alt="Pratinjau foto baru"
                             class="size-14 shrink-0 rounded-full border border-admin-line object-cover">
                    @elseif ($fotoLama)
                        <img src="{{ $fotoLama }}" alt="Foto saat ini" referrerpolicy="no-referrer"
                             class="size-14 shrink-0 rounded-full border border-admin-line object-cover">
                    @else
                        <span class="grid size-14 shrink-0 place-items-center rounded-full bg-admin-tint-strong text-admin-muted">
                            <x-admin.icon name="user" class="size-5" />
                        </span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <input type="file" name="image" id="image" wire:model="image" accept="image/png,image/jpeg"
                               aria-label="Foto profil" class="admin-input" @error('image') aria-invalid="true" @enderror>
                        <p class="admin-hint" wire:loading.remove wire:target="image">JPG atau PNG, paling besar 2 MB.</p>
                        <p class="admin-hint" wire:loading wire:target="image">Mengunggah…</p>
                        @error('image') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-admin.card>
        </form>

        @if ($perangkat !== null)
            {{-- Di luar <form>: daftar dan tombolnya tidak ikut tersimpan
                 bersama profil. Hanya tampil di akun sendiri. --}}
            <x-admin.card class="mt-5" title="Masuk dengan biometrik"
                          description="Sidik jari, wajah, atau PIN perangkat bisa dipakai masuk tanpa mengetik email dan password.">
                <div x-data="{ nama: '', sibuk: false, galat: '', didukung: window.cmsPasskey?.didukung() ?? false,
                    async tambah() {
                        this.sibuk = true; this.galat = '';
                        const { success } = await window.cmsPasskey.daftar(this.nama);
                        this.sibuk = false;
                        if (success) { this.nama = ''; $wire.$refresh(); }
                        else { this.galat = 'Perangkat tidak jadi didaftarkan. Coba lagi, dan setujui permintaan sidik jari atau wajah di perangkat.'; }
                    } }">
                    @if ($perangkat->isNotEmpty())
                        <ul class="mb-4 divide-y divide-admin-line rounded-md border border-admin-line">
                            @foreach ($perangkat as $kunci)
                                <li class="flex items-center gap-3 py-2 pr-1.5 pl-3" wire:key="kunci-{{ $kunci->getKey() }}">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-[13.5px] font-medium text-admin-smoke">{{ $kunci->alias ?: 'Perangkat' }}</span>
                                        <span class="block text-[12.5px] text-admin-muted">Didaftarkan {{ $kunci->created_at->translatedFormat('j F Y') }}</span>
                                    </span>
                                    <button type="button" wire:click="hapusPerangkat('{{ $kunci->getKey() }}')"
                                            wire:confirm="Hapus perangkat ini? Perangkat itu tidak bisa lagi dipakai untuk masuk."
                                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="admin-hint mb-3">Belum ada perangkat terdaftar.</p>
                    @endif

                    <div class="flex flex-wrap items-end gap-2">
                        <div class="min-w-[200px] flex-1">
                            <label for="nama-perangkat" class="admin-label">Nama perangkat (opsional)</label>
                            <input id="nama-perangkat" type="text" x-model="nama" maxlength="60" placeholder="Contoh: Laptop kantor" class="admin-input">
                        </div>
                        <button type="button" @click="tambah" :disabled="sibuk || !didukung" class="admin-btn admin-btn--ghost">
                            <span x-show="!sibuk">Tambah perangkat ini</span>
                            <span x-show="sibuk" x-cloak>Menunggu perangkat…</span>
                        </button>
                    </div>
                    {{-- Tombol selalu tampil supaya jelas di mana tempatnya; kalau
                         peramban menolak WebAuthn, alasannya dijelaskan di sini. --}}
                    <p x-show="!didukung" x-cloak class="admin-error">
                        <template x-if="!window.isSecureContext">
                            <span>Biometrik hanya jalan lewat https. Buka halaman ini lewat
                                <a class="underline" :href="'https://' + location.host + location.pathname" x-text="'https://' + location.host"></a>.</span>
                        </template>
                        <template x-if="window.isSecureContext">
                            <span>Peramban ini tidak mendukung biometrik. Pakai peramban terbaru.</span>
                        </template>
                    </p>
                    <p x-show="galat" x-cloak x-text="galat" class="admin-error"></p>
                </div>
            </x-admin.card>
        @endif
    </div>

    <x-admin.toast />
</div>
