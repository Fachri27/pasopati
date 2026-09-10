<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pustaka berkas "Reference" — sumber gambar untuk tombol + Image,
 * + GLightbox, dan + Before/After di editor TinyMCE.
 *
 * Berbeda dengan Laravel File Manager yang hanya mengembalikan URL polos,
 * tabel ini menyimpan judul dan alt text tiap berkas. Editor memakainya
 * sebagai caption bawaan galeri dan sebagai alt gambar, jadi metadata itu
 * harus ikut tersimpan, bukan hanya jalur berkasnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_images', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('image_path');
            // Gambar dilayani langsung dari /storage; berkas lain (PDF, DOC,
            // video) disimpan di disk privat dan hanya keluar lewat route
            // download/preview yang berada di balik middleware auth.
            $table->string('disk', 20)->default('public');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_images');
    }
};
