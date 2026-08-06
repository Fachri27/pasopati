<?php

namespace App\Livewire\Deforestory;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use App\Jobs\DeforestoryNotificationJob;
use App\Jobs\DeforestoryWebhookJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\Component;

/**
 * Form satu laporan Deforestory (mengikuti pola PageForm / CMS artikel).
 *
 * Tiap laporan = entitas sendiri: judul + slug (diturunkan dari judul ID) +
 * excerpt + gambar + isi (TinyMCE), per-locale ID/EN. Laporan ditautkan ke
 * sebuah kasus (DeforestoryCase) lewat case_id. Halaman arsip /{caseSlug}
 * menampilkan daftar laporan; detail di /{caseSlug}/{laporanSlug}.
 */
class DeforestoryLaporanForm extends Component
{
    use WithFileUploads;

    public $laporan;
    public $laporanId;
    public $caseId;
    public $caseSlug;

    public $title_id;
    public $title_en;
    public $slug;
    public $excerpt_id;
    public $excerpt_en;
    public $laporan_content_id = '';
    public $laporan_content_en = '';
    public $image;
    public $old_image;
    public $status = 'draft';
    public $sort = 0;
    public $published_at;

    public static $sanitizeHtml = false;

    public function mount($laporanId = null, $caseSlug = null)
    {
        $this->laporanId = $laporanId;
        $this->caseSlug = $caseSlug;

        if ($laporanId) {
            $this->laporan = DeforestoryLaporan::with(['translations', 'case'])->findOrFail($laporanId);

            $idTrans = $this->laporan->translations->firstWhere('locale', 'id');
            $enTrans = $this->laporan->translations->firstWhere('locale', 'en');

            $this->fill([
                'caseId' => $this->laporan->case_id,
                'caseSlug' => $this->laporan->case->slug,
                'title_id' => $idTrans->title ?? '',
                'title_en' => $enTrans->title ?? '',
                'slug' => $this->laporan->slug,
                'excerpt_id' => $idTrans->excerpt ?? '',
                'excerpt_en' => $enTrans->excerpt ?? '',
                'laporan_content_id' => $idTrans->content ?? '',
                'laporan_content_en' => $enTrans->content ?? '',
                'status' => $this->laporan->status,
                'sort' => $this->laporan->sort ?? 0,
                'published_at' => $this->laporan->published_at?->format('Y-m-d'),
                'old_image' => $this->laporan->image,
            ]);

            $this->image = null;
        } else {
            // Buat laporan baru: kasus harus sudah ada (di-auto-create oleh
            // halaman daftar laporan). Ambil caseSlug dari URL.
            $case = DeforestoryCase::where('slug', $caseSlug)->firstOrFail();
            $this->caseId = $case->id;
            $this->caseSlug = $case->slug;
            $this->published_at = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $isUpdate = $this->laporan && $this->laporan->exists;
        // Status laporan SEBELUM disimpan — dipakai untuk mendeteksi "publish"
        // (transisi dari non-aktif → aktif), bukan edit biasa.
        $wasActive = $isUpdate && $this->laporan->status === 'active';

        $rules = [
            'title_id' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('deforestory_laporans', 'slug')
                ->where('case_id', $this->caseId)
                ->ignore($this->laporanId)],
            'status' => 'required|in:draft,active,inactive',
            'sort' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image',
            'excerpt_id' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'laporan_content_id' => 'nullable|string',
            'laporan_content_en' => 'nullable|string',
        ];

        $this->validate($rules);

        // Slug diturunkan dari judul ID bila kosong.
        $slug = $this->slug ?: \Illuminate\Support\Str::slug($this->title_id);

        // Upload gambar laporan.
        if ($this->image instanceof TemporaryUploadedFile) {
            if ($this->old_image && Storage::disk('public')->exists($this->old_image)) {
                Storage::disk('public')->delete($this->old_image);
            }
            $filename = $slug . '-' . time() . '.' . $this->image->getClientOriginalExtension();
            $imagePath = $this->image->storeAs('deforestory/laporans', $filename, 'public');
        } else {
            $imagePath = $this->old_image;
        }

        $data = [
            'case_id' => $this->caseId,
            'slug' => $slug,
            'image' => $imagePath,
            'sort' => (int) $this->sort,
            'status' => $this->status,
            'published_at' => $this->published_at ?: null,
        ];

        if ($isUpdate) {
            $this->laporan->update($data);
            $laporan = $this->laporan;
        } else {
            $laporan = DeforestoryLaporan::create($data);
        }

        foreach (['id', 'en'] as $locale) {
            DeforestoryLaporanTranslation::updateOrCreate(
                ['laporan_id' => $laporan->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id' ? $this->title_id : $this->title_en,
                    'excerpt' => $locale === 'id' ? $this->excerpt_id : $this->excerpt_en,
                    'content' => $locale === 'id' ? $this->laporan_content_id : $this->laporan_content_en,
                ]
            );
        }

        // Antrekan notifikasi email HANYA saat publish — yaitu laporan menjadi
        // aktif untuk pertama kali (dibuat aktif, atau diubah dari draft/inactive
        // ke active). Edit laporan yang sudah aktif TIDAK memicu notifikasi.
        $justPublished = $this->status === 'active' && ! $wasActive;
        if ($justPublished) {
            $case = DeforestoryCase::find($this->caseId);
            if ($case && $case->status === 'active') {
                // 1) Notifikasi email ke subscriber CMS ini.
                DeforestoryNotificationJob::dispatch($case, 'created');

                // 2) Webhook keluar ke web lain supaya langsung update tanpa polling.
                DeforestoryWebhookJob::dispatch($case, $laporan, 'created');
            }
        }

        session()->flash('success', 'Laporan berhasil disimpan.');

        return redirect()->route('deforestory.laporan.index', ['caseSlug' => $this->caseSlug]);
    }

    public function render()
    {
        return view('livewire.deforestory.deforestory-laporan-form')
            ->layout('layouts.admin');
    }
}