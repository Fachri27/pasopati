<div>
    <div class="relative w-full max-w-sm">
        <input type="text" wire:model.debounce.{{ $delay }}ms="search" placeholder="{{ $placeholder }}"
            class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>
</div>