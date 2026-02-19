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
        Schema::table('fellowships', function (Blueprint $table) {
            if (!Schema::hasColumn('fellowships', 'image_id')) {
                $table->string('image_id')->nullable()->after('image2');
            }
            if (!Schema::hasColumn('fellowships', 'image_en')) {
                $table->string('image_en')->nullable()->after('image_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellowships', function (Blueprint $table) {
            if (Schema::hasColumn('fellowships', 'image_id')) {
                $table->dropColumn('image_id');
            }
            if (Schema::hasColumn('fellowships', 'image_en')) {
                $table->dropColumn('image_en');
            }
        });
    }
};
