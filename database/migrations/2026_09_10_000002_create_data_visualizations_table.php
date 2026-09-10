<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data & Grafik — grafik yang disisipkan editor lewat tombol + Data/Grafik.
 *
 * Grafik tidak ditanam sebagai HTML di dalam konten, melainkan sebagai
 * <iframe> ke route embed. Satu grafik dengan demikian bisa dipakai di banyak
 * laporan sekaligus, dan memperbaruinya cukup sekali di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_visualizations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('chart_type', 20)->default('bar');
            $table->json('chart_data')->nullable();
            $table->string('provider', 30)->default('flourish');
            // Nullable: grafik yang digambar Chart.js dari chart_data tidak
            // punya URL sumber eksternal.
            $table->text('embed_url')->nullable();
            $table->text('source_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_visualizations');
    }
};
