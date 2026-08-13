<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kartu kasus Deforestory yang DIDORONG web lain ke CMS via inbound webhook
 * (POST /api/deforestory/cards). Sebelumnya CMS nge-GET card dari web lain
 * (mock /api/deforestory-cases); sekarang web lain yang POST, CMS simpan lokal.
 *
 * Kedua locale (id + en) disimpan sekaligus supaya getCases('id'|'en') cukup
 * baca lokal — mirip `laporan.translations` di webhook keluar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deforestory_cards', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('year')->nullable();
            $table->string('image')->nullable();          // absolut URL
            $table->string('title_id')->nullable();
            $table->string('title_en')->nullable();
            $table->text('excerpt_id')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deforestory_cards');
    }
};
