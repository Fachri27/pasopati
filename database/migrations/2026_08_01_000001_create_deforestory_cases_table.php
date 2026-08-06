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
        Schema::create('deforestory_cases', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->string('featured_image')->nullable(); // cover kartu index
            $table->string('category')->nullable();        // badge kartu index, mis. "sawit pulp"
            $table->string('year')->nullable();            // badge kartu index
            $table->integer('sort')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('deforestory_case_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('deforestory_cases')->onDelete('cascade');
            $table->string('locale', 5); // id, en
            $table->string('title');
            $table->text('intro')->nullable();            // paragraf header arsip
            $table->text('excerpt')->nullable();          // teks pendek kartu index
            $table->longText('laporan_content')->nullable(); // TinyMCE HTML halaman laporan
            // chapters: array of {
            //   year, label, title, image, is_cta, body (HTML), cta_text
            // }
            $table->json('chapters')->nullable();
            $table->timestamps();

            $table->unique(['case_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deforestory_case_translations');
        Schema::dropIfExists('deforestory_cases');
    }
};