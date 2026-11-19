<div>
    <x-admin.page-header title="Petisi" description="Petisi publik beserta tanda tangan yang terkumpul.">
        <a href="{{ route('petition.admin.create') }}" class="admin-btn admin-btn--primary admin-btn--sm">
            <x-admin.icon name="plus" class="size-3.5" />
            Tambah petisi
        </a>
    </x-admin.page-header>

    <div class="admin-wrap admin-body">
        {{-- Ringkasan. Angka tanda tangan hanya menghitung yang sudah
             terverifikasi lewat email — sama dengan angka di halaman publik. --}}
        <dl class="admin-card mb-5 grid divide-y divide-admin-line sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="px-5 py-4">
                <dt class="admin-eyebrow">Petisi aktif</dt>
                <dd class="admin-num mt-1.5 text-[22px] leading-none font-semibold text-admin-smoke">{{ number_format($totalActive) }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="admin-eyebrow">Tanda tangan terverifikasi</dt>
                <dd class="admin-num mt-1.5 text-[22px] leading-none font-semibold text-admin-smoke">{{ number_format($totalSignaturesAll) }}</dd>
            </div>
            <div class="px-5 py-4">
                <dt class="admin-eyebrow">Tanda tangan bulan ini</dt>
                <dd class="admin-num mt-1.5 text-[22px] leading-none font-semibold text-admin-smoke">{{ number_format($totalSignaturesThisMonth) }}</dd>
            </div>
        </dl>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <x-admin.search wire:model.live.debounce.300ms="search" placeholder="Cari judul atau sasaran…" />

            <div class="max-w-full overflow-x-auto">
                <div class="admin-tabs" role="group" aria-label="Saring status">
                    @foreach (['' => 'Semua', 'draft' => 'Draft', 'active' => 'Active', 'closed' => 'Closed', 'succeeded' => 'Succeeded'] as $nilai => $label)
                        <button type="button" wire:click="$set('filterStatus', '{{ $nilai }}')"
                                aria-pressed="{{ (string) $filterStatus === (string) $nilai ? 'true' : 'false' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="admin-table-frame">
            <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Ditujukan kepada</th>
                            <th>Status</th>
                            <th>Progres</th>
                            <th class="text-right">Terkumpul</th>
                            <th class="admin-cell-actions"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($petitions as $petition)
                            @php
                                $locale = app()->getLocale();
                                $title = $petition->translation($locale)?->title ?? $petition->slug;
                                $progres = $petition->progressPercent();
                            @endphp
                            <tr wire:key="petisi-{{ $petition->id }}">
                                <td class="min-w-[220px] max-w-[360px]">
                                    <a href="{{ route('petition.admin.edit', $petition->id) }}" class="font-medium hover:underline">{{ $title }}</a>
                                </td>
                                <td class="max-w-[220px] text-admin-ash">{{ $petition->target_name }}</td>
                                <td>
                                    @switch($petition->status)
                                        @case('active')
                                            <span class="admin-cap admin-cap--ok admin-cap--dot">active</span>
                                            @break
                                        @case('succeeded')
                                            <span class="admin-cap admin-cap--ok">succeeded</span>
                                            @break
                                        @case('closed')
                                            <span class="admin-cap admin-cap--warn">closed</span>
                                            @break
                                        @default
                                            <span class="admin-cap admin-cap--quiet">{{ $petition->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-24 shrink-0 overflow-hidden rounded-full bg-admin-tint-strong" aria-hidden="true">
                                            <div class="h-full rounded-full bg-admin-smoke" style="width: {{ $progres }}%"></div>
                                        </div>
                                        <span class="admin-num text-[12px] text-admin-muted">{{ $progres }}%</span>
                                    </div>
                                </td>
                                <td class="admin-num text-right whitespace-nowrap text-admin-ash">
                                    {{ number_format($petition->signatureCount()) }}
                                    <span class="text-admin-muted">/ {{ number_format($petition->goal_count) }}</span>
                                </td>
                                <td class="admin-cell-actions">
                                    <a href="{{ route('petition.admin.signatures', $petition->id) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Tanda tangan</a>
                                    <a href="{{ route('petition.admin.export-pdf', $petition->id) }}" class="admin-btn admin-btn--quiet admin-btn--sm">PDF</a>
                                    <a href="{{ route('petition.admin.edit', $petition->id) }}" class="admin-btn admin-btn--quiet admin-btn--sm">Ubah</a>
                                    <button type="button" wire:click="delete({{ $petition->id }})"
                                            wire:confirm="Hapus petisi ini beserta semua tanda tangannya? Tindakan ini tidak bisa dibatalkan."
                                            class="admin-btn admin-btn--quiet-danger admin-btn--sm">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <x-admin.empty title="Tidak ada petisi" icon="pen">
                                        @if ($search !== '' || $filterStatus !== '')
                                            Tidak ada petisi yang cocok dengan saringan ini.
                                        @else
                                            Belum ada petisi yang dibuat.
                                        @endif
                                    </x-admin.empty>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $petitions->links('vendor.pagination.admin', ['livewire' => true]) }}
        </div>
    </div>

    <x-admin.toast />
</div>
