<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deforestory_laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('deforestory_cases')->onDelete('cascade');
            $table->string('slug');                       // diturunkan dari judul laporan
            $table->string('image')->nullable();          // gambar laporan
            $table->integer('sort')->default(0);
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->timestamps();

            // Slug unik per kasus (URL: /deforestory/{caseSlug}/{laporanSlug}).
            $table->unique(['case_id', 'slug']);
        });

        Schema::create('deforestory_laporan_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('deforestory_laporans')->onDelete('cascade');
            $table->string('locale', 5); // id, en
            $table->string('title');
            $table->text('excerpt')->nullable();   // deskripsi singkat (kartu arsip)
            $table->longText('content')->nullable(); // isi laporan (TinyMCE HTML)
            $table->timestamps();

            $table->unique(['laporan_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deforestory_laporan_translations');
        Schema::dropIfExists('deforestory_laporans');
    }
};
