<main class="grid min-h-screen lg:grid-cols-[1.05fr_minmax(400px,0.95fr)]">
    {{-- Panel merek. Selalu gelap, apa pun temanya — karena itu warnanya
         ditulis langsung, bukan lewat token CMS yang terang. Di layar sempit
         ia menyusut jadi pita di atas form. --}}
    <section class="relative flex min-h-[140px] flex-col justify-between overflow-hidden bg-[hsl(0_0%_7%)] p-6 text-white sm:min-h-[260px] lg:p-10">
        {{-- Garis Nusantara, digambar provinsi demi provinsi dari barat ke
             timur. Animasinya ada di dalam berkas SVG-nya sendiri; dibangkitkan
             dari public/data/peta-provinsi.js. --}}
        <img src="{{ asset('img/cms-nusantara.svg') }}" alt="" aria-hidden="true"
             class="pointer-events-none absolute top-1/2 -left-[8%] w-[116%] max-w-none -translate-y-1/2 select-none">

        <p class="relative text-[17px] font-semibold tracking-tight">
            Pasopati<span class="text-[#e5484d]">.</span>
        </p>
        <p class="relative text-[12px] font-medium text-white/50">
            Ruang redaksi Pasopati · Auriga Nusantara
        </p>
    </section>

    <section class="flex items-center justify-center p-6 lg:p-10">
        <div class="w-full max-w-[380px]">
            <p class="admin-eyebrow">Pasopati CMS</p>
            <h1 class="mt-2 text-[26px] leading-tight font-semibold tracking-tight">Masuk</h1>
            <p class="mt-2.5 text-[14px] text-admin-ash">Kelola artikel, kejadian, petisi, dan arsip Deforestory.</p>

            {{-- Galat masuk (email/password salah) dipasang komponennya di
                 kunci `email`; ditampilkan sekali di sini, tidak diulang di
                 bawah isiannya. --}}
            @error('email')
                <div class="admin-alert mt-5" role="alert">{{ $message }}</div>
            @enderror

            <form wire:submit.prevent="login" class="mt-6">
                <div class="grid gap-4">
                    <div>
                        <label for="email" class="admin-eyebrow mb-1.5 block">Email</label>
                        <input id="email" type="email" wire:model.lazy="email"
                               class="admin-input"
                               placeholder="nama@contoh.id"
                               autocomplete="email" autofocus
                               @error('email') aria-invalid="true" @enderror>
                    </div>

                    <div>
                        <label for="password" class="admin-eyebrow mb-1.5 block">Password</label>
                        <input id="password" type="password" wire:model.lazy="password"
                               class="admin-input"
                               placeholder="Masukkan password"
                               autocomplete="current-password"
                               @error('password') aria-invalid="true" @enderror>
                        @error('password')
                            <p class="admin-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" wire:model="remember" class="size-4">
                        <span class="text-[13.5px] text-admin-ash">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="admin-btn admin-btn--primary mt-6 w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="login">Masuk</span>
                    <span wire:loading wire:target="login">Memproses…</span>
                </button>
            </form>

            <p class="mt-5 border-t border-admin-line pt-4 text-[12.5px] leading-[1.6] text-admin-muted">
                Akun dibuat oleh admin. Kalau lupa password, minta admin mengaturnya ulang dari menu Pengguna.
            </p>
        </div>
    </section>
</main>
