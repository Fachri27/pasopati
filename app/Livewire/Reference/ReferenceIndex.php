<?php

namespace App\Livewire\Reference;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Pustaka berkas Reference, sekaligus pemilih gambar untuk editor TinyMCE.
 *
 * Satu komponen dipakai dua peran. Sebagai halaman CMS biasa ia mengelola
 * unggahan; sebagai pemilih (?picker=1) ia dibuka di dalam modal editor dan
 * setiap kartu mendapat tombol yang mengirim gambar terpilih kembali ke
 * editor lewat postMessage. Peran pemilih hanya menampilkan berkas gambar —
 * PDF dan dokumen tetap tersimpan tapi tidak bisa disisipkan sebagai gambar.
 */
class ReferenceIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $image;

    public string $title = '';

    public string $alt_text = '';

    public bool $showForm = false;

    public bool $picker = false;

    /** Pilih banyak gambar sekaligus — dipakai galeri GLightbox dan Before/After. */
    public bool $multiple = false;

    public int $selectionLimit = 0;

    /** '' | 'lightbox' | 'before-after' — menentukan teks bantuan dan jumlah pilihan. */
    public string $pickerPurpose = '';

    /** Nama properti Livewire editor pemanggil, dipakai mencocokkan postMessage. */
    public string $editorKey = '';

    /** Dibuka di dalam modal editor: header dan sidebar CMS disembunyikan. */
    public bool $modal = false;

    public function mount(): void
    {
        $request = request();

        $this->picker = $request->boolean('picker');
        $this->multiple = $request->boolean('multiple');
        $this->selectionLimit = max(0, (int) $request->integer('limit'));
        $this->pickerPurpose = (string) $request->string('purpose');
        $this->editorKey = (string) $request->string('editor');
        $this->modal = $request->boolean('modal');
    }

    protected function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm,pdf,doc,docx',
                'max:51200',
            ],
            'title' => ['required', 'string', 'max:255'],
            'alt_text' => ['required', 'string', 'max:255'],
        ];
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate();
        $mimeType = $this->image->getMimeType() ?: 'application/octet-stream';
        $isImage = str_starts_with($mimeType, 'image/');
        // Hanya gambar yang boleh publik: ia disisipkan ke dalam konten dan
        // harus bisa dimuat pembaca tanpa login. Sisanya disimpan di disk
        // privat dan hanya keluar lewat route download/preview.
        $disk = $isImage ? 'public' : 'local';
        $directory = $isImage ? 'references' : 'reference-files';
        $path = $this->image->store($directory, $disk);

        DB::table('reference_images')->insert([
            'title' => $validated['title'],
            'alt_text' => $validated['alt_text'],
            'image_path' => $path,
            'disk' => $disk,
            'original_name' => $this->image->getClientOriginalName(),
            'mime_type' => $mimeType,
            'file_size' => $this->image->getSize(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->reset(['image', 'title', 'alt_text']);
        $this->showForm = false;
        $this->resetPage();

        session()->flash('success', 'File reference berhasil diunggah.');
    }

    public function delete(int $id): void
    {
        $item = DB::table('reference_images')->find($id);

        if ($item === null) {
            return;
        }

        DB::table('reference_images')->where('id', $id)->delete();
        Storage::disk($item->disk ?: 'public')->delete($item->image_path);

        $this->resetPage();
        session()->flash('success', 'File reference berhasil dihapus.');
    }

    public function render()
    {
        $items = DB::table('reference_images')
            ->when($this->picker, function ($query) {
                $query->where(function ($images) {
                    // mime_type null = baris lama sebelum kolom metadata ada;
                    // waktu itu hanya gambar yang bisa diunggah.
                    $images
                        ->where('mime_type', 'like', 'image/%')
                        ->orWhereNull('mime_type');
                });
            })
            ->orderByDesc('id')
            ->paginate(12);

        return view('livewire.reference.reference-index', compact('items'))
            ->layout($this->modal ? 'layouts.picker' : 'layouts.admin');
    }
}
