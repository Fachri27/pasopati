@extends('layouts.admin')

@section('title', 'Dashboard — Pasopati CMS')

@section('content')
@php
    $adalahAdmin = auth()->user()->role === 'admin';

    // Satu baris angka, bukan deretan kartu berikon: enam hitungan ini dibaca
    // berdampingan, jadi yang dibutuhkan adalah kolom yang sejajar. `url`
    // kosong berarti selnya bukan tautan — Komentar belum punya halaman
    // moderasi di CMS, dan daftar Pengguna hanya terbuka untuk admin.
    $angka = [
        [
            'label' => 'Artikel',
            'nilai' => number_format($totalArticles),
            'ket' => $activeArticles.' aktif · '.$draftArticles.' draft',
            'url' => route('pages.index'),
        ],
        [
            'label' => 'Fellowship',
            'nilai' => number_format($totalFellowships),
            'ket' => $activeFellowships.' aktif · '.$upcomingFellowships.' mendatang',
            'url' => route('fellowship.index'),
        ],
        [
            'label' => 'Petisi',
            'nilai' => number_format($totalPetitions),
            'ket' => $activePetitions.' aktif · rata-rata '.$avgProgress.'% dari target',
            'url' => route('petition.admin.index'),
        ],
        [
            'label' => 'Tanda tangan',
            'nilai' => number_format($totalSignatures),
            'ket' => number_format($pendingSignatures).' menunggu verifikasi',
            'url' => route('petition.admin.index'),
        ],
        [
            'label' => 'Komentar',
            'nilai' => number_format($totalComments),
            'ket' => $pendingComments > 0 ? number_format($pendingComments).' perlu moderasi' : 'Semua terverifikasi',
            'perhatian' => $pendingComments > 0,
            'url' => null,
        ],
        [
            'label' => 'Pengguna',
            'nilai' => number_format($totalUsers),
            'ket' => 'Admin & editor',
            'url' => $adalahAdmin ? route('user.index') : null,
        ],
    ];

    $capStatus = [
        'active' => 'admin-cap--ok admin-cap--dot',
        'succeeded' => 'admin-cap--ok',
        'closed' => 'admin-cap--warn',
    ];
@endphp

