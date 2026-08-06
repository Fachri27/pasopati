@php
    $locale = app()->getLocale();
    $title = $petition->translation($locale)?->title ?? $petition->slug;
@endphp

<x-mail::message>
# {{ $locale === 'id' ? 'Verifikasi Tanda Tangan' : 'Verify Your Signature' }}

{{ $locale === 'id'
    ? 'Terima kasih telah menandatangani petisi:'
    : 'Thank you for signing the petition:' }}

**{{ $title }}**

{{ $locale === 'id'
    ? 'Klik tombol di bawah untuk memverifikasi tanda tangan Anda:'
    : 'Click the button below to verify your signature:' }}

<x-mail::button :url="$verificationUrl" color="success">
{{ $locale === 'id' ? 'Verifikasi Tanda Tangan' : 'Verify Signature' }}
</x-mail::button>

{{ $locale === 'id'
    ? 'Jika Anda tidak merasa menandatangani petisi ini, abaikan email ini.'
    : 'If you did not sign this petition, please ignore this email.' }}

<hr>

<small>{{ config('app.name') }}</small>
</x-mail::message>
