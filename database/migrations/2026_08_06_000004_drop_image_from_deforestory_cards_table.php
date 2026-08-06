<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus kolom legacy `image` dari deforestory_cards.
 *
 * Card sekarang punya image per-locale: `image_id` + `image_en` (ditambah di
 * migration 000003). Kolom `image` tunggal jadi redundan — di-drop saja.
 * Konsumen (toCardArray) udah pakai image per-locale.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('deforestory_cards', 'image')) {
            Schema::table('deforestory_cards', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }

    public function down(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->string('image')->nullable()->after('year');
        });
    }
};