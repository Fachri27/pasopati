<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('image_id', 300)->nullable();
            $table->string('image_en', 300)->nullable();
            $table->string('title_id', 255);
            $table->string('title_en', 255);
            $table->date('event_date');
            $table->string('location', 255);
            $table->decimal('location_lat', 10, 7);
            $table->decimal('location_lng', 10, 7);
            $table->json('location_geojson')->nullable();
            $table->enum('orientation', ['landscape', 'horizontal'])->default('landscape');
            $table->timestamps();

            $table->index('event_date');
            $table->index('orientation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
