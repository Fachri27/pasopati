@extends('layouts.admin')

@section('title', 'Dashboard — Pasopati CMS')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="font-admin-display font-bold text-2xl text-admin-smoke tracking-tight">Dashboard</h1>
        <p class="text-sm text-admin-muted mt-1">Ringkasan aktivitas sistem monitoring karhutla.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        {{-- Artikel --}}
        <div class="admin-stat-card bg-admin-surface border border-admin-line rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-admin-muted uppercase tracking-wider">Artikel</p>
                <div class="w-9 h-9 rounded-lg bg-admin-amber/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-admin-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
            </div>
            <p class="font-admin-display font-bold text-3xl text-admin-smoke">{{ $totalArticles }}</p>
            <div class="flex gap-3 text-xs mt-2">
                <span class="badge-active px-1.5 py-0.5 rounded">{{ $activeArticles }} aktif</span>
                <span class="badge-draft px-1.5 py-0.5 rounded">{{ $draftArticles }} draft</span>
            </div>
        </div>

        {{-- Fellowship --}}
        <div class="admin-stat-card bg-admin-surface border border-admin-line rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-admin-muted uppercase tracking-wider">Fellowship</p>
                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <p class="font-admin-display font-bold text-3xl text-admin-smoke">{{ $totalFellowships }}</p>
            <div class="flex gap-3 text-xs mt-2">
                <span class="badge-active px-1.5 py-0.5 rounded">{{ $activeFellowships }} aktif</span>
                <span class="badge-info px-1.5 py-0.5 rounded">{{ $upcomingFellowships }} mendatang</span>
            </div>
        </div>

        {{-- Petisi --}}
        <div class="admin-stat-card bg-admin-surface border border-admin-line rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-admin-muted uppercase tracking-wider">Petisi</p>
                <div class="w-9 h-9 rounded-lg bg-violet-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <p class="font-admin-display font-bold text-3xl text-admin-smoke">{{ $totalPetitions }}</p>
            <div class="flex gap-3 text-xs mt-2">
                <span class="badge-active px-1.5 py-0.5 rounded">{{ $activePetitions }} aktif</span>
                <span class="text-admin-muted">{{ number_format($totalSignatures) }} ttd</span>
            </div>
        </div>

        {{-- Komentar --}}
        <div class="admin-stat-card bg-admin-surface border border-admin-line rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-admin-muted uppercase tracking-wider">Komentar</p>
                <div class="w-9 h-9 rounded-lg bg-admin-red/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-admin-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
            <p class="font-admin-display font-bold text-3xl text-admin-smoke">{{ $totalComments }}</p>
            <div class="text-xs mt-2">
                @if ($pendingComments > 0)
                    <span class="badge-closed px-1.5 py-0.5 rounded">{{ $pendingComments }} perlu moderasi</span>
                @else
                    <span class="badge-active px-1.5 py-0.5 rounded">Semua terverifikasi</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Second row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

        <div class="bg-admin-surface border border-admin-line rounded-xl p-5">
            <p class="text-xs font-semibold text-admin-muted uppercase tracking-wider mb-2">Tanda Tangan Petisi</p>
            <p class="font-admin-display font-bold text-3xl text-admin-smoke">{{ number_format($totalSignatures) }}</p>
            <p class="text-xs text-admin-muted mt-2">
                <span class="text-amber-400 font-semibold">{{ number_format($pendingSignatures) }}</span> menunggu verifikasi
            </p>
        </div>

        <div class="bg-admin-surface border border-admin-line rounded-xl p-5">
            <p class="text-xs font-semibold text-admin-muted uppercase tracking-wider mb-2">Pengguna</p>
            <p class="font-admin-display font-bold text-3xl text-admin-smoke">{{ $totalUsers }}</p>
            <p class="text-xs text-admin-muted mt-2">Admin & Editor</p>
        </div>

        <div class="bg-admin-surface border border-admin-line rounded-xl p-5">
            <p class="text-xs font-semibold text-admin-muted uppercase tracking-wider mb-2">Progress Petisi Aktif</p>
            <p class="font-admin-display font-bold text-3xl text-admin-smoke">{{ $avgProgress }}%</p>
            <div class="w-full bg-admin-line rounded-full h-1.5 mt-3">
                <div class="bg-admin-amber h-1.5 rounded-full transition-all" style="width: {{ $avgProgress }}%"></div>
            </div>
        </div>
    </div>

    {{-- Recent lists --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">

        {{-- Recent Articles --}}
        <div class="bg-admin-surface border border-admin-line rounded-xl">
            <div class="px-5 py-4 border-b border-admin-line flex justify-between items-center">
                <h3 class="font-admin-display font-semibold text-admin-smoke">Artikel Terbaru</h3>
                <a href="{{ route('pages.index') }}" class="text-xs font-medium text-admin-amber hover:text-admin-smoke transition">Lihat semua</a>
            </div>
            <div class="divide-y divide-admin-line">
                @forelse ($recentArticles as $article)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-admin-smoke truncate">{{ $article['title'] }}</p>
                            <p class="text-xs text-admin-muted">{{ $article['created_at']->diffForHumans() }}</p>
                        </div>
                        <span class="ml-3 px-2 py-0.5 text-[11px] font-semibold rounded
                            @switch($article['status'])
                                @case('active') badge-active @break
                                @case('draft') badge-draft @break
                                @default text-admin-muted
                            @endswitch">
                            {{ ucfirst($article['status']) }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-admin-muted">Belum ada artikel.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Petitions --}}
        <div class="bg-admin-surface border border-admin-line rounded-xl">
            <div class="px-5 py-4 border-b border-admin-line flex justify-between items-center">
                <h3 class="font-admin-display font-semibold text-admin-smoke">Petisi Terbaru</h3>
                <a href="{{ route('petition.admin.index') }}" class="text-xs font-medium text-admin-amber hover:text-admin-smoke transition">Lihat semua</a>
            </div>
            <div class="divide-y divide-admin-line">
                @forelse ($recentPetitions as $petition)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-admin-smoke truncate">{{ $petition['title'] }}</p>
                            <p class="text-xs text-admin-muted">{{ number_format($petition['signatures']) }} ttd &middot; {{ $petition['created_at']->diffForHumans() }}</p>
                        </div>
                        <span class="ml-3 px-2 py-0.5 text-[11px] font-semibold rounded
                            @switch($petition['status'])
                                @case('active') badge-active @break
                                @case('draft') badge-draft @break
                                @case('closed') badge-closed @break
                                @case('succeeded') badge-info @break
                                @default text-admin-muted
                            @endswitch">
                            {{ ucfirst($petition['status']) }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-admin-muted">Belum ada petisi.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">

        {{-- Pending Comments --}}
        <div class="bg-admin-surface border border-admin-line rounded-xl">
            <div class="px-5 py-4 border-b border-admin-line flex justify-between items-center">
                <h3 class="font-admin-display font-semibold text-admin-smoke">
                    Komentar Menunggu Moderasi
                    @if ($pendingComments > 0)
                        <span class="ml-2 badge-closed px-2 py-0.5 text-[11px] rounded-full">{{ $pendingComments }}</span>
                    @endif
                </h3>
            </div>
            <div class="divide-y divide-admin-line">
                @forelse ($recentComments as $comment)
                    <div class="px-5 py-3">
                        <p class="text-sm font-medium text-admin-smoke">{{ $comment['name'] }}</p>
                        <p class="text-xs text-admin-ash truncate">{{ $comment['body'] }}</p>
                        <p class="text-xs text-admin-muted mt-1">
                            Pada: {{ $comment['page_title'] }} &middot; {{ $comment['created_at']->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-admin-muted">Tidak ada komentar yang perlu dimoderasi.</p>
                @endforelse
            </div>
        </div>

        {{-- Top Petitions --}}
        <div class="bg-admin-surface border border-admin-line rounded-xl">
            <div class="px-5 py-4 border-b border-admin-line">
                <h3 class="font-admin-display font-semibold text-admin-smoke">Petisi dengan Tanda Tangan Terbanyak</h3>
            </div>
            <div class="divide-y divide-admin-line">
                @forelse ($topPetitions as $petition)
                    <div class="px-5 py-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-sm font-medium text-admin-smoke truncate flex-1">{{ $petition['title'] }}</p>
                            <span class="text-sm font-bold text-violet-400 ml-2 font-admin-display">{{ number_format($petition['signatures']) }}</span>
                        </div>
                        @if ($petition['goal'] > 0)
                            @php $pct = min(100, round(($petition['signatures'] / $petition['goal']) * 100)); @endphp
                            <div class="w-full bg-admin-line rounded-full h-1.5">
                                <div class="bg-violet-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="text-[11px] text-admin-muted mt-1">{{ $pct }}% dari {{ number_format($petition['goal']) }}</p>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-admin-muted">Belum ada data.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-admin-surface border border-admin-line rounded-xl p-5 mb-8">
        <h3 class="font-admin-display font-semibold text-admin-smoke mb-4">Aksi Cepat</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('pages.create') }}" class="px-4 py-2 rounded-lg bg-admin-amber/10 text-admin-amber text-sm font-medium hover:bg-admin-amber/20 transition">
                + Artikel Baru
            </a>
            <a href="{{ route('fellowship.create') }}" class="px-4 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm font-medium hover:bg-emerald-500/20 transition">
                + Fellowship Baru
            </a>
            <a href="{{ route('petition.admin.create') }}" class="px-4 py-2 rounded-lg bg-violet-500/10 text-violet-400 text-sm font-medium hover:bg-violet-500/20 transition">
                + Petisi Baru
            </a>
            <a href="{{ route('kategori.create') }}" class="px-4 py-2 rounded-lg bg-admin-amber/10 text-admin-amber text-sm font-medium hover:bg-admin-amber/20 transition">
                + Kategori Baru
            </a>
        </div>
    </div>

</div>
@endsection
