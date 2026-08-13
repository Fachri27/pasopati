<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catatan bahwa email notifikasi untuk (subscriber, case, event) sudah dikirim.
 * Dipakai DeforestoryNotificationJob untuk idempotensi: baris dicatat sebelum
 * mailable di-queue, dan unique(subscriber_id, case_id, event) membuat retry
 * melewati pasangan yang sudah dikirim sehingga tidak ada email ganda.
 */
class DeforestorySentEmail extends Model
{
    protected $table = 'deforestory_sent_emails';

    protected $fillable = [
        'subscriber_id',
        'case_id',
        'event',
    ];
}