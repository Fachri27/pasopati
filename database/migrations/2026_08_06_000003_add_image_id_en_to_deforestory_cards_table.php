<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah image per-locale (id + en) ke deforestory_cards.
 *
 * Sebelumnya card hanya punya satu `image` (absolut URL dari web lain). Sekarang tiap
 * locale punya gambarnya sendiri. Kolom legacy `image` tetap dipertahankan sebagai
 * fallback untuk push web lain yang masih kirim `image` saja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->string('image_id')->nullable()->after('image');
            $table->string('image_en')->nullable()->after('image_id');
        });
    }

    public function down(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->dropColumn(['image_id', 'image_en']);
        });
    }
};