<?php

namespace Tests\Feature;

use App\Enums\EventOrientation;
use App\Models\Event;
use App\Models\User;
use App\Services\GeoServerService;
use App\Services\VideoThumbnailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    protected function videoPayload(array $overrides = []): array
    {
        return array_merge($this->basePayload(), [
            'video' => UploadedFile::fake()->create('kejadian.mp4', 2048, 'video/mp4'),
        ], $overrides);
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'title_id' => 'Banjir Bandung',
            'title_en' => 'Bandung Flood',
            'event_date' => '2026-08-01',
            'location' => 'Bandung, Jawa Barat',
            'location_lat' => -6.917500,
            'location_lng' => 107.619100,
            'location_geojson' => '{"type":"Point","coordinates":[107.6191,-6.9175]}',
            'orientation' => 'landscape',
        ], $overrides);
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge($this->basePayload(), [
            'image_id' => UploadedFile::fake()->image('foto-id.png', 200, 120),
            'image_en' => UploadedFile::fake()->image('foto-en.png', 200, 120),
        ], $overrides);
    }

    protected function payloadWithoutImages(array $overrides = []): array
    {
        return $this->basePayload($overrides);
    }

    protected function createEvent(array $overrides = []): Event
    {
        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->payload($overrides))
            ->assertRedirect();

        return Event::orderByDesc('id')->firstOrFail();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('events.index'))->assertRedirect('/login');
    }

    public function test_non_admin_role_gets_forbidden(): void
    {
        $commenter = User::factory()->create(['role' => 'commenter']);

        $this->actingAs($commenter)->get(route('events.index'))->assertForbidden();
    }

    public function test_admin_can_create_event_with_images(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('events.store'), $this->payload());

        $event = Event::first();

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title_id' => 'Banjir Bandung',
            'title_en' => 'Bandung Flood',
            'location_lat' => -6.9175,
            'orientation' => 'landscape',
        ]);
        $this->assertSame('2026-08-01', $event->event_date->format('Y-m-d'));

        $this->assertNotNull($event->image_id);
        $this->assertNotNull($event->image_en);
        Storage::disk('public')->assertExists($event->image_id);
        Storage::disk('public')->assertExists($event->image_en);

        $this->assertSame('Point', $event->location_geojson['type']);
    }

    public function test_create_requires_all_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('events.store'), [])
            ->assertSessionHasErrors([
                'image_id', 'image_en', 'title_id', 'title_en',
                'event_date', 'location', 'orientation',
            ]);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_create_requires_image_or_video(): void
    {
        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->basePayload())
            ->assertSessionHasErrors(['image_id', 'image_en']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_create_rejects_invalid_orientation(): void
    {
        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->payload(['orientation' => 'square']))
            ->assertSessionHasErrors(['orientation']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_admin_can_list_and_filter_events(): void
    {
        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->payload());
        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->payload([
                'title_id' => 'Kebakaran Hutan',
                'title_en' => 'Forest Fire',
                'event_date' => '2026-07-15',
                'orientation' => 'horizontal',
            ]));

        $this->get(route('events.index'))
            ->assertSee('Banjir Bandung')
            ->assertSee('Kebakaran Hutan');

        $this->get(route('events.index', ['search' => 'Banjir']))
            ->assertSee('Banjir Bandung')
            ->assertDontSee('Kebakaran Hutan');

        $this->get(route('events.index', ['orientation' => 'horizontal']))
            ->assertSee('Kebakaran Hutan')
            ->assertDontSee('Banjir Bandung');

        $this->get(route('events.index', ['date_from' => '2026-08-01']))
            ->assertSee('Banjir Bandung')
            ->assertDontSee('Kebakaran Hutan');
    }

    public function test_admin_can_show_event_detail(): void
    {
        $event = $this->createEvent();

        $this->actingAs($this->admin())
            ->get(route('events.show', $event))
            ->assertSee('Banjir Bandung')
            ->assertSee('Bandung Flood')
            ->assertSee('Bandung, Jawa Barat')
            ->assertSee('Landscape');
    }

    public function test_update_keeps_old_images_when_not_replaced(): void
    {
        $event = $this->createEvent();
        $oldId = $event->image_id;
        $oldEn = $event->image_en;

        $response = $this->actingAs($this->admin())
            ->put(route('events.update', $event), $this->payloadWithoutImages([
                'title_id' => 'Updated Title',
            ]));

        $event->refresh();

        $response->assertRedirect(route('events.show', $event));
        $this->assertSame('Updated Title', $event->title_id);
        $this->assertSame($oldId, $event->image_id, 'image_id harus dipertahankan');
        $this->assertSame($oldEn, $event->image_en, 'image_en harus dipertahankan');
        Storage::disk('public')->assertExists($oldId);
        Storage::disk('public')->assertExists($oldEn);
    }

    public function test_update_replaces_image_and_deletes_old_file(): void
    {
        $event = $this->createEvent();
        $oldId = $event->image_id;
        $oldEn = $event->image_en;

        $newId = UploadedFile::fake()->image('baru-id.png', 300, 200);

        $this->actingAs($this->admin())
            ->put(route('events.update', $event), $this->payloadWithoutImages(['image_id' => $newId]));

        $event->refresh();

        $this->assertNotSame($oldId, $event->image_id);
        $this->assertSame($oldEn, $event->image_en);
        Storage::disk('public')->assertMissing($oldId);
        Storage::disk('public')->assertExists($oldEn);
        Storage::disk('public')->assertExists($event->image_id);
    }

    public function test_delete_removes_record_and_both_images(): void
    {
        $event = $this->createEvent();
        $idPath = $event->image_id;
        $enPath = $event->image_en;

        $response = $this->actingAs($this->admin())
            ->delete(route('events.destroy', $event));

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
        Storage::disk('public')->assertMissing($idPath);
        Storage::disk('public')->assertMissing($enPath);
    }

    public function test_location_search_endpoint_returns_geoserver_results(): void
    {
        $mock = \Mockery::mock(GeoServerService::class);
        $mock->shouldReceive('searchLocations')
            ->with('bandung')
            ->once()
            ->andReturn([
                [
                    'id' => 66068,
                    'name' => '[Air Putih Kali Bandung][Selupu Rejang][Rejang Lebong][Bengkulu][Sumatra][Indonesia][73712]',
                    'latitude' => -3.4611274,
                    'longitude' => 102.6076627,
                ],
            ]);
        $this->app->instance(GeoServerService::class, $mock);

        $this->actingAs($this->admin())
            ->get('/api/locations/search?q=bandung')
            ->assertOk()
            ->assertJsonPath('0.name', '[Air Putih Kali Bandung][Selupu Rejang][Rejang Lebong][Bengkulu][Sumatra][Indonesia][73712]')
            ->assertJsonPath('0.latitude', -3.4611274);
    }

    public function test_location_search_rejects_short_query(): void
    {
        $this->actingAs($this->admin())
            ->get('/api/locations/search?q=b')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_orientation_enum_is_casted(): void
    {
        $event = $this->createEvent(['orientation' => 'horizontal']);

        $this->assertInstanceOf(EventOrientation::class, $event->orientation);
        $this->assertSame(EventOrientation::Horizontal, $event->orientation);
        $this->assertSame('Horizontal', $event->orientation->label());
    }

    public function test_admin_can_create_event_with_video_and_auto_thumbnail(): void
    {
        $mock = \Mockery::mock(VideoThumbnailService::class);
        $mock->shouldReceive('generate')
            ->once()
            ->andReturn('events/event-thumb-20260812000000-abcdefgh.jpg');
        $this->app->instance(VideoThumbnailService::class, $mock);

        $response = $this->actingAs($this->admin())
            ->post(route('events.store'), $this->videoPayload());

        $event = Event::first();

        $response->assertRedirect(route('events.show', $event));
        $this->assertNotNull($event->video);
        $this->assertStringStartsWith('events/videos/event-', $event->video);
        $this->assertTrue($event->has_video);
        $this->assertSame('events/event-thumb-20260812000000-abcdefgh.jpg', $event->image_id);
        $this->assertNull($event->image_en);
        Storage::disk('public')->assertExists($event->video);
    }

    public function test_create_with_video_keeps_uploaded_images(): void
    {
        $mock = \Mockery::mock(VideoThumbnailService::class);
        $mock->shouldNotReceive('generate');
        $this->app->instance(VideoThumbnailService::class, $mock);

        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->videoPayload([
                'image_id' => UploadedFile::fake()->image('manual-id.png', 200, 120),
                'image_en' => UploadedFile::fake()->image('manual-en.png', 200, 120),
            ]))
            ->assertRedirect();

        $event = Event::first();

        $this->assertStringStartsWith('events/event-', $event->image_id);
        $this->assertStringStartsWith('events/event-', $event->image_en);
        $this->assertNotNull($event->video);
    }

    public function test_update_adds_video_without_overwriting_manual_image(): void
    {
        $event = $this->createEvent();
        $oldImage = $event->image_id;

        $mock = \Mockery::mock(VideoThumbnailService::class);
        $mock->shouldNotReceive('generate');
        $this->app->instance(VideoThumbnailService::class, $mock);

        $this->actingAs($this->admin())
            ->put(route('events.update', $event), $this->basePayload([
                'video' => UploadedFile::fake()->create('baru.mp4', 2048, 'video/mp4'),
            ]));

        $event->refresh();

        $this->assertNotNull($event->video);
        $this->assertSame($oldImage, $event->image_id, 'gambar manual tidak boleh di-overwrite thumbnail');
    }

    public function test_update_replaces_video_and_thumbnail(): void
    {
        $event = $this->createEventWithVideo();
        $oldVideo = $event->video;
        $oldThumb = $event->image_id;

        $this->assertSame('events/event-thumb-20260812000000-abcdefgh.jpg', $oldThumb);

        $secondThumb = 'events/event-thumb-20260812020000-22222222.jpg';
        $mock = \Mockery::mock(VideoThumbnailService::class);
        $mock->shouldReceive('generate')->once()->andReturn($secondThumb);
        $this->app->instance(VideoThumbnailService::class, $mock);

        $this->actingAs($this->admin())
            ->put(route('events.update', $event), $this->basePayload([
                'video' => UploadedFile::fake()->create('ganti.mp4', 2048, 'video/mp4'),
            ]));

        $event->refresh();

        $this->assertNotSame($oldVideo, $event->video);
        $this->assertSame($secondThumb, $event->image_id);
        Storage::disk('public')->assertMissing($oldVideo);
        Storage::disk('public')->assertMissing($oldThumb);
        Storage::disk('public')->assertExists($event->video);
    }

    public function test_delete_removes_video_file(): void
    {
        $event = $this->createEventWithVideo();
        $videoPath = $event->video;
        $thumbPath = $event->image_id;

        $this->actingAs($this->admin())
            ->delete(route('events.destroy', $event));

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
        Storage::disk('public')->assertMissing($videoPath);
        Storage::disk('public')->assertMissing($thumbPath);
    }

    public function test_show_page_renders_video_player(): void
    {
        $event = $this->createEventWithVideo();

        $this->actingAs($this->admin())
            ->get(route('events.show', $event))
            ->assertSee('<video', false)
            ->assertSee($event->video_url);
    }

    /**
     * Batas unggah gambar & video sama-sama 100 MB (max:102400 KB). Diuji tepat
     * di ambangnya supaya perubahan angka tidak lolos diam-diam — batas PHP di
     * public/.user.ini sengaja dipasang lebih longgar agar Laravel yang menolak
     * dan pesan berbahasa Indonesia benar-benar sampai ke admin.
     */
    public function test_upload_accepts_files_up_to_100_mb(): void
    {
        $mock = \Mockery::mock(VideoThumbnailService::class);
        $mock->shouldReceive('generate')->andReturn('events/thumb.jpg');
        $this->app->instance(VideoThumbnailService::class, $mock);

        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->basePayload([
                'image_id' => UploadedFile::fake()->create('foto-id.jpg', 102400, 'image/jpeg'),
                'image_en' => UploadedFile::fake()->create('foto-en.jpg', 102400, 'image/jpeg'),
                'video' => UploadedFile::fake()->create('kejadian.mp4', 102400, 'video/mp4'),
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();
    }

    public function test_upload_rejects_files_over_100_mb(): void
    {
        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->basePayload([
                'image_id' => UploadedFile::fake()->create('foto-id.jpg', 102401, 'image/jpeg'),
                'video' => UploadedFile::fake()->create('kejadian.mp4', 102401, 'video/mp4'),
            ]))
            ->assertSessionHasErrors([
                'image_id' => 'Ukuran Gambar Bahasa Indonesia maksimal 100 MB.',
                'video' => 'Ukuran video maksimal 100 MB.',
            ]);
    }

    protected function createEventWithVideo(): Event
    {
        $mock = \Mockery::mock(VideoThumbnailService::class);
        $mock->shouldReceive('generate')
            ->once()
            ->andReturn('events/event-thumb-20260812000000-abcdefgh.jpg');
        $this->app->instance(VideoThumbnailService::class, $mock);

        $this->actingAs($this->admin())
            ->post(route('events.store'), $this->videoPayload())
            ->assertRedirect();

        return Event::orderByDesc('id')->firstOrFail();
    }
}
