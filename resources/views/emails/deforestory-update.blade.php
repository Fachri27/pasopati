@php
    // Notifikasi email saat kasus/laporan Deforestory di-publish (DeforestoryUpdateMail).
    // Single-locale per subscriber. Tampilan = layout "Deforestory Dispatch" yang
    // dipakai bareng sama email deforestory-card lewat partial — biar gak bisa drift.
    $isId = $locale === 'id';
    $title = $case->translation($locale)?->title ?? $case->translation('id')?->title ?? $case->slug;
    $excerpt = $case->translation($locale)?->excerpt ?? $case->translation('id')?->excerpt ?? '';
    $caseUrl = route('deforestory.case', ['locale' => $locale, 'slug' => $case->slug]);
    $unsubscribeUrl = route('deforestory.unsubscribe', ['locale' => $locale, 'token' => $subscriber->unsubscribe_token]);

    $imagePath = $case->featured_image ?: null;
    $imageUrl = $imagePath
        ? (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://']) ? $imagePath : asset('storage/' . $imagePath))
        : null;

    $dateText = ($case->created_at ? \Carbon\Carbon::parse($case->created_at)->locale($locale)->translatedFormat('d F Y') : '');

    if ($event === 'created') {
        $tag = $isId ? 'Baru' : 'New';
        if ($isCaseSpecific) {
            $label = $isId ? 'Kasus Baru · Yang Anda Ikuti' : 'New Case · You Follow';
        } else {
            $label = $isId ? 'Kasus Baru' : 'New Case';
        }
    } else {
        $tag = $isId ? 'Pembaruan' : 'Update';
        $label = $isCaseSpecific
            ? ($isId ? 'Pembaruan · Yang Anda Ikuti' : 'Update · You Follow')
            : ($isId ? 'Pembaruan Kasus' : 'Case Update');
    }
@endphp
@include('emails.partials.deforestory-dispatch', [
    'brand' => 'PASOPATI',
    'subline' => 'Deforestory',
    'tag' => $tag,
    'label' => $label,
    'title' => $title,
    'description' => $excerpt,
    'imageUrl' => $imageUrl,
    'dateLabel' => $isId ? 'DITERBITKAN' : 'PUBLISHED',
    'dateText' => $dateText,
    'buttonLabel' => $isId ? 'Baca Arsip' : 'Read Archive',
    'buttonUrl' => $caseUrl,
    'reason' => $isId
        ? 'Anda menerima email ini karena berlangganan pembaruan Deforestory di Pasopati.'
        : 'You receive this email because you subscribed to Deforestory updates on Pasopati.',
    'unsubscribeLabel' => $isId ? 'Berhenti berlangganan / Unsubscribe' : 'Unsubscribe',
    'unsubscribeUrl' => $unsubscribeUrl,
    'footWordmark' => 'PASOPATI · Deforestory',
    'lang' => $locale,
])