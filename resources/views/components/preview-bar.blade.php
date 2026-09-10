@props([
    'status' => null,
    'cmsUrl' => null,
    'idUrl' => null,
    'enUrl' => null,
    'locale' => 'id',
    'label' => 'Pratinjau',
])

{{-- Bilah mengambang di bawah, bukan menempel di atas.

     Layout Deforestory memasang headernya sebagai `fixed` setinggi 64px, dan
     isi halaman diberi padding atas sebesar itu. Bilah `sticky top-0` di dalam
     isi akan tergelincir ke balik header itu, sementara membuatnya `fixed`
     di atas menuntut seluruh padding halaman digeser. Di bawah tidak ada yang
     perlu dinegosiasikan — dan ia tetap terlihat sepanjang halaman digulir. --}}
<div class="fixed bottom-5 left-1/2 -translate-x-1/2 z-[60] print:hidden" role="status">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 rounded-full border border-white/15 bg-[#1c1f24] px-4 py-2.5 text-white shadow-[0_8px_30px_rgba(0,0,0,.35)]">

        <span class="flex items-center gap-1.5 font-mono-ui text-[.68rem] font-semibold uppercase tracking-[.14em] text-amber-300">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            {{ $label }}
        </span>

        @if ($status)
            <span class="rounded-full px-2 py-0.5 text-[.68rem] font-semibold uppercase tracking-wide
                {{ $status === 'active' ? 'bg-emerald-400/15 text-emerald-300' : 'bg-amber-400/15 text-amber-300' }}">
                {{ $status }}
            </span>
        @endif

        @if ($idUrl && $enUrl)
            <span class="h-4 w-px bg-white/15" aria-hidden="true"></span>
            <span class="flex items-center gap-1 font-mono-ui text-[.7rem]" aria-label="Ganti bahasa pratinjau">
                <a href="{{ $idUrl }}" class="rounded px-1.5 py-0.5 transition {{ $locale === 'id' ? 'bg-white/15 font-semibold text-white' : 'text-white/55 hover:text-white' }}">ID</a>
                <a href="{{ $enUrl }}" class="rounded px-1.5 py-0.5 transition {{ $locale === 'en' ? 'bg-white/15 font-semibold text-white' : 'text-white/55 hover:text-white' }}">EN</a>
            </span>
        @endif

        @if ($cmsUrl)
            <span class="h-4 w-px bg-white/15" aria-hidden="true"></span>
            <a href="{{ $cmsUrl }}" class="text-[.78rem] font-medium text-white/75 underline-offset-2 transition hover:text-white hover:underline">
                Buka di CMS
            </a>
        @endif
    </div>

    <p class="mt-1.5 text-center font-mono-ui text-[.62rem] uppercase tracking-[.12em] text-ink-3">
        Halaman ini tidak dapat diakses publik
    </p>
</div>
