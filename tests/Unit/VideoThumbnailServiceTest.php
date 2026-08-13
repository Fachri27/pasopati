<?php

namespace Tests\Unit;

use App\Services\VideoThumbnailService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class VideoThumbnailServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    protected function makeTinyVideo(): ?string
    {
        $path = tempnam(sys_get_temp_dir(), 'pasopati-video-').'.mp4';
        $process = new Process([
            'ffmpeg',
            '-y',
            '-f', 'lavfi',
            '-i', 'testsrc=size=160x90:rate=15:duration=1',
            '-pix_fmt', 'yuv420p',
            $path,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        return $path;
    }

    public function test_generate_extracts_thumbnail_from_real_video(): void
    {
        $video = $this->makeTinyVideo();

        if ($video === null) {
            $this->markTestSkipped('ffmpeg tidak tersedia untuk membuat video uji.');
        }

        // Service bekerja di filesystem asli (ffmpeg), jadi tulis file fisik.
        Storage::disk('public')->makeDirectory('events/videos');
        $videoPath = 'events/videos/uji.mp4';
        copy($video, Storage::disk('public')->path($videoPath));
        @unlink($video);

        $service = new VideoThumbnailService;
        $thumbnail = $service->generate($videoPath);

        $this->assertNotNull($thumbnail);
        $this->assertStringStartsWith('events/event-', $thumbnail);
        $this->assertStringEndsWith('.jpg', $thumbnail);

        $thumbnailAbsolute = Storage::disk('public')->path($thumbnail);
        $this->assertFileExists($thumbnailAbsolute);
        $this->assertStringStartsWith("\xFF\xD8", file_get_contents($thumbnailAbsolute), 'Thumbnail harus berupa file JPEG.');

        Storage::disk('public')->deleteDirectory('events');
    }

    public function test_generate_returns_null_when_video_missing(): void
    {
        $service = new VideoThumbnailService;

        $this->assertNull($service->generate('events/videos/tidak-ada.mp4'));
    }
}
