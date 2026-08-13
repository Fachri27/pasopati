<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Generate thumbnail (screenshot/frame) dari file video menggunakan ffmpeg.
 *
 * Dipakai saat event menyertakan file video (.mp4 dll) — frame video diambil
 * pada detik tertentu lalu disimpan ke disk public. Hasilnya dipakai sebagai
 * thumbnail otomatis (fallback image_id) bila admin tidak meng-upload gambar.
 */
class VideoThumbnailService
{
    /**
     * Buat thumbnail dari file video yang sudah tersimpan di disk public.
     *
     * @param  string  $videoPath  path relatif video di disk public.
     * @param  string|null  $outputPath  path output thumbnail (default: auto).
     * @return string|null path relatif thumbnail di disk public, null bila gagal.
     */
    public function generate(string $videoPath, string $disk = 'public', ?string $outputPath = null): ?string
    {
        try {
            $videoAbsolute = Storage::disk($disk)->path($videoPath);

            if (! is_file($videoAbsolute)) {
                Log::warning('VideoThumbnailService: file video tidak ditemukan.', ['video' => $videoPath]);

                return null;
            }

            $outputPath ??= 'events/event-'.now()->format('YmdHis').'-'.Str::lower(Str::random(8)).'.jpg';
            $outputAbsolute = Storage::disk($disk)->path($outputPath);

            $ffmpeg = (string) config('services.video.ffmpeg_path', 'ffmpeg');
            $timeout = (int) config('services.video.timeout', 30);
            $baseArgs = [
                $ffmpeg,
                '-y',
                '-i', $videoAbsolute,
                '-frames:v', '1',
                '-pix_fmt', 'yuvj420p',
                '-q:v', '2',
                $outputAbsolute,
            ];

            // Coba ambil frame pada detik yang dikonfigurasi; bila gagal (video
            // lebih pendek dari seek), ulangi dari awal video.
            foreach ([$this->seekSeconds(), null] as $seek) {
                $args = $baseArgs;

                if ($seek !== null) {
                    array_splice($args, 2, 0, ['-ss', (string) $seek]);
                }

                $process = new Process($args);
                $process->setTimeout($timeout);
                $process->run();

                if ($process->isSuccessful() && is_file($outputAbsolute)) {
                    return $outputPath;
                }

                Log::warning('VideoThumbnailService: ffmpeg gagal.', [
                    'video' => $videoPath,
                    'seek' => $seek ?? '0',
                    'error' => trim($process->getErrorOutput()),
                ]);
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('VideoThumbnailService: error tidak terduga.', [
                'video' => $videoPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function seekSeconds(): int
    {
        return (int) config('services.video.thumbnail_seek', 1);
    }
}
