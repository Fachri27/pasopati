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
        Schema::table('fellowship_translations', function (Blueprint $table) {
            $table->renameColumn('image-cover', 'image_cover');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellowship_translations', function (Blueprint $table) {
            $table->renameColumn('image-cover', 'image_cover');
        });
    }
};
