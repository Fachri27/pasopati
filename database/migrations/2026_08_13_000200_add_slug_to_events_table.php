<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/*
 * Kolom `slug` untuk Event — dipakai tautan share pop-up rincian
 * (?event=<slug>) menggantikan id numerik, supaya URL terbaca sebagai
 * judul kejadian alih-alih angka. Slug dibangun dari `title_id`, stabil
 * saat judul diubah (hanya diisi saat kosong), dan unik per event.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title_id');
        });

        // Isi slug untuk event yang sudah ada, lalu pasang index unik setelah
        // seluruh baris terisi — kalau index dipasang lebih dulu, backfill
        // untuk dua event dengan judul sama akan menabrak constraint.
        foreach (Event::all() as $event) {
            $dasar = Str::slug($event->title_id) ?: ('event-' . $event->id);
            $slug = $dasar;
            $i = 1;
            while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                $slug = $dasar . '-' . ++$i;
            }
            $event->slug = $slug;
            $event->save();
        }

        Schema::table('events', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};