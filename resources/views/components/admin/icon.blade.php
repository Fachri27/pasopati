{{--
    Ikon CMS: satu set garis 16×16, tebal 1,4, ujung membulat.

    Satu komponen, bukan SVG yang ditempel di tiap view: dulu tiap view
    membawa path Heroicons-nya sendiri dengan ukuran dan tebal garis yang
    berbeda-beda. Menambah ikon cukup menambah satu baris di $paths.

    Pemakaian: <x-admin.icon name="plus" /> — ukuran bawaan size-4, bisa
    ditimpa lewat class.
--}}
@props(['name'])

@php
    $paths = [
        // Navigasi
        'home' => 'M2.5 7 8 2.5 13.5 7v6.5h-4v-4h-3v4h-4z',
        'doc' => 'M4 2.5h6l2.5 2.5v8.5h-8.5zM10 2.5V5h2.5M6 8h4M6 10.5h4',
        'pin' => 'M8 14s4.5-4 4.5-7.5a4.5 4.5 0 0 0-9 0C3.5 10 8 14 8 14zM8 8a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
        'users' => 'M6 7a2.25 2.25 0 1 0 0-4.5A2.25 2.25 0 0 0 6 7zM1.5 13c.3-2.3 2-3.6 4.5-3.6s4.2 1.3 4.5 3.6M10.5 6.8a2 2 0 1 0-.6-3.9M12 9.6c1.4.4 2.3 1.5 2.5 3.4',
        'pen' => 'M2.5 13.5h11M4 10.5l6.5-6.5 2 2L6 12.5l-2.5.5z',
        'tree' => 'M8 14v-3.5M8 10.5c-2.5 0-4-1.6-4-3.6C4 4.4 5.8 2 8 2s4 2.4 4 4.9c0 2-1.5 3.6-4 3.6z',
        'tag' => 'M2.5 2.5h5l6 6-5 5-6-6zM5.5 5.5h0',
        'image' => 'M2.5 3h11v10h-11zM2.5 10.5 5.5 7.5l3 3 2-2 3 3M10.5 6.5h0',
        'chart' => 'M2.5 13.5h11M4.5 11V7.5M8 11V4M11.5 11V9',
        'user' => 'M8 7.5a2.75 2.75 0 1 0 0-5.5 2.75 2.75 0 0 0 0 5.5zM2.5 14c.4-2.8 2.6-4.5 5.5-4.5s5.1 1.7 5.5 4.5',
        'fire' => 'M8 14c2.8 0 4.5-1.9 4.5-4.4C12.5 6.5 9.5 5 9 1.5 7 3 6.6 5 6.8 6.5 5.6 6 5 4.8 5 4 3.8 5.2 3.5 7.2 3.5 9.6 3.5 12.1 5.2 14 8 14z',
        'chat' => 'M2.5 3.5h11v7.5H7l-3 2.5V11H2.5z',
        'mail' => 'M2.5 4.5h11v8h-11zM2.5 4.5 8 9l5.5-4.5',

        // Kerangka
        'panel' => 'M2.5 3h11v10h-11zM6 3v10',
        'logout' => 'M6 2.5H3v11h3M10 5l3 3-3 3M13 8H6.5',
        'search' => 'M7 12A5 5 0 1 0 7 2a5 5 0 0 0 0 10zM14 14l-3.5-3.5',
        'external' => 'M9.5 2.5h4v4M13.5 2.5 7.5 8.5M11.5 9.5v4h-9v-9h4',
        'globe' => 'M8 2.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11zM2.5 8h11M8 2.5c1.5 1.6 2.2 3.4 2.2 5.5S9.5 11.9 8 13.5C6.5 11.9 5.8 10.1 5.8 8S6.5 4.1 8 2.5z',

        // Arah
        'left' => 'M10 3 5 8l5 5',
        'right' => 'M6 3l5 5-5 5',
        'up' => 'M4 10l4-4 4 4',
        'down' => 'M4 6l4 4 4-4',

        // Tindakan
        'plus' => 'M8 3v10M3 8h10',
        'close' => 'M4 4l8 8M12 4l-8 8',
        'check' => 'M3 8.5l3 3 7-7',
        'eye' => 'M1.5 8S4 3.5 8 3.5 14.5 8 14.5 8 12 12.5 8 12.5 1.5 8 1.5 8zM8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z',
        'edit' => 'M3 13l.6-3.1 7-7 2.5 2.5-7 7zM9.5 4l2.5 2.5',
        'trash' => 'M3 4.5h10M6.5 4.5v-2h3v2M4.5 4.5l.5 9h6l.5-9M6.75 7v4M9.25 7v4',
        'download' => 'M8 2.5v8M4.5 7.5 8 11l3.5-3.5M2.5 13.5h11',
        'upload' => 'M8 11V3M4.5 6 8 2.5 11.5 6M2.5 13.5h11',
        'copy' => 'M5.5 5.5h8v8h-8zM10.5 5.5v-3h-8v8h3',
        'link' => 'M6.5 9.5l3-3M7 4.5l1.5-1.5a2.5 2.5 0 0 1 3.5 3.5L10.5 8M9 11.5 7.5 13A2.5 2.5 0 0 1 4 9.5L5.5 8',
        'refresh' => 'M13 8a5 5 0 1 1-1.5-3.5M13 2.5v3h-3',
        'grip' => 'M6 4h0M10 4h0M6 8h0M10 8h0M6 12h0M10 12h0',

        // Benda
        'calendar' => 'M2.5 3.5h11v10h-11zM2.5 6.5h11M5.5 2v3M10.5 2v3',
        'lock' => 'M4 7.5h8v6H4zM5.5 7.5V5a2.5 2.5 0 0 1 5 0v2.5',
        'flag' => 'M3.5 14V2.5M3.5 3h8.5l-2 3 2 3H3.5',
        'table' => 'M2.5 3h11v10h-11zM2.5 6.5h11M2.5 10h11M6.5 3v10',
        'video' => 'M2.5 3.5h11v9h-11zM6.5 6v4l3.5-2z',
        'file' => 'M4 2.5h6l2.5 2.5v8.5h-8.5zM10 2.5V5h2.5',
        'inbox' => 'M2.5 9.5h3l1 2h3l1-2h3M2.5 9.5 4.5 3h7l2 6.5v4h-11z',
        'info' => 'M8 14A6 6 0 1 0 8 2a6 6 0 0 0 0 12zM8 7.5V11M8 5h0',
        'warning' => 'M8 2.5 14 13H2zM8 6.5v3M8 11.5h0',
    ];

    // size-4 hanya bila pemanggil tidak membawa ukurannya sendiri. Kalau dua
    // kelas ukuran ditumpuk, yang menang ditentukan urutan di CSS, bukan
    // urutan di markup.
    $berukuran = preg_match('/(^|\s)(size|w|h)-/', (string) $attributes->get('class'));
@endphp

<svg {{ $attributes->class(['shrink-0', 'size-4' => ! $berukuran]) }} viewBox="0 0 16 16" fill="none" stroke="currentColor"
     stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="{{ $paths[$name] ?? $paths['file'] }}" />
</svg>
