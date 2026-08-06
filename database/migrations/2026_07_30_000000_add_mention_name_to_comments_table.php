<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("comments", function (Blueprint $table) {
            // Untuk reply-style Instagram: balasan ke balasan tetap nempel
            // di root comment (tidak nesting lebih dalam), tapi mencatat
            // nama author yang di-mention agar bisa ditampilkan sebagai @mention.
            $table->string("mention_name")->nullable()->after("parent_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("comments", function (Blueprint $table) {
            $table->dropColumn("mention_name");
        });
    }
};