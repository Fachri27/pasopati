<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petition_signatures', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('petition_id')
                ->constrained('petitions')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('city', 100)->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('verification_token', 64)->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['petition_id', 'email']);
            $table->index(['petition_id', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petition_signatures');
    }
};
