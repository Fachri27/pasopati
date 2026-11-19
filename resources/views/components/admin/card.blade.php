{{--
    Kartu CMS.

    title/description — kepala kartu (opsional; tanpa title kepala tidak dibuat).
    flush   — isi tanpa pias, untuk daftar atau tabel yang menempel ke tepi.
    danger  — bingkai merah untuk zona tindakan merusak.
    Slot: actions (kanan kepala), foot (kaki berlatar sisipan).
--}}
@props([
    'title' => null,
    'description' => null,
    'flush' => false,
    'danger' => false,
])

<section {{ $attributes->class(['admin-card', 'admin-card--danger' => $danger]) }}>
    @if ($title || isset($actions))
        <div class="admin-card__head">
            <div class="min-w-0 flex-1">
                @if ($title)
                    <h2>{{ $title }}</h2>
                @endif
                @if ($description)
                    <p>{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-1.5">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['admin-card__body' => ! $flush])>
        {{ $slot }}
    </div>

    @isset($foot)
        <div class="admin-card__foot">{{ $foot }}</div>
    @endisset
</section>
