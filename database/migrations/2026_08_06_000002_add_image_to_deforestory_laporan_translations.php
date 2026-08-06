<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom `image` per-locale ke deforestory_laporan_translations.
 *
 * Sebelumnya laporan hanya punya satu `image` (di deforestory_laporans). Sekarang tiap
 * locale (id/en) punya gambarnya sendiri. Kolom legacy deforestory_laporans.image tetap
 * dipertahankan sebagai fallback; di sini kita backfill image lama ke translation `id`
 * supaya data lama gak hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deforestory_laporan_translations', function (Blueprint $table) {
            $table->string('image')->nullable()->after('content');
        });

        // Backfill: salin image legacy (deforestory_laporans.image) ke translation id.
        // UPDATE...JOIN gak didukung SQLite (DB test pakai :memory:), jadi bedakan
        // per driver. Di MySQL pakai UPDATE...JOIN (efisien); di driver lain iterasi
        // baris di PHP (data terbatas, aman).
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::table('deforestory_laporan_translations as t')
                ->join('deforestory_laporans as l', 'l.id', '=', 't.laporan_id')
                ->where('t.locale', 'id')
                ->whereNull('t.image')
                ->whereNotNull('l.image')
                ->update(['t.image' => DB::raw('l.image')]);
        } else {
            $legacy = DB::table('deforestory_laporans')
                ->whereNotNull('image')
                ->pluck('image', 'id');

            foreach ($legacy as $laporanId => $image) {
                DB::table('deforestory_laporan_translations')
                    ->where('laporan_id', $laporanId)
                    ->where('locale', 'id')
                    ->whereNull('image')
                    ->update(['image' => $image]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('deforestory_laporan_translations', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};