<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Services\GeoServerService;
use App\Services\VideoThumbnailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected GeoServerService $geoServer,
        protected VideoThumbnailService $videoThumbnails,
    ) {}

    public function index(Request $request): View
    {
        $events = Event::query()
            ->when($search = trim((string) $request->query('search')), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title_id', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($dateFrom = $request->query('date_from'), fn ($query) => $query->whereDate('event_date', '>=', $dateFrom))
            ->when($dateTo = $request->query('date_to'), fn ($query) => $query->whereDate('event_date', '<=', $dateTo))
            ->when($orientation = $request->query('orientation'), fn ($query) => $query->where('orientation', $orientation))
            ->orderByDesc('event_date')
            ->paginate(10)
            ->withQueryString();

        return view('events.index', compact('events'));
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['location_geojson'] = $this->parseGeojson($request->input('location_geojson'));
        $data['image_id'] = $request->hasFile('image_id') ? $this->storeImage($request->file('image_id')) : null;
        $data['image_en'] = $request->hasFile('image_en') ? $this->storeImage($request->file('image_en')) : null;

        if ($request->hasFile('video')) {
            $data['video'] = $this->storeVideo($request->file('video'));

            if ($data['image_id'] === null) {
                $data['image_id'] = $this->videoThumbnails->generate($data['video']);
            }
        }

        $event = Event::create($data);

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event): View
    {
        return view('events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        return view('events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();
        $data['location_geojson'] = $this->parseGeojson($request->input('location_geojson'));

        if ($request->hasFile('image_id')) {
            $data['image_id'] = $this->storeImage($request->file('image_id'));
            $this->deleteFile($event->image_id);
        }

        if ($request->hasFile('image_en')) {
            $data['image_en'] = $this->storeImage($request->file('image_en'));
            $this->deleteFile($event->image_en);
        }

        if ($request->hasFile('video')) {
            $data['video'] = $this->storeVideo($request->file('video'));
            $this->deleteFile($event->video);

            // Regenerasi thumbnail dari video baru hanya bila event belum punya
            // gambar manual: image_id kosong, atau image_id lama berasal dari
            // video sebelumnya (has_video). Gambar yang di-upload admin tetap
            // dipertahankan (tidak di-overwrite).
            if (! $request->hasFile('image_id') && ($event->image_id === null || $event->has_video)) {
                $thumbnail = $this->videoThumbnails->generate($data['video']);

                if ($thumbnail !== null) {
                    if ($event->has_video) {
                        $this->deleteFile($event->image_id);
                    }

                    $data['image_id'] = $thumbnail;
                }
            }
        }

        $event->update($data);

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->deleteFile($event->image_id);
        $this->deleteFile($event->image_en);
        $this->deleteFile($event->video);
        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    public function searchLocations(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q'));

        if ($query === '' || mb_strlen($query) < 2) {
            return response()->json([]);
        }

        return response()->json($this->geoServer->searchLocations($query));
    }

    public function locationDetail(int $id): JsonResponse
    {
        $location = $this->geoServer->getLocation($id);

        if ($location === null) {
            return response()->json(['message' => 'Lokasi tidak ditemukan.'], 404);
        }

        return response()->json($location);
    }

    protected function storeImage(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'event-'.now()->format('YmdHis').'-'.Str::lower(Str::random(8)).'.'.$extension;

        return $file->storeAs('events', $filename, 'public');
    }

    protected function storeVideo(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        $filename = 'event-'.now()->format('YmdHis').'-'.Str::lower(Str::random(8)).'.'.$extension;

        return $file->storeAs('events/videos', $filename, 'public');
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function parseGeojson(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
