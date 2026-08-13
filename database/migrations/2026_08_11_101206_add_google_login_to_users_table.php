<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Login Google untuk kolom komentar (Laravel Socialite):
     * - google_id: relasi akun Google (unique). User Google tidak punya password.
     * - password: jadi nullable (user Google login via Google, bukan password).
     * - role: tambah 'commenter' — role khusus commenter yang TIDAK boleh akses
     *   route admin (middleware role:admin,editor). Kalau diberi 'editor' default,
     *   commenter bisa kebocoran akses dashboard.
     *
     * `->change()` dipakai supaya sekali jalan di MySQL (ALTER COLUMN) maupun
     * SQLite (rebuild tabel + CHECK constraint baru).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->enum('role', ['admin', 'editor', 'commenter'])->default('editor')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan enum ke daftar lama (data 'commenter' harus diubah dulu
            // bila ada, supaya tidak melanggar CHECK lama).
            if (DB::table('users')->where('role', 'commenter')->exists()) {
                DB::table('users')->where('role', 'commenter')->update(['role' => 'editor']);
            }
            $table->enum('role', ['admin', 'editor'])->default('editor')->change();
            $table->string('password')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn('google_id');
        });
    }
};
