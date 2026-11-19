{{-- Halaman masuk. Satu-satunya tempat di CMS yang memakai warna merek
     (merah dan hijau dari logo Pasopati Project): ini pintu depannya, dan
     bagian dalam CMS tetap netral. Foto di kanan adalah foto situs sendiri
     (public/img/gambar5.jpg); orangutannya sengaja dibiarkan di luar titik
     tengah supaya terlihat mengintip dari balik daun. --}}
<main class="admin-login grid min-h-screen lg:grid-cols-[minmax(440px,2fr)_3fr]">
    <section class="admin-login__form flex flex-col px-6 py-8 sm:px-12 lg:px-16 lg:py-12">
        <a href="{{ url('/') }}" aria-label="Pasopati Project">
            <img src="{{ asset('img/logo_pasopati.png') }}" alt="Pasopati Project" class="h-[18px] w-auto sm:h-5">
        </a>

        <div class="my-auto w-full max-w-[380px] py-12">
            <h1 class="text-[30px] leading-[1.15] font-semibold tracking-tight text-admin-smoke">Masuk ke CMS</h1>
            <p class="mt-3 text-[14.5px] leading-relaxed text-admin-ash">Kelola artikel, kejadian, petisi, dan arsip Deforestory.</p>

            {{-- Galat masuk (email/password salah) dipasang komponennya di
                 kunci `email`; ditampilkan sekali di sini, tidak diulang di
                 bawah isiannya. --}}
            @error('email')
                <div class="admin-alert mt-6" role="alert">{{ $message }}</div>
            @enderror

            <form wire:submit.prevent="login" class="mt-7">
                <div class="grid gap-5">
                    <div>
                        <label for="email" class="admin-label">Email</label>
                        <input id="email" type="email" wire:model.lazy="email"
                               class="admin-input"
                               placeholder="nama@contoh.id"
                               autocomplete="email" autofocus
                               @error('email') aria-invalid="true" @enderror>
                    </div>

                    <div>
                        <label for="password" class="admin-label">Kata sandi</label>
                        <input id="password" type="password" wire:model.lazy="password"
                               class="admin-input"
                               placeholder="Masukkan kata sandi"
                               autocomplete="current-password"
                               @error('password') aria-invalid="true" @enderror>
                        @error('password')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input type="checkbox" wire:model="remember" class="size-4">
                        <span class="text-[13.5px] text-admin-ash">Tetap masuk di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="admin-login__submit mt-7" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="login">Masuk</span>
                    <span wire:loading wire:target="login">Memproses…</span>
                </button>
            </form>

            {{-- Hanya muncul kalau peramban mendukung WebAuthn. Tanpa email:
                 perangkat sendiri yang memilih kunci tersimpan. --}}
            <div x-data="{ didukung: window.cmsPasskey?.didukung() ?? false, sibuk: false, galat: '',
                    async masuk() {
                        this.sibuk = true; this.galat = '';
                        const { success, error } = await window.cmsPasskey.masuk(document.getElementById('email')?.value.trim());
                        if (success) { window.location.href = '{{ route('dashboard') }}'; return; }
                        this.sibuk = false;
                        this.galat = 'Biometrik tidak dikenali. Daftarkan perangkat ini dulu di profil Anda. Kalau sudah, isi email di atas lalu coba lagi, atau masuk dengan email dan kata sandi.' + (error ? ' (' + error + ')' : '');
                    } }" x-show="didukung || !window.isSecureContext" x-cloak class="mt-6">
                <div x-show="didukung" class="mb-4 flex items-center gap-3 text-[12.5px] text-admin-muted">
                    <span class="h-px flex-1 bg-admin-line"></span>atau<span class="h-px flex-1 bg-admin-line"></span>
                </div>
                {{-- Peramban menyembunyikan WebAuthn di halaman http; tanpa
                     keterangan ini tombolnya lenyap tanpa sebab yang jelas. --}}
                <p x-show="!window.isSecureContext" x-cloak class="admin-hint">Biometrik hanya bisa dipakai lewat https. Buka situs ini dengan alamat https://.</p>
                <button type="button" @click="masuk" :disabled="sibuk || !didukung" x-show="didukung" class="admin-login__biometrik">
                    <x-admin.icon name="user" class="size-4" />
                    <span x-show="!sibuk">Masuk dengan biometrik</span>
                    <span x-show="sibuk" x-cloak>Menunggu perangkat…</span>
                </button>
                <p x-show="galat" x-cloak x-text="galat" class="admin-error mt-2" role="alert"></p>
            </div>

            <p class="mt-8 text-[12.5px] leading-[1.6] text-admin-muted">
                Akun dibuat oleh admin. Kalau lupa kata sandi, minta admin mengaturnya ulang dari menu Pengguna.
            </p>
        </div>

        <p class="text-[12px] text-admin-muted">Auriga Nusantara</p>
    </section>

    <section class="admin-login__foto order-first h-[200px] sm:h-[280px] lg:order-none lg:h-auto" aria-hidden="true">
        <img src="{{ asset('img/gambar5.jpg') }}" alt="" class="size-full object-cover">
    </section>
</main>
