<?php

namespace App\Livewire\Fellowship;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\{Fellowship, FellowshipTranslation, Kategori};
use Livewire\{Component, WithFileUploads};

class FellowshipForm extends Component
{
    use WithFileUploads;

    /* ======================================================
    |  PROPERTIES
    ====================================================== */

    public $fellowship;
    public $fellowshipId;

    public $categoriesData = [];
    public $selectedCategories = [];
    public $selectedKategori = [];

    public $title_id, $title_en;
    public $sub_judul_id, $sub_judul_en;
    public $excerpt_id, $excerpt_en;

    public $start_date;
    public $end_date;
    public $status;

    /* ================= IMAGES PER LOCALE ================= */

    public $image_id;
    public $image_en;

    public $image_cover_id;
    public $image_cover_en;

    public $old_image_id;
    public $old_image_en;

    public $old_image_cover_id;
    public $old_image_cover_en;

    protected $listeners = ['updateCategoryContent' => 'updateCategoryContent'];

    /* ======================================================
    |  CATEGORY LISTENER
    ====================================================== */

    public function updateCategoryContent($payload)
    {
        $catId = $payload['catId'];
        $locale = $payload['locale'];
        $content = $payload['content'];

        if (! isset($this->selectedKategori[$catId])) {
            $this->selectedKategori[$catId] = [];
        }

        $this->selectedKategori[$catId][$locale] = $content;
    }

    /* ======================================================
    |  MOUNT (EDIT MODE)
    ====================================================== */

    public function mount($fellowshipId = null)
    {
        $this->fellowshipId = $fellowshipId;

        $this->categoriesData = Kategori::with('translations')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'id_name' => optional($cat->translations->firstWhere('locale', 'id'))->kategori_name,
                    'en_name' => optional($cat->translations->firstWhere('locale', 'en'))->kategori_name,
                ];
            })->toArray();

        if (! $fellowshipId) return;

        $this->fellowship = Fellowship::with('translations', 'kategoris')
            ->findOrFail($fellowshipId);

        $idTrans = $this->fellowship->translations->firstWhere('locale', 'id');
        $enTrans = $this->fellowship->translations->firstWhere('locale', 'en');

        /* ========= FILL DATA ========= */

        $this->fill([
            'title_id' => $idTrans->title ?? '',
            'title_en' => $enTrans->title ?? '',
            'sub_judul_id' => $idTrans->sub_judul ?? '',
            'sub_judul_en' => $enTrans->sub_judul ?? '',
            'excerpt_id' => $idTrans->excerpt ?? '',
            'excerpt_en' => $enTrans->excerpt ?? '',
            'start_date' => $this->fellowship->start_date,
            'end_date' => $this->fellowship->end_date,
            'status' => $this->fellowship->status,
        ]);

        /* ========= OLD IMAGES ========= */

        $this->old_image_id = $idTrans->image ?? null;
        $this->old_image_en = $enTrans->image ?? null;

        $this->old_image_cover_id = $idTrans->image_cover ?? null;
        $this->old_image_cover_en = $enTrans->image_cover ?? null;

        /* ========= CATEGORY ========= */

        foreach ($this->fellowship->kategoris as $kategori) {
            $this->selectedCategories[] = $kategori->id;

            $this->selectedKategori[$kategori->id] = [
                'id' => $kategori->pivot->content_id,
                'en' => $kategori->pivot->content_en,
                'status' => $kategori->pivot->status,
            ];
        }
    }

    /* ======================================================
    |  HELPER UPLOAD (REUSABLE)
    ====================================================== */

    private function uploadImage($file, $oldPath, $title, $suffix)
    {
        if (! $file) return $oldPath;

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $filename = Str::slug($title) . '-' . $suffix . '-' . time() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('fellowship', $filename, 'public');
    }

    /* ======================================================
    |  SAVE
    ====================================================== */

    public function save()
    {
        $this->validate([
            'title_id' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'start_date' => 'required|date',
            'status' => 'required|in:draft,active,inactive',

            'image_id' => 'nullable|image',
            'image_en' => 'nullable|image',
            'image_cover_id' => 'nullable|image',
            'image_cover_en' => 'nullable|image',
        ]);

        /* ========= CREATE / UPDATE MAIN ========= */

        if ($this->fellowship && $this->fellowship->exists) {
            $fellowship = $this->fellowship;

            $fellowship->update([
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);
        } else {
            $fellowship = Fellowship::create([
                'slug' => Str::slug($this->title_id),
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
                'user_id' => auth()->id(),
            ]);
        }

        /* ========= SAVE TRANSLATIONS + IMAGES ========= */

        foreach (['id', 'en'] as $locale) {

            $image = $this->uploadImage(
                $this->{'image_' . $locale},
                $this->{'old_image_' . $locale},
                $this->{'title_' . $locale},
                'image'
            );

            $cover = $this->uploadImage(
                $this->{'image_cover_' . $locale},
                $this->{'old_image_cover_' . $locale},
                $this->{'title_' . $locale},
                'cover'
            );

            FellowshipTranslation::updateOrCreate(
                [
                    'fellowship_id' => $fellowship->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $this->{'title_' . $locale},
                    'sub_judul' => $this->{'sub_judul_' . $locale},
                    'excerpt' => $this->{'excerpt_' . $locale},
                    'image' => $image,
                    'image_cover' => $cover,
                ]
            );

            
        }

        /* ========= SAVE CATEGORIES ========= */

        if ($this->selectedCategories) {
            foreach ($this->selectedCategories as $catId) {
                $data = $this->selectedKategori[$catId] ?? [];

                $fellowship->kategoris()->syncWithoutDetaching([
                    $catId => [
                        'status' => $data['status'] ?? null,
                        'content_id' => $data['id'] ?? null,
                        'content_en' => $data['en'] ?? null,
                    ],
                ]);
            }
        }

        session()->flash('success', 'Fellowship berhasil disimpan');

        return redirect()->route('fellowship.index');
    }

    /* ======================================================
    |  RENDER
    ====================================================== */

    public function render()
    {
        return view('livewire.fellowship.fellowship-form')
            ->layout('layouts.admin');
    }
}
