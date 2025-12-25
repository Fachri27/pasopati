<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE pages
            SET expose_type = JSON_ARRAY(expose_type)
            WHERE JSON_VALID(expose_type) = 0
            AND expose_type IS NOT NULL
            AND expose_type != ''
        ");

        // set default kosong
        DB::statement("
            UPDATE pages
            SET expose_type = '[]'
            WHERE expose_type IS NULL
            OR expose_type = ''
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
