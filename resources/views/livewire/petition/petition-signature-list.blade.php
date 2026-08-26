<div>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        @php $locale = app()->getLocale(); @endphp

        {{-- Breadcrumb --}}
        <nav class="text-sm text-admin-ash mb-6 flex items-center gap-2">
            <a href="{{ route('petition.admin.index') }}" class="text-admin-smoke hover:text-blue-400 font-medium">
                Petisi
            </a>
            <span class="text-admin-muted">›</span>
            <span class="text-blue-400 font-semibold">
                {{ $petition->translation($locale)?->title ?? $petition->slug }}
            </span>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-admin-smoke">{{ $petition->translation($locale)?->title ?? $petition->slug }}</h1>
                <p class="text-sm text-admin-muted mt-1">
                    {{ number_format($petition->signatureCount()) }} terverifikasi dari target {{ number_format($petition->goal_count) }}
                </p>
            </div>
            <button wire:click="exportCsv"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                Export CSV
            </button>
        </div>

        {{-- Filters --}}
        <div class="bg-admin-surface rounded-xl shadow p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari penandatangan..."
                        class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <select wire:model.live="filterVerified" class="border rounded-lg px-3 py-2">
                        <option value="">Semua</option>
                        <option value="verified">Terverifikasi</option>
                        <option value="unverified">Belum Verifikasi</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-admin-surface rounded-xl shadow overflow-hidden">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-admin-raised text-left text-sm font-medium text-admin-muted">
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Kota</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Komentar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($signatures as $signature)
                        <tr class="hover:bg-admin-raised">
                            <td class="px-6 py-4 text-sm font-medium text-admin-smoke">{{ $signature->name }}</td>
                            <td class="px-6 py-4 text-sm text-admin-ash">{{ $signature->email }}</td>
                            <td class="px-6 py-4 text-sm text-admin-ash">{{ $signature->city ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if ($signature->is_verified)
                                    <span class="text-emerald-400 text-xs font-semibold">✓ Verified</span>
                                @else
                                    <span class="text-amber-400 text-xs font-semibold">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-admin-ash">{{ $signature->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($signature->comment)
                                    <div class="flex items-start gap-2">
                                        <span class="text-admin-ash line-clamp-2">{{ $signature->comment }}</span>
                                        <button wire:click="deleteComment({{ $signature->id }})"
                                            wire:confirm="Hapus komentar ini?"
                                            class="text-red-400 hover:text-red-400 text-xs shrink-0">✕</button>
                                    </div>
                                @else
                                    <span class="text-admin-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-admin-muted">
                                Belum ada tanda tangan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $signatures->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
