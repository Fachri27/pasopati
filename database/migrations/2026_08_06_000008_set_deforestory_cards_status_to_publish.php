<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah nilai `status` deforestory_cards dari 'active' → 'publish'.
 *
 * Migration 000007 awalnya pakai 'active'/'inactive' (mengikuti konvensi
 * DeforestoryCase). Ternyata nilai yang diinginkan: 'publish' (tampil di
 * halaman /id/deforestory) & 'draft' (sembunyi). Migration ini menyesuaikan:
 *  - default kolom → 'publish'
 *  - semua card yang masih 'active' → 'publish' (supaya tetap tampil, gak
 *    ke-sembunyiin karena filter getCases pakai 'publish').
 *
 * Migration 000007 sendiri sudah dikoreksi ke 'publish' untuk instalasi baru;
 * migration ini cuma untuk DB yang sudah jalan 000007 versi 'active'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->string('status')->default('publish')->change();
        });

        DB::table('deforestory_cards')->where('status', 'active')->update(['status' => 'publish']);
    }

    public function down(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });

        DB::table('deforestory_cards')->where('status', 'publish')->update(['status' => 'active']);
    }
};
