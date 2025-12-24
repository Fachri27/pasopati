<?php

namespace App\Livewire\Fellowship;

use App\Models\Fellowship;
use App\Models\FellowshipTranslation;
use App\Models\Kategori;
use App\Models\KategoriFellowship;
use App\Models\KategoriTranslation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class FellowshipForm extends Component
{
    use WithFileUploads;

    public $categories = [];

    public $fellowship;

    public $categoriesData = [];

    public $selectedCategories = [];

    public $selectedKategori = [];

    public $title_id;

    public $title_en;

    public $sub_judul_id;

    public $sub_judul_en;

    public $excerpt_id;

    public $excerpt_en;

    public $start_date;

    public $end_date;

    public $image;

    public $old_image;

    public $userId;

    public $status;

    protected $listeners = ['updateCategoryContent' => 'updateCategoryContent'];

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

    public function mount($fellowshipId = null)
    {
        $this->fellowshipId = $fellowshipId;

        // $this->categories = KategoriTranslation::select('id', 'kategori_name')->get();
        // $this->kategori = $fellowshipId
        //     ? KategoriFellowship::findOrFail($fellowshipId)
        //     : new KategoriFellowship();

        $this->categoriesData = Kategori::with('translations')->get()->map(function ($cat) {
            $idTrans = $cat->translations->firstWhere('locale', 'id');
            $enTrans = $cat->translations->firstWhere('locale', 'en');

            return [
                'id' => $cat->id,
                'id_name' => $idTrans->kategori_name ?? '-',
                'en_name' => $enTrans->kategori_name ?? '-',
            ];
        })->toArray();

        // cek di log
        \Log::info('Categories data:', $this->categoriesData);

        if ($fellowshipId) {
            $this->fellowship = Fellowship::with('kategoris', 'translations')->findOrFail($fellowshipId);

            $idTranslation = $this->fellowship->translations->firstWhere('locale', 'id');
            $enTranslation = $this->fellowship->translations->firstWhere('locale', 'en');

            $this->fill([
                'title_id' => $idTranslation->title ?? '-',
                'title_en' => $enTranslation->title ?? '-',
                'sub_judul_id' => $idTranslation->sub_judul ?? '-',
                'sub_judul_en' => $enTranslation->sub_judul ?? '-',
                'slug' => $this->fellowship->slug,
                'image' => $this->fellowship->image,
                'start_date' => $this->fellowship->start_date,
                'end_date' => $this->fellowship->end_date,
                'status' => $this->fellowship->status,
                'excerpt_id' => $idTranslation->excerpt ?? '',
                'excerpt_en' => $enTranslation->excerpt ?? '',
            ]);
            $this->old_image = $this->fellowship->image;
            $this->image = null;

            foreach ($this->fellowship->kategoris as $kategori) {
                $this->selectedCategories[] = $kategori->id;
                $this->selectedKategori[$kategori->id] = [
                    'id' => $kategori->pivot->content_id ?? '',
                    'en' => $kategori->pivot->content_en ?? '',
                    'status' => $kategori->pivot->status ?? '',
                ];
            }

        }
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($page) {
    //         if (empty($page->slug)) {
    //             $page->slug = Str::slug(request()->input('title_id'));
    //         }
    //     });

    //     static::updating(function ($page) {
    //         // hanya ubah slug kalau title_id berubah
    //         if (request()->filled('title_id') && $page->isDirty('title_id')) {
    //             $page->slug = Str::slug(request()->input('title_id'));
    //         }
    //     });
    // }

    public function save()
    {
        $this->validate([
            'title_id' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'excerpt_id' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5048',
            'status' => 'required|in:draft,active,inactive',
        ]);

        // 🧠 Cek apakah ini edit atau create
        if ($this->fellowship && $this->fellowship->exists) {
            // UPDATE
            $fellowship = $this->fellowship;
            $fellowship->update([
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
            ]);
        } else {
            // CREATE
            $fellowship = Fellowship::create([
                'slug' => Str::slug($this->title_id),
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => $this->status,
                'user_id' => auth()->id(),
            ]);
        }

        // ✅ Upload & buat meta image
        if ($this->image) {

            // Hapus gambar lama
            if ($this->old_image && Storage::disk('public')->exists($this->old_image)) {
                Storage::disk('public')->delete($this->old_image);
            }

            // Simpan gambar baru
            $filename = Str::slug($this->title_id).'-'.time().'.'.$this->image->getClientOriginalExtension();
            $path = $this->image->storeAs('fellowship', $filename, 'public');

            // Resize untuk meta
            $metaDir = storage_path('app/public/fellowship/meta');
            if (! file_exists($metaDir)) {
                mkdir($metaDir, 0755, true);
            }

            $fullPath = storage_path('app/public/'.$path);
            $metaPath = null;
            if (file_exists($fullPath)) {
                $manager = ImageManager::gd()
                    ->read($fullPath)
                    ->resize(1200, 630);
                $manager->save($metaDir.'/'.$filename);

                $metaPath = 'fellowship/meta/'.$filename;
            }

            $fellowship->update([
                'image' => $path,
                'meta_image' => $metaPath,
            ]);
            $this->old_image = $path;

        }

        foreach (['id', 'en'] as $locale) {
            FellowshipTranslation::updateOrCreate(
                [
                    'fellowship_id' => $fellowship->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $this->{'title_'.$locale},
                    'sub_judul' => $this->{'sub_judul_'.$locale},
                    'excerpt' => $this->{'excerpt_'.$locale},
                ]
            );
        }

        if (! empty($this->selectedCategories)) {
            foreach ($this->selectedCategories as $catId) {
                $data = $this->selectedKategori[$catId] ?? [];

                $fellowship->kategoris()->syncWithoutDetaching([
                    $catId => [
                        'status' => $data['status'],
                        'content_id' => $data['id'] ?? null,
                        'content_en' => $data['en'] ?? null,
                    ],
                ]);
            }
        }
        session()->flash('success', 'Fellowship berhasil ditambahkan!');

        return redirect()->route('fellowship.index');
    }

    public function render()
    {
        return view('livewire.fellowship.fellowship-form')
            ->layout('layouts.admin');
    }
}
