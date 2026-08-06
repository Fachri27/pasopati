<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\{Page, PageTranslation};
use App\Services\ImportToHtmlService;
use Intervention\Image\ImageManager;
use Livewire\{Component, WithFileUploads};
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

    public $expose_type = [];

    public $source_type = 'manual';

    public $file_import_id;

    public $file_import_en;

    public $content_blocks_id = [];

    public $content_blocks_en = [];

    public $blocksVersion = 0;

    // 🔥 WAJIB agar Livewire tidak membersihkan HTML
    protected $rules = [
        'title_id' => 'required|string|max:255',
        'title_en' => 'required|string|max:255',
        'page_type' => 'required|in:expose,ngopini',
        'type' => 'required|in:parallax,default',
        'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,PNG|max:5048',
        'published_at' => 'nullable|date',
        'status' => 'required|in:draft,active,inactive',
        'file_import_id' => 'nullable|file|mimes:docx,doc,pdf|max:10240',
        'file_import_en' => 'nullable|file|mimes:docx,doc,pdf|max:10240',
        'expose_type' => 'nullable|array',
        'expose_type.*' => 'string|in:deforestasi,kebakaran,pulp,mining',

        // 🔥 konten HTML jangan difilter
        // SARAN: Saat migrasi ke content_blocks, hapus validasi manual ini
        // dan ganti dengan validasi per-block.
        'content_id' => 'nullable|string',
        'content_en' => 'nullable|string',
    ];

    protected $casts = [
        'content_id' => 'string',
        'content_en' => 'string',
    ];

    // SARAN: Dengan content_blocks (data terstruktur JSON, bukan HTML mentah),
    // $sanitizeHtml tidak perlu diset false karena kita tidak lagi menyimpan HTML
    // sembarangan — setiap block dirender oleh partial view sendiri di frontend.
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
                'expose_type' =>  $this->page->expose_type ?? [],
                'file_import_id' => $this->page->source_file,
                'file_import_en' => $this->page->source_file,
                'published_at' => $this->page->published_at,
                'excerpt_id' => $idTranslation->excerpt ?? '',
                'excerpt_en' => $enTranslation->excerpt ?? '',
                'content_id' => $idTranslation->content ?? '',
                'content_en' => $enTranslation->content ?? '',
                'content_blocks_id' => $idTranslation->content_blocks ?? [],
                'content_blocks_en' => $enTranslation->content_blocks ?? [],
            ]);

            $this->old_featured_image = $this->page->featured_image;
            $this->featured_image = null;
        }
    }

    public function addBlock($locale, $type)
    {
        $prop = 'content_blocks_' . $locale;
        $defaults = [
            'paragraph' => ['html' => '', 'mt' => null, 'mb' => null],
            'image' => ['src' => '', 'caption' => '', 'alignment' => 'center', 'mt' => null, 'mb' => null],
            'event_info_box' => ['format' => '', 'date' => '', 'time' => '', 'venue' => '', 'registration_links' => [], 'notes' => '', 'mt' => null, 'mb' => null],
            'agenda_day' => ['day' => '', 'sessions' => [], 'mt' => null, 'mb' => null],
            'speaker_bio' => ['photo' => '', 'name' => '', 'title' => '', 'bio' => '', 'mt' => null, 'mb' => null],
            'quote' => ['text' => '', 'source' => '', 'mt' => null, 'mb' => null],
        ];
        $blocks = $this->$prop;
        $blocks[] = ['type' => $type, 'data' => $defaults[$type] ?? []];
        $this->$prop = $blocks;
        $this->blocksVersion++;
    }

    public function removeBlock($locale, $index)
    {
        $prop = 'content_blocks_' . $locale;
        $blocks = $this->$prop;
        unset($blocks[$index]);
        $this->$prop = array_values($blocks);
        $this->blocksVersion++;
    }

    public function moveBlockUp($locale, $index)
    {
        if ($index === 0) return;
        $prop = 'content_blocks_' . $locale;
        $blocks = $this->$prop;
        $tmp = $blocks[$index - 1];
        $blocks[$index - 1] = $blocks[$index];
        $blocks[$index] = $tmp;
        $this->$prop = array_values($blocks);
        $this->blocksVersion++;
    }

    public function moveBlockDown($locale, $index)
    {
        $prop = 'content_blocks_' . $locale;
        $blocks = $this->$prop;
        if ($index >= count($blocks) - 1) return;
        $tmp = $blocks[$index + 1];
        $blocks[$index + 1] = $blocks[$index];
        $blocks[$index] = $tmp;
        $this->$prop = array_values($blocks);
        $this->blocksVersion++;
    }

    public function addRegLink($locale, $blockIndex)
    {
        $prop = 'content_blocks_' . $locale;
        $blocks = $this->$prop;
        $links = $blocks[$blockIndex]['data']['registration_links'] ?? [];
        $links[] = ['day' => '', 'url' => ''];
        $blocks[$blockIndex]['data']['registration_links'] = $links;
        $this->$prop = $blocks;
    }

    public function removeRegLink($locale, $blockIndex, $linkIndex)
    {
        $prop = 'content_blocks_' . $locale;
        $blocks = $this->$prop;
        unset($blocks[$blockIndex]['data']['registration_links'][$linkIndex]);
        $blocks[$blockIndex]['data']['registration_links'] = array_values($blocks[$blockIndex]['data']['registration_links'] ?? []);
        $this->$prop = $blocks;
    }

    public function addSession($locale, $blockIndex)
    {
        $prop = 'content_blocks_' . $locale;
        $blocks = $this->$prop;
        $sessions = $blocks[$blockIndex]['data']['sessions'] ?? [];
        $sessions[] = ['time' => '', 'title' => '', 'description' => '', 'moderator' => '', 'commentator' => '', 'speakers' => ''];
        $blocks[$blockIndex]['data']['sessions'] = $sessions;
        $this->$prop = $blocks;
    }

    public function removeSession($locale, $blockIndex, $sessionIndex)
    {
        $prop = 'content_blocks_' . $locale;
        $blocks = $this->$prop;
        unset($blocks[$blockIndex]['data']['sessions'][$sessionIndex]);
        $blocks[$blockIndex]['data']['sessions'] = array_values($blocks[$blockIndex]['data']['sessions'] ?? []);
        $this->$prop = $blocks;
    }

    public function updateTitleId($value)
    {
        if (! $this->slug) {
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
            'published_at' => $this->published_at,
            'status' => $this->status,
            'user_id' => auth()->id(),
            'source_type' => $this->source_type,
            'expose_type' => $this->page_type === 'expose'
                ? $this->expose_type
                : [],
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
                    ->resize(300, 150);
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

        foreach (['id', 'en'] as $locale) {
            $contentBlocksProp = 'content_blocks_' . $locale;
            $blocks = $this->$contentBlocksProp;
            if (is_array($blocks)) {
                foreach ($blocks as $i => $block) {
                    foreach (['src', 'photo'] as $field) {
                        if (isset($block['data'][$field]) && $block['data'][$field] instanceof TemporaryUploadedFile) {
                            $path = $block['data'][$field]->store('pages/blocks', 'public');
                            $blocks[$i]['data'][$field] = $path;
                        }
                    }
                }
                $this->$contentBlocksProp = $blocks;
            }
            PageTranslation::updateOrCreate(
                ['page_id' => $page->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id' ? $this->title_id : $this->title_en,
                    'excerpt' => $locale === 'id' ? $this->excerpt_id : $this->excerpt_en,
                    'content' => $locale === 'id' ? $this->content_id : $this->content_en,
                    'content_blocks' => !empty($this->$contentBlocksProp) ? $this->$contentBlocksProp : null,
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
