@php
    $ptop = $data['mt'] ?? null;
    $pbot = $data['mb'] ?? null;
    $align = $data['alignment'] ?? 'center';
    $figureStyle = '';
    $imgClasses = 'h-auto shadow';
    $imgStyle = '';

    if ($align === 'full') {
        $figureStyle = 'max-width:100%; margin-inline:auto; padding-left:0; padding-right:0;';
        $imgClasses .= ' w-full';
    } elseif ($align === 'center') {
        $figureStyle = 'max-width:42rem; margin-inline:auto; padding-left:1.25rem; padding-right:1.25rem;';
        $imgClasses .= ' w-full';
    } elseif ($align === 'left') {
        $figureStyle = 'max-width:42rem; margin-inline:auto; padding-left:1.25rem; padding-right:1.25rem;';
        $imgClasses .= ' max-w-[50%]';
        $imgStyle = 'float:left; margin-right:1rem;';
    } elseif ($align === 'right') {
        $figureStyle = 'max-width:42rem; margin-inline:auto; padding-left:1.25rem; padding-right:1.25rem;';
        $imgClasses .= ' max-w-[50%]';
        $imgStyle = 'float:right; margin-left:1rem;';
    }

    $figureStyle .= ($ptop ? "margin-top:{$ptop}px;" : '') . ($pbot ? "margin-bottom:{$pbot}px;" : '');
@endphp
<figure class="my-8 media-caption {{ $align === 'full' ? '!max-w-full !px-0' : 'max-w-2xl mx-auto px-5' }}"
        style="{{ $figureStyle }}">
    <img src="{{ asset('storage/' . ($data['src'] ?? '')) }}"
         alt="{{ $data['caption'] ?? '' }}"
         class="{{ $imgClasses }}"
         style="{{ $imgStyle }}">
    @if (!empty($data['caption']))
        <figcaption>
            {{ $data['caption'] }}
        </figcaption>
    @endif
</figure>
<div class="clear-both"></div>