<div>
    <x-admin.page-header title="Dashboard" description="Ringkasan isi dan aktivitas situs Pasopati." />

    <div class="admin-wrap admin-body">
        {{-- gap-px di atas alas sewarna garis: sekat antarsel tetap garis
             rambut berapa pun kolomnya (2, 3, atau 6), tanpa aturan border
             per titik-henti. --}}
        <div class="grid grid-cols-2 gap-px overflow-hidden rounded-[10px] border border-admin-line bg-admin-line shadow-(--admin-shadow) sm:grid-cols-3 xl:grid-cols-6">
            @foreach ($angka as $sel)
                <{{ $sel['url'] ? 'a' : 'div' }} @if ($sel['url']) href="{{ $sel['url'] }}" @endif
                    class="block bg-admin-surface px-4 py-3.5 {{ $sel['url'] ? 'hover:bg-admin-inset' : '' }}">
                    <p class="admin-eyebrow">{{ $sel['label'] }}</p>
                    <p class="admin-num mt-1.5 text-[26px] leading-none font-semibold tracking-tight text-admin-smoke">{{ $sel['nilai'] }}</p>
                    <p class="mt-2 text-[12.5px] leading-snug {{ ($sel['perhatian'] ?? false) ? 'font-medium text-admin-ember' : 'text-admin-muted' }}">{{ $sel['ket'] }}</p>
                </{{ $sel['url'] ? 'a' : 'div' }}>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <p class="admin-eyebrow mr-1">Aksi Cepat</p>
            <a href="{{ route('pages.create') }}" class="admin-btn admin-btn--ghost admin-btn--sm">
                <x-admin.icon name="plus" class="size-3.5" /> Artikel Baru
            </a>
            <a href="{{ route('fellowship.create') }}" class="admin-btn admin-btn--ghost admin-btn--sm">
                <x-admin.icon name="plus" class="size-3.5" /> Fellowship Baru
            </a>
            <a href="{{ route('petition.admin.create') }}" class="admin-btn admin-btn--ghost admin-btn--sm">
                <x-admin.icon name="plus" class="size-3.5" /> Petisi Baru
            </a>
            <a href="{{ route('kategori.create') }}" class="admin-btn admin-btn--ghost admin-btn--sm">
                <x-admin.icon name="plus" class="size-3.5" /> Kategori Baru
            </a>
        </div>

        <div class="mt-6 grid items-start gap-5 lg:grid-cols-2">
            <x-admin.card title="Artikel terbaru" flush>
                <x-slot:actions>
                    <a href="{{ route('pages.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Lihat semua</a>
                </x-slot:actions>

                <ul class="divide-y divide-admin-line">
                    @forelse ($recentArticles as $article)
                        <li>
                            <a href="{{ route('pages.edit', $article['id']) }}" class="flex items-center gap-3 px-5 py-2.5 hover:bg-admin-inset max-sm:px-4">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13.5px] font-medium text-admin-smoke">{{ $article['title'] }}</span>
                                    <span class="block text-[12.5px] text-admin-muted">{{ $article['created_at']->diffForHumans() }}</span>
                                </span>
                                <span class="admin-cap {{ $capStatus[$article['status']] ?? 'admin-cap--quiet' }}">{{ $article['status'] }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-[13px] text-admin-muted max-sm:px-4">Belum ada artikel.</li>
                    @endforelse
                </ul>
            </x-admin.card>

            <x-admin.card title="Petisi terbaru" flush>
                <x-slot:actions>
                    <a href="{{ route('petition.admin.index') }}" class="admin-btn admin-btn--quiet admin-btn--sm">Lihat semua</a>
                </x-slot:actions>

                <ul class="divide-y divide-admin-line">
                    @forelse ($recentPetitions as $petition)
                        <li>
                            <a href="{{ route('petition.admin.edit', $petition['id']) }}" class="flex items-center gap-3 px-5 py-2.5 hover:bg-admin-inset max-sm:px-4">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13.5px] font-medium text-admin-smoke">{{ $petition['title'] }}</span>
                                    <span class="block text-[12.5px] text-admin-muted">
                                        <span class="admin-num">{{ number_format($petition['signatures']) }}</span> ttd · {{ $petition['created_at']->diffForHumans() }}
                                    </span>
                                </span>
                                <span class="admin-cap {{ $capStatus[$petition['status']] ?? 'admin-cap--quiet' }}">{{ $petition['status'] }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-[13px] text-admin-muted max-sm:px-4">Belum ada petisi.</li>
                    @endforelse
                </ul>
            </x-admin.card>

            <x-admin.card title="Komentar menunggu moderasi" flush>
                @if ($pendingComments > 0)
                    <x-slot:actions>
                        <span class="admin-cap admin-cap--warn">{{ number_format($pendingComments) }}</span>
                    </x-slot:actions>
                @endif

                <ul class="divide-y divide-admin-line">
                    @forelse ($recentComments as $comment)
                        <li class="px-5 py-2.5 max-sm:px-4">
                            <p class="text-[13.5px] font-medium text-admin-smoke">{{ $comment['name'] }}</p>
                            <p class="truncate text-[13px] text-admin-ash">{{ $comment['body'] }}</p>
                            <p class="mt-0.5 truncate text-[12.5px] text-admin-muted">
                                Pada: {{ $comment['page_title'] }} · {{ $comment['created_at']->diffForHumans() }}
                            </p>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-[13px] text-admin-muted max-sm:px-4">Tidak ada komentar yang perlu dimoderasi.</li>
                    @endforelse
                </ul>
            </x-admin.card>

            <x-admin.card title="Petisi dengan tanda tangan terbanyak" flush>
                <ul class="divide-y divide-admin-line">
                    @forelse ($topPetitions as $petition)
                        <li class="px-5 py-2.5 max-sm:px-4">
                            <div class="flex items-baseline justify-between gap-3">
                                <p class="min-w-0 flex-1 truncate text-[13.5px] font-medium text-admin-smoke">{{ $petition['title'] }}</p>
                                <span class="admin-num text-[13.5px] font-semibold text-admin-smoke">{{ number_format($petition['signatures']) }}</span>
                            </div>
                            @if ($petition['goal'] > 0)
                                @php $pct = min(100, round(($petition['signatures'] / $petition['goal']) * 100)); @endphp
                                <div class="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-admin-tint-strong"
                                     role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}"
                                     aria-label="Capaian {{ $petition['title'] }}">
                                    <div class="h-full rounded-full bg-admin-smoke" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="mt-1 text-[12px] text-admin-muted">
                                    <span class="admin-num">{{ $pct }}%</span> dari <span class="admin-num">{{ number_format($petition['goal']) }}</span>
                                </p>
                            @endif
                        </li>
                    @empty
                        <li class="px-5 py-4 text-[13px] text-admin-muted max-sm:px-4">Belum ada data.</li>
                    @endforelse
                </ul>
            </x-admin.card>
        </div>
    </div>

    <x-admin.toast />
</div>
@endsection
