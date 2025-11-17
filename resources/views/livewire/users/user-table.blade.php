<div class="flex flex-col justify-center items-center">
    <div class="bg-white shadow rounded-lg p-6 overflow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Daftar Halaman</h2>
            <a href="{{ route('user.create') }}">
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                    🚀 Create
                </button>
            </a>
        </div>
        <div class="flex items-center justify-between mb-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="border p-2">
            </div>

        <table id="userTable" class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left text-sm font-medium text-gray-600">
                    <th class="p-3">No</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Action</th>
                </tr>
            </thead>
            @foreach ($users as $index => $data)
            <tbody>
                <tr>
                    <td class="p-3">{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                    <td class="p-3">{{ $data->name }}</td>
                    <td class="p-3">{{ $data->email }}</td>
                    <td class="p-3">{{ $data->role }}</td>
                    <td class="p-3">
                        <a href="{{ route('user.edit', $data->id) }}"
                            class="bg-yellow-600 px-3 py-1 rounded text-white">Edit</a>
                        <button wire:click='delete({{ $data->id }})'
                            class="bg-red-600 px-3 py-1 rounded text-white">Delete</button>
                    </td>
                </tr>
            </tbody>
            @endforeach
        </table>
        <!-- Pagination -->
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 3000)"
        class="fixed bottom-6 right-6 bg-green-400 text-white p-10 shadow-lg 
               hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
        {{ session('success') }}
    </div>
    @endif
</div>