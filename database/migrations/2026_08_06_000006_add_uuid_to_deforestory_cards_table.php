<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Tambah kolom `uuid` ke deforestory_cards.
 *
 * UUID adalah identifier stabil & portable antar environment (id auto-increment
 * beda antara dev & produksi; uuid tetap). Auto-generate saat card dibuat (lihat
 * DeforestoryCard::booted() → creating hook). Tetap disimpan sebagai kolom biasa
 * — endpoint update tetap pakai {id}, bukan uuid. Response card menyertakan uuid.
 *
 * Backfill card yang sudah ada: generate uuid per baris (Str::uuid, format sama
 * dengan yang di-generate app) lewat loop PHP — driver-safe (uuid generation di
 * SQL beda per DB: MySQL UUID() vs SQLite gak punya). Unique index dipasang setelah
 * backfill biar gak bentrok saat menulis baris demi baris.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Backfill card yang sudah ada dengan uuid unik (Str::uuid, sama dengan
        // yang di-generate model saat create).
        $ids = DB::table('deforestory_cards')->whereNull('uuid')->pluck('id');
        foreach ($ids as $id) {
            DB::table('deforestory_cards')
                ->where('id', $id)
                ->update(['uuid' => (string) Str::uuid()]);
        }

        // Setelah semua baris punya uuid → pasang unique index.
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->unique('uuid', 'deforestory_cards_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::table('deforestory_cards', function (Blueprint $table) {
            $table->dropUnique('deforestory_cards_uuid_unique');
            $table->dropColumn('uuid');
        });
    }
};