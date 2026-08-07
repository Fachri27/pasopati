@php
    // Notifikasi email "kasus baru" berbasis card Deforestory (DeforestoryCardMail).
    // Single-locale per subscriber. Tampilan = layout "Deforestory Dispatch" yang
    // dipakai bareng sama email deforestory-update lewat partial — biar gak bisa drift.
    $isId = $locale === 'id';
    $imagePath = $isId ? ($card->image_id ?? null) : ($card->image_en ?? null);
    $imageUrl = $imagePath
        ? (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://']) ? $imagePath : asset('storage/' . $imagePath))
        : null;
    $dateText = ($card->created_at ? \Carbon\Carbon::parse($card->created_at)->locale($locale)->translatedFormat('d F Y') : '');
@endphp
@include('emails.partials.deforestory-dispatch', [
    'brand' => 'PASOPATI',
    'subline' => 'Deforestory',
    'tag' => $isId ? 'Baru' : 'New',
    'label' => $isId ? 'Kasus Baru' : 'New Case',
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