<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("petition_translations", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("petition_id")
                ->constrained("petitions")
                ->cascadeOnDelete();
            $table->string("locale", 5);
            $table->string("title");
            $table->text("description");

            $table->unique(["petition_id", "locale"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("petition_translations");
    }
};
