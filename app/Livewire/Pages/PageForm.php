<?php

namespace App\Livewire\Pages;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Services\ImportToHtmlService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithFileUploads;

class PageForm extends Component
{
    use WithFileUploads;

    public $page;
    public $title_id;
    public $title_en;
    public $slug;
    public $page_type = 'expose';
    public $type = 'default';
    public $status = 'draft';
    public $published_at;
    public $featured_image;
    public $old_featured_image;
    public $content_id = '';
    public $content_en = '';
    public $excerpt_id;
    public $excerpt_en;
    public $expose_type;
    public $source_type = 'manual';
    public $file_import_id;
    public $file_import_en;

    // 🔥 WAJIB agar Livewire tidak membersihkan HTML
    protected $rules = [
        'title_id' => 'required|string|max:255',
        'title_en' => 'required|string|max:255',
        'page_type' => 'required|in:expose,ngopini',
        'type' => 'required|in:parallax,default',
        'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,PNG|max:5048',
        'published_at' => 'nullable|date',
        'status' => 'required|in:draft,active,inactive',
        'file_import_id' => 'nullable|file|mimes:docx|max:10240',
        'file_import_en' => 'nullable|file|mimes:docx|max:10240',

        // 🔥 konten HTML jangan difilter
        'content_id' => 'nullable|string',
        'content_en' => 'nullable|string',
    ];

    protected $casts = [
        'content_id' => 'string',
        'content_en' => 'string',
    ];

    // 🔥 Matikan sanitasi internal Livewire (iframe tidak terhapus)
    public static $sanitizeHtml = false;

    public function mount($pageId = null)
    {
        if ($pageId) {
            $this->page = Page::with('translations')->findOrFail($pageId);

            $idTranslation = $this->page->translations->firstWhere('locale', 'id');
            $enTranslation = $this->page->translations->firstWhere('locale', 'en');

            $this->fill([
                'title_id' => $idTranslation->title ?? '',
                'title_en' => $enTranslation->title ?? '',
                'slug' => $this->page->slug,
                'page_type' => $this->page->page_type,
                'type' => $this->page->type,
                'status' => $this->page->status,
                'featured_image' => $this->page->featured_image,
                'source_type' => $this->page->source_type,
                'expose_type' => $this->page->expose_type,
                'file_import_id' => $this->page->source_file,
                'file_import_en' => $this->page->source_file,
                'published_at' => $this->page->published_at,
                'excerpt_id' => $idTranslation->excerpt ?? '',
                'excerpt_en' => $enTranslation->excerpt ?? '',
                'content_id' => $idTranslation->content ?? '',
                'content_en' => $enTranslation->content ?? '',
            ]);

            $this->old_featured_image = $this->page->featured_image;
            $this->featured_image = null;
        }
    }

    public function updateTitleId($value)
    {
        if (!$this->slug) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSourceType()
    {
        $this->dispatch('$refresh');
    }

    public function save()
    {
        $this->validate();

        $page = $this->page ?? new Page;

        $data = [
            'slug' => Str::slug($this->title_id),
            'type' => $this->type,
            'page_type' => $this->page_type,
            'expose_type' => $this->expose_type,
            'published_at' => $this->published_at,
            'status' => $this->status,
            'user_id' => auth()->id(),
            'source_type' => $this->source_type,
        ];

        if ($this->file_import_id) {
            $path = $this->file_import_id->store('pages/import', 'public');
            $parser = app(ImportToHtmlService::class);
            $this->content_id = $parser->parseToHtml(storage_path('app/public/'.$path));
            $data['source_file'] = $path;
        }

        if ($this->file_import_en) {
            $path = $this->file_import_en->store('pages/import', 'public');
            $parser = app(ImportToHtmlService::class);
            $this->content_en = $parser->parseToHtml(storage_path('app/public/'.$path));
            $data['source_file'] = $path;
        }

        // ✅ Upload & buat meta image
        if ($this->featured_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {

            // Hapus gambar lama
            if ($this->old_featured_image && Storage::disk('public')->exists($this->old_featured_image)) {
                Storage::disk('public')->delete($this->old_featured_image);
            }

            // Simpan gambar baru
            $filename = Str::slug($this->title_id).'-'.time().'.'.$this->featured_image->getClientOriginalExtension();
            $path = $this->featured_image->storeAs('pages', $filename, 'public');

            // Resize untuk meta
            $metaDir = storage_path('app/public/pages/meta');
            if (! file_exists($metaDir)) {
                mkdir($metaDir, 0755, true);
            }

            $fullPath = storage_path('app/public/'.$path);
            if (file_exists($fullPath)) {
                $manager = ImageManager::gd()
                    ->read($fullPath)
                    ->resize(1200, 630);
                $manager->save($metaDir.'/'.$filename);
            }

            $data['featured_image'] = $path;

        } else {
            // Tidak upload baru → tetap pakai gambar lama
            $data['featured_image'] = $this->old_featured_image;
        }


        // Simpan page
        $page->fill($data)->save();
        $page->refresh();

        // 🔥 simpan iframe apa adanya tanpa pemotongan HTML
        foreach (['id', 'en'] as $locale) {
            PageTranslation::updateOrCreate(
                ['page_id' => $page->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id' ? $this->title_id : $this->title_en,
                    'excerpt' => $locale === 'id' ? $this->excerpt_id : $this->excerpt_en,
                    'content' => $locale === 'id' ? $this->content_id : $this->content_en,
                ]
            );
        }

        session()->flash('success', 'Page berhasil disimpan.');
        return redirect()->route('pages.index');
    }

    public function render()
    {
        return view('livewire.pages.page-form')
            ->layout('layouts.admin');
    }
}

