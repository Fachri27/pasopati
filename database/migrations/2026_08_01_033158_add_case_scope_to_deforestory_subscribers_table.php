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
        Schema::table('deforestory_subscribers', function (Blueprint $table) {
            $table->enum('type', ['all', 'case'])->default('all')->after('email');
            $table->foreignId('case_id')->nullable()->constrained('deforestory_cases')->onDelete('cascade')->after('type');
            $table->dropUnique('deforestory_subscribers_email_unique');
            $table->unique(['email', 'type', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::table('deforestory_subscribers', function (Blueprint $table) {
            $table->dropUnique(['email', 'type', 'case_id']);
            $table->unique('email');
            $table->dropColumn(['type', 'case_id']);
        });
    }
};
