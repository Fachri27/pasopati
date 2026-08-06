@php
    $isId = $locale === 'id';
    $title = $case->translation($locale)?->title ?? $case->translation('id')?->title ?? $case->slug;
    $excerpt = $case->translation($locale)?->excerpt ?? $case->translation('id')?->excerpt ?? '';
    $caseUrl = route('deforestory.case', ['locale' => $locale, 'slug' => $case->slug]);
    $unsubscribeUrl = route('deforestory.unsubscribe', ['locale' => $locale, 'token' => $subscriber->unsubscribe_token]);
@endphp

<x-mail::message>
@if ($event === 'created')
    @if ($isCaseSpecific)
# {{ $isId ? 'Kasus yang Anda Ikuti Telah Diterbitkan' : 'A Case You Follow Has Been Published' }}
    @else
# {{ $isId ? 'Kasus Deforestory Baru' : 'New Deforestory Case' }}
    @endif
@else
    @if ($isCaseSpecific)
# {{ $isId ? 'Update untuk Kasus yang Anda Ikuti' : 'Update for a Case You Follow' }}
    @else
# {{ $isId ? 'Update Kasus Deforestory' : 'Deforestory Case Updated' }}
    @endif
@endif

{{ $isCaseSpecific
    ? ($isId ? 'Kasus yang Anda ikuti memiliki pembaruan:' : 'A case you follow has an update:')
    : ($isId ? 'Kami menerbitkan arsip baru di Deforestory:' : 'We published a new archive on Deforestory:') }}

**{{ $title }}**

@if ($excerpt)
{{ strip_tags($excerpt) }}
@endif

<x-mail::button :url="$caseUrl" color="success">
{{ $isId ? 'Baca Arsip' : 'Read Archive' }}
</x-mail::button>

---

{{ $isId ? 'Anda menerima email ini karena berlangganan Deforestory.' : 'You are receiving this because you subscribed to Deforestory.' }}

{{ $isId ? 'Berhenti berlangganan:' : 'Unsubscribe:' }} [{{ $unsubscribeUrl }}]({{ $unsubscribeUrl }})

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
