<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan idempotensi pengiriman email notifikasi card Deforestory (card baru
 * masuk dari simontini lewat inbound webhook).
 *
 * DeforestoryCardNotificationJob meng-loop subscriber type=all lalu
 * Mail::to()->queue() per subscriber. Tanpa catatan, retry/timeout job (queue
 * `database`, retry_after=90) me-loop ulang seluruh subscriber → email ganda.
 * Baris dicatat SEBELUM mailable di-queue; unique(subscriber_id, card_id, event)
 * membuat retry melewati pasangan yang sudah dikirim.
 *
 * Terpisah dari deforestory_sent_emails (yang berbasis DeforestoryCase) supaya
 * jalur card simontini tetap berskema sendiri dan tidak mengganggu tabel case.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deforestory_sent_card_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id');
            $table->unsignedBigInteger('card_id');
            $table->string('event', 40)->default('created');
            $table->timestamps();

            // Satu subscriber hanya boleh dicatat sekali per (card, event).
            $table->unique(['subscriber_id', 'card_id', 'event'], 'def_sent_card_unique');
            $table->index(['card_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deforestory_sent_card_emails');
    }
};