<div>
    <section id="hero" class="flex flex-col justify-center items-center">
        <div class="max-w-md mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-gray-700">
                {{ $userId ? '✏️ Edit Profile' : '➕ Add User' }}
            </h1>

            <form wire:submit.prevent="save">
                <div class="flex items-center space-x-2">
                    <div class="mb-3 w-1/2">
                        <input type="text" wire:model.defer="name" placeholder="Name" class="w-full border rounded p-2">
                        @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3 w-1/2">
                        <input type="email" wire:model.defer="email" placeholder="Email"
                            class="w-full border rounded p-2">
                        @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <input type="password" wire:model.defer="password" placeholder="Password"
                        class="w-full border rounded p-2">
                    @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mb-3">
                    <input type="password" wire:model.defer="password_confirmation" placeholder="Confirm Password"
                        class="w-full border rounded p-2">
                </div>

                {{-- <div class="w-full max-w-lg mb-3">
                    <input type="file" wire:model="image" class="w-full">
                    @error('image') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

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

                        <div class="flex items-center justify-between w-full border border-gray-200 rounded-xl bg-white p-3 
                        transition-all duration-200 ease-in-out
                        focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-400/50
                        group-hover:shadow-md">
                            <div class="flex items-center gap-2">
                                <div class="bg-gray-100 p-2 rounded-lg text-gray-500">
                                    📁
                                </div>
                                <span x-text="fileName" class="text-gray-500 text-sm truncate w-44"></span>
                            </div>

                            <div class="text-gray-400">
                                📎
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
                        class="w-full border px-3 py-2 rounded-lg focus:ring focus:border-blue-400" required>
                        <option value="">Pilih Role</option>
                        @foreach(['admin','editor'] as $r)
                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full">
                    {{ $userId ? 'Update User' : 'Add User' }}
                </button>
            </form>
        </div>

    </section>
</div>