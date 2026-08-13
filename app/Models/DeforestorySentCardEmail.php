<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catatan bahwa email notifikasi card untuk (subscriber, card, event) sudah
 * dikirim. Dipakai DeforestoryCardNotificationJob untuk idempotensi: baris
 * dicatat sebelum mailable di-queue, dan unique(subscriber_id, card_id, event)
 * membuat retry melewati pasangan yang sudah dikirim sehingga tidak ada email
 * ganda.
 */
class DeforestorySentCardEmail extends Model
{
    protected $table = 'deforestory_sent_card_emails';

    protected $fillable = [
        'subscriber_id',
        'card_id',
        'event',
    ];
}