<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom published_at (tanggal publikasi laporan).
     */
    public function up(): void
    {
        Schema::table('deforestory_laporans', function (Blueprint $table) {
            $table->date('published_at')->nullable()->after('sort');
        });
    }

    public function down(): void
    {
        Schema::table('deforestory_laporans', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
