<?php

namespace App\Livewire\Deforestory;

use App\Models\DeforestoryCase;
use App\Models\DeforestoryLaporan;
use App\Models\DeforestoryLaporanTranslation;
use App\Jobs\DeforestoryNotificationJob;
use App\Jobs\DeforestorySyncJob;
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
    public $image_id;
    public $image_en;
    public $old_image_id;
    public $old_image_en;
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
                'old_image_id' => $idTrans->image ?? null,
                'old_image_en' => $enTrans->image ?? null,
            ]);

            $this->image_id = null;
            $this->image_en = null;
        } else {
            // Buat laporan baru: kasus harus sudah ada (di-auto-create oleh
            // halaman daftar laporan). Ambil caseSlug dari URL.
            $case = DeforestoryCase::where('slug', $caseSlug)->firstOrFail();
            $this->caseId = $case->id;
            $this->caseSlug = $case->slug;
            $this->published_at = now()->format('Y-m-d');
        }
    }

    /**
     * Upload gambar laporan untuk satu locale. Kalau gak ada upload baru, pakai
     * path lama (old_image_<locale>) supaya gambar gak hilang saat edit field lain.
     */
    private function storeLaporanImage(string $locale, string $slug): ?string
    {
        $upload = $locale === 'id' ? $this->image_id : $this->image_en;
        $old = $locale === 'id' ? $this->old_image_id : $this->old_image_en;

        if ($upload instanceof TemporaryUploadedFile) {
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $filename = $slug . '-' . $locale . '-' . time() . '.' . $upload->getClientOriginalExtension();

            return $upload->storeAs('deforestory/laporans', $filename, 'public');
        }

        return $old;
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
            'image_id' => 'nullable|image',
            'image_en' => 'nullable|image',
            'excerpt_id' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'laporan_content_id' => 'nullable|string',
            'laporan_content_en' => 'nullable|string',
        ];

        // Slug diturunkan dari judul ID bila kosong. Input readonly diisi Alpine
        // JS yang kadang gagal sync balik ke Livewire → server yang hitung, bukan
        // mengandalkan nilai dari browser.
        if (! $this->slug) {
            $this->slug = \Illuminate\Support\Str::slug($this->title_id);
        }

        $this->validate($rules);

        $slug = $this->slug;

        // Upload gambar laporan per-locale (id + en). Image legacy (laporan.image)
        // gak lagi ditulis form; cuma dipakai sebagai fallback baca data lama.
        $imagePathId = $this->storeLaporanImage('id', $slug);
        $imagePathEn = $this->storeLaporanImage('en', $slug);

        $data = [
            'case_id' => $this->caseId,
            'slug' => $slug,
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
                    'image' => $locale === 'id' ? $imagePathId : $imagePathEn,
                ]
            );
        }

        // Antrekan job keluar HANYA saat publish (draft/inactive → active):
        // - notifikasi email ke subscriber CMS ini,
        // - webhook keluar ke web lain (sindikasi lama),
        // - sync keluar ke simontini (publish-only, body 7-field).
        // Unpublish (active → draft/inactive) gak di-sync ke simontini —
        // simontini cuma perlu tau laporan baru naik. Edit laporan aktif
        // TIDAK memicu job apa pun.
        $justPublished = $this->status === 'active' && ! $wasActive;

        if ($justPublished) {
            $case = DeforestoryCase::find($this->caseId);

            if ($case && $case->status === 'active') {
                // 1) Notifikasi email ke subscriber CMS ini.
                DeforestoryNotificationJob::dispatch($case, 'created');

                // 2) Webhook keluar ke web lain supaya langsung update tanpa polling.
                DeforestoryWebhookJob::dispatch($case, $laporan, 'created');

                // 3) Sync keluar ke simontini (publish-only).
                DeforestorySyncJob::dispatch($case, $laporan);
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