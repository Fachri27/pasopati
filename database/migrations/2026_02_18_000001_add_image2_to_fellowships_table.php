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
            if (! Schema::hasColumn('fellowships', 'image2')) {
                $table->string('image2')->nullable()->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellowships', function (Blueprint $table) {
            if (Schema::hasColumn('fellowships', 'image2')) {
                $table->dropColumn('image2');
            }
        });
    }
};
