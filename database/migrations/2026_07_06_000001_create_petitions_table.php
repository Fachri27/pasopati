<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petitions', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 200)->unique();
            $table->string('target_name');
            $table->json('demands');
            $table->string('cover_image', 300)->nullable();
            $table->unsignedInteger('goal_count');
            $table->enum('status', ['draft', 'active', 'closed', 'succeeded'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petitions');
    }
};
