<div>
    <section id="hero" class="flex flex-col justify-center items-center">
        <div class="max-w-md mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-admin-ash">
                {{ $userId ? 'Edit Profile' : 'Add User' }}
            </h1>

            <form wire:submit.prevent="save">
                <div class="flex items-center space-x-2">
                    <div class="mb-3 w-1/2">
                        <input type="text" wire:model.defer="name" placeholder="Name" class="w-full border rounded p-2">
                        @error('name') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3 w-1/2">
                        <input type="email" wire:model.defer="email" placeholder="Email"
                            class="w-full border rounded p-2">
                        @error('email') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <input type="password" wire:model.defer="password" placeholder="Password"
                        class="w-full border rounded p-2">
                    @error('password') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mb-3">
                    <input type="password" wire:model.defer="password_confirmation" placeholder="Confirm Password"
                        class="w-full border rounded p-2">
                </div>

                {{-- <div class="w-full max-w-lg mb-3">
                    <input type="file" wire:model="image" class="w-full">
                    @error('image') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror

                    @if ($image)
                    <div class="mt-3">
                        <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                            class="w-16 h-16 rounded-full object-cover border">
                    </div>
                    @elseif ($existing_image)
                    <div class="mt-3">
                        <img src="{{ asset('storage/' . $existing_image) }}" alt="Avatar"
                            class="w-16 h-16 rounded-full object-cover border">
                    </div>
                    @endif
                </div> --}}

                <div class="w-full max-w-lg mb-3"
                    x-data="{ fileName: '{{ $image ? basename($image) : 'No File Selected' }}' }">

                    <div class="relative group">
                        <input type="file" name="image" id="image" wire:model="image"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            @change="fileName = $event.target.files.length ? $event.target.files[0].name : 'No File Selected'">

                        <div class="flex items-center justify-between w-full border border-admin-line rounded-xl bg-admin-surface p-3 transition-all duration-200 ease-in-out focus-within:border-admin-amber focus-within:ring-2 focus-within:ring-indigo-400/50 group-hover:shadow-md">
                            <div class="flex items-center gap-2">
                                <div class="bg-admin-raised p-2 rounded-lg text-admin-muted">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                    </svg>
                                </div>
                                <span x-text="fileName" class="text-admin-muted text-sm truncate w-44"></span>
                            </div>

                            <div class="text-admin-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                            </div>
                            @if ($image)
                            <div class="mt-3">
                                <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                                    class="w-16 h-16 rounded-full object-cover border">
                            </div>
                            @elseif ($existing_image)
                            <div class="mt-3">
                                <img src="{{ asset('storage/' . $existing_image) }}" alt="Avatar"
                                    class="w-16 h-16 rounded-full object-cover border">
                            </div>
                            @endif
                        </div>
                    </div>

                    @if(isset($user) && $user->image)
                    <div class="mt-3">
                        <img src="{{ asset('storage/' . $user->image) }}" alt="Avatar"
                            class="w-16 h-16 rounded-full object-cover border">
                    </div>
                    @endif
                </div>

                <div class="mb-3">
                    <select wire:model="role"
                        class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-admin-amber" required>
                        <option value="">Pilih Role</option>
                        @foreach(['admin','editor'] as $r)
                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="bg-admin-amber text-white px-4 py-2 rounded w-full">
                    {{ $userId ? 'Update User' : 'Add User' }}
                </button>
            </form>
        </div>

    </section>
</div>