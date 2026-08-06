<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom `status` ke deforestory_cards — kendalikan visibilitas card.
 *
 * Nilai: 'publish' (tampil di publik via getCases / halaman /id/deforestory) /
 * 'draft' (sembunyi). Default 'publish' supaya card yang di-push web lain langsung
 * tampil (perilaku lama tetap). Card lama di-backfill ke 'publish' (gak ada
 * perubahan visibilitas).
 *
 * Pengaturan: POST bisa kirim `status` (opsional — kalau gak dikirim, create dapat
 * default 'publish', update mempertahankan status lama). PUT /cards/{uuid} bisa
 * set status (field updatable) — inilah jalur utama admin publish/unpublish card.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->string('status')->default('publish')->after('slug');
        });

        // Backfill eksplisit card lama → 'publish' (aman lintas driver).
        DB::table('deforestory_cards')->whereNull('status')->orWhere('status', '')->update(['status' => 'publish']);
    }

    public function down(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};