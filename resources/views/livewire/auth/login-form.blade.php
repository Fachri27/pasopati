<div>
    {{-- Login card --}}
    <div class="bg-admin-surface/80 backdrop-blur-xl border border-admin-line rounded-2xl p-8 shadow-2xl shadow-black/30">

        {{-- Brand --}}
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-admin-amber to-admin-red flex items-center justify-center shadow-lg shadow-admin-amber/20">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div>
                <h1 class="font-admin-display font-bold text-admin-smoke text-xl tracking-tight">Pasopati</h1>
                <p class="text-[11px] text-admin-muted uppercase tracking-[0.2em] font-medium">Monitoring Center</p>
            </div>
        </div>

        <form wire:submit.prevent="login" class="space-y-5">
            {{-- Email --}}
            <div>
                <label for="email" class="block text-xs font-semibold text-admin-ash uppercase tracking-wider mb-1.5">Email</label>
                <input id="email" type="email" wire:model.lazy="email"
                       class="admin-input"
                       placeholder="nama@contoh.id"
                       autocomplete="email" autofocus>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-xs font-semibold text-admin-ash uppercase tracking-wider mb-1.5">Password</label>
                <input id="password" type="password" wire:model.lazy="password"
                       class="admin-input"
                       placeholder="Masukkan password"
                       autocomplete="current-password">
                @error('password')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="remember"
                       class="w-4 h-4 rounded border-admin-line bg-admin-raised text-admin-amber focus:ring-admin-amber/30 focus:ring-offset-0">
                <span class="text-sm text-admin-ash">Ingat saya</span>
            </label>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-lg font-semibold text-sm bg-gradient-to-r from-admin-amber to-admin-ember text-white shadow-lg shadow-admin-amber/20 hover:shadow-admin-amber/30 hover:brightness-110 transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" class="opacity-75"/></svg>
                    Memproses…
                </span>
            </button>
        </form>

        {{-- General error --}}
        @if ($errors->has('email'))
            <div class="mt-4 text-center text-sm text-red-400 bg-red-400/10 rounded-lg py-2">
                {{ $errors->first('email') }}
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <p class="text-center text-xs text-admin-muted mt-6">
        Sistem Monitoring Karhutla Indonesia
    </p>
</div>
