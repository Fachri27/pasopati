<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Penyaji berkas Reference non-publik.
 *
 * Gambar dilayani langsung dari /storage karena harus bisa dimuat pembaca.
 * PDF, dokumen, dan video justru disimpan di disk privat: berkas kerja
 * redaksi tidak boleh bocor hanya karena jalurnya tertebak, jadi keduanya
 * hanya keluar lewat route ini yang berada di balik middleware auth.
 */
class ReferenceController extends Controller
{
    public function download(int $id)
    {
        $file = DB::table('reference_images')->find($id);
        abort_if($file === null, 404);

        $disk = $file->disk ?: 'public';
        abort_unless(Storage::disk($disk)->exists($file->image_path), 404);

        return Storage::disk($disk)->download(
            $file->image_path,
            $file->original_name ?: basename($file->image_path)
        );
    }

    public function preview(int $id)
    {
        $file = DB::table('reference_images')->find($id);
        abort_if($file === null, 404);
        $mimeType = $file->mime_type ?: '';
        // Hanya yang bisa dirender <video>/<iframe>. Sisanya lewat download,
        // supaya HTML atau SVG unggahan tidak pernah dieksekusi inline pada
        // origin CMS.
        abort_unless(str_starts_with($mimeType, 'video/') || $mimeType === 'application/pdf', 404);

        $disk = $file->disk ?: 'public';
        abort_unless(Storage::disk($disk)->exists($file->image_path), 404);

        return Storage::disk($disk)->response(
            $file->image_path,
            $file->original_name ?: basename($file->image_path),
            ['Content-Type' => $mimeType],
            'inline'
        );
    }
}
