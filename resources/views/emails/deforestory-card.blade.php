<x-mail::message>
# {{ $isId ? 'Kasus Deforestory Baru' : 'New Deforestory Case' }}

{{ $isId
    ? 'Kami menerbitkan arsip baru di Deforestory:'
    : 'We published a new archive on Deforestory:' }}

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