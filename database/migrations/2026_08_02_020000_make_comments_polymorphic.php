<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan tabel comments polymorphic supaya bisa dipakai model apa pun
     * (Page, DeforestoryLaporan, dst.) lewat commentable_type + commentable_id.
     * Komentar Page yang sudah ada di-backfill ke kolom polymorphic.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // page_id jadi nullable: komentar non-Page (mis. laporan) tidak punya page.
            $table->unsignedBigInteger('page_id')->nullable()->change();

            $table->string('commentable_type', 190)->nullable()->after('page_id');
            $table->unsignedBigInteger('commentable_id')->nullable()->after('commentable_type');
            $table->index(['commentable_type', 'commentable_id', 'is_approved'], 'comments_commentable_index');
        });

        // Backfill komentar Page yang sudah ada.
        DB::table('comments')
            ->whereNull('commentable_id')
            ->update([
                'commentable_type' => 'App\\Models\\Page',
                'commentable_id' => DB::raw('page_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_commentable_index');
            $table->dropColumn(['commentable_type', 'commentable_id']);
            // page_id tetap nullable: mungkin ada komentar non-Page.
        });
    }
};