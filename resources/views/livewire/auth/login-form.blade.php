<div>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
            <h1 class="text-2xl font-bold text-center mb-6">Login</h1>

            <form wire:submit.prevent="login" class="space-y-4">
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model.lazy="email"
                        class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-300" placeholder="Email kamu">
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" wire:model.lazy="password"
                        class="w-full border rounded-lg p-2 focus:ring focus:ring-blue-300" placeholder="********">
                    @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Remember Me -->
                <label class="flex items-center text-sm">
                    <input type="checkbox" wire:model="remember" class="mr-2">
                    Ingat saya
                </label>

                <!-- Tombol Login -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition disabled:bg-gray-400"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="login">Login</span>
                    <span wire:loading wire:target="login">Memproses...</span>
                </button>
            </form>

            <!-- Pesan error umum -->
            @if ($errors->has('email'))
            <div class="mt-3 text-center text-red-500 text-sm">
                {{ $errors->first('email') }}
            </div>
            @endif
        </div>
    </div>
</div>