<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah nilai 'deforestory' ke kolom enum page_type pada tabel pages,
     * dipakai sebagai jangkar untuk komponen komentar Livewire di halaman
     * detail arsip Deforestory (mis. Mayawana). Page dengan page_type ini
     * tidak muncul di listing expose/ngopini (lihat PageController).
     */
    public function up(): void
    {
        // MODIFY ... ENUM hanya didukung MySQL. SQLite (dipakai saat testing)
        // tidak menerapkan constraint enum, jadi kolom sudah menerima nilai apa
        // pun — tidak perlu diubah.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pages MODIFY page_type ENUM('expose','ngopini','deforestory') NOT NULL DEFAULT 'expose'");
        }
    }

    public function down(): void
    {
        // Hapus baris yang memakai tipe deforestory sebelum mengecilkan enum.
        DB::table('pages')->where('page_type', 'deforestory')->delete();

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pages MODIFY page_type ENUM('expose','ngopini') NOT NULL DEFAULT 'expose'");
        }
    }
};
