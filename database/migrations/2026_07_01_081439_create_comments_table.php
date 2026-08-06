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
        Schema::create("comments", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("page_id")
                ->constrained("pages")
                ->cascadeOnDelete();
            $table
                ->foreignId("user_id")
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string("name")->nullable(); // untuk guest comment
            $table->string("email")->nullable();
            $table->text("body");
            $table->string("ip_address", 45)->nullable();
            $table->boolean("is_approved")->default(true); // bisa diubah ke false kalau mau moderasi manual
            $table
                ->foreignId("parent_id")
                ->nullable()
                ->constrained("comments")
                ->cascadeOnDelete();
            $table->timestamps();

            $table->index(["page_id", "is_approved"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("comments");
    }
};
