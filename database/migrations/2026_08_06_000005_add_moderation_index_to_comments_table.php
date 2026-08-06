<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah index untuk query moderasi admin komentar.
 *
 * Hot path publik (CommentSection::render: WHERE commentable_type + commentable_id
 * + is_approved) SUDAH dilayani index composite `comments_commentable_index` yang
 * ditambah saat comments jadi polymorphic (migration 2026_08_02_020000). Begitu juga
 * parent_id (FK index), reactions (comment_id+user_id / comment_id+ip_address) — semua
 * sudah ber-index. Jadi hot path publik sudah optimal (EXPLAIN = Index lookup, cost ~2).
 *
 * Satu-satunya path yang belum terlayani adalah moderasi admin di DashboardController:
 *   - Comment::where('is_approved', false)->count()
 *   - Comment::where('is_approved', false)->latest()->take(5)->get()
 * Filter `is_approved` saja (tanpa commentable_type/page_id) gak bisa pakai index
 * composite yang ada (kolom utamanya commentable_type / page_id), jadi MySQL jadi
 * full table scan + filesort. Index `(is_approved, created_at)` menutup itu:
 *   - is_approved (equality) sebagai kolom utama
 *   - created_at menyusul → ORDER BY created_at DESC terlayani index, gak filesort.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Index untuk moderasi admin: filter is_approved + urut created_at.
            $table->index(['is_approved', 'created_at'], 'comments_is_approved_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_is_approved_created_at_index');
        });
    }
};