<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan idempotensi pengiriman email notifikasi Deforestory.
 *
 * DeforestoryNotificationJob meng-loop semua subscriber lalu Mail::to()->queue()
 * per subscriber. Tanpa catatan, retry/timeout job (queue `database`,
 * retry_after=90) me-loop ulang seluruh subscriber → email ganda / spam. Baris di
 * tabel ini dicatat SEBELUM setiap mailable di-queue; unique(subscriber_id,
 * case_id, event) membuat retry melewati pasangan yang sudah dikirim.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deforestory_sent_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id');
            $table->unsignedBigInteger('case_id');
            $table->string('event', 40)->default('created');
            $table->timestamps();

            // Satu subscriber hanya boleh dicatat sekali per (case, event).
            $table->unique(['subscriber_id', 'case_id', 'event'], 'def_sent_unique');
            $table->index(['case_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deforestory_sent_emails');
    }
};