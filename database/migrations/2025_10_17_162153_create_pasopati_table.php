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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('type', ['parallax', 'default'])->default('default');
            $table->enum('page_type', ['expose', 'ngopini'])->default('expose');
            $table->string('featured_image')->nullable();
            $table->date('published_at')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->enum('source_type', ['manual', 'docx'])->default('manual');
            $table->string('source_file')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete(action: 'cascade');
            $table->timestamps();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->onDelete('cascade');
            $table->string('locale', 5); // id, en, dll
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'locale']); // satu page, satu locale
        });

        // Fellowship
        Schema::create('fellowships', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('meta_image')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete(action: 'cascade');
            $table->timestamps();
        });

        // Fellowship translations
        Schema::create('fellowship_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellowship_id')->constrained('fellowships')->cascadeOnDelete();
            $table->string('locale', 5)->index(); // contoh: 'id', 'en'
            $table->string('title');
            $table->string('sub_judul');
            $table->text('excerpt')->nullable();
            $table->timestamps();

            $table->unique(['fellowship_id', 'locale']);
        });

        // Kategori
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Kategori translations
        Schema::create('kategori_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('kategori_name');
            $table->timestamps();

            $table->unique(['kategori_id', 'locale']);
        });

        // Pivot: kategori_fellowship
        Schema::create('kategori_fellowships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellowship_id')->constrained('fellowships')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->text('content_id')->nullable();
            $table->text('content_en')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->timestamps();

            $table->unique(['fellowship_id', 'kategori_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('kategori_fellowship');
        Schema::dropIfExists('kategori_translations');
        Schema::dropIfExists('kategori');
        Schema::dropIfExists('fellowship_translations');
        Schema::dropIfExists('fellowships');
    }
};
