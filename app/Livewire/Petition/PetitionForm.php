<?php

namespace App\Livewire\Petition;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class PetitionForm extends Component
{
    use WithFileUploads;

    public $petition;

    public $title_id;

    public $title_en;

    public $slug;

    public $target_name;

    public $demands = [];

    public $demandInput = '';

    public $cover_image;

    public $old_cover_image;

    public $goal_count;

    public $status = 'draft';

    public $published_at;

    public $description_id = '';

    public $description_en = '';

    protected $rules = [
        'title_id' => 'required|string|max:255',
        'title_en' => 'required|string|max:255',
        'target_name' => 'required|string|max:255',
        'demands' => 'nullable|array',
        'demands.*' => 'string|max:500',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,PNG|max:5048',
        'goal_count' => 'required|integer|min:1',
        'status' => 'required|in:draft,active,closed,succeeded',
        'published_at' => 'nullable|date',
        'description_id' => 'nullable|string',
        'description_en' => 'nullable|string',
    ];

    public static $sanitizeHtml = false;

    public function mount($petitionId = null)
    {
        if ($petitionId) {
            $this->petition = Petition::with('translations')->findOrFail($petitionId);

            $idTranslation = $this->petition->translations->firstWhere('locale', 'id');
            $enTranslation = $this->petition->translations->firstWhere('locale', 'en');

            $this->fill([
                'title_id' => $idTranslation->title ?? '',
                'title_en' => $enTranslation->title ?? '',
                'slug' => $this->petition->slug,
                'target_name' => $this->petition->target_name,
                'demands' => $this->petition->demands ?? [],
                'goal_count' => $this->petition->goal_count,
                'status' => $this->petition->status,
                'published_at' => $this->petition->published_at?->format('Y-m-d'),
                'description_id' => $idTranslation->description ?? '',
                'description_en' => $enTranslation->description ?? '',
            ]);

            $this->old_cover_image = $this->petition->cover_image;
            $this->cover_image = null;
        }
    }

    public function addDemand()
    {
        $this->validate(['demandInput' => 'required|string|max:500']);

        $this->demands[] = $this->demandInput;
        $this->demandInput = '';
    }

    public function removeDemand($index)
    {
        unset($this->demands[$index]);
        $this->demands = array_values($this->demands);
    }

    public function save()
    {
        $this->validate();

        $petition = $this->petition ?? new Petition;

        $data = [
            'slug' => Str::slug($this->title_id),
            'target_name' => $this->target_name,
            'demands' => $this->demands,
            'goal_count' => $this->goal_count,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'user_id' => auth()->id(),
        ];

        if ($this->cover_image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            if ($this->old_cover_image && Storage::disk('public')->exists($this->old_cover_image)) {
                Storage::disk('public')->delete($this->old_cover_image);
            }

            $filename = Str::slug($this->title_id).'-'.time().'.'.$this->cover_image->getClientOriginalExtension();
            $data['cover_image'] = $this->cover_image->storeAs('petitions', $filename, 'public');
        } else {
            $data['cover_image'] = $this->old_cover_image;
        }

        $petition->fill($data)->save();
        $petition->refresh();

        foreach (['id', 'en'] as $locale) {
            PetitionTranslation::updateOrCreate(
                ['petition_id' => $petition->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id' ? $this->title_id : $this->title_en,
                    'description' => $locale === 'id' ? $this->description_id : $this->description_en,
                ]
            );
        }

        session()->flash('success', 'Petisi berhasil disimpan.');

        return redirect()->route('petition.admin.index');
    }

    public function render()
    {
        return view('livewire.petition.petition-form')
            ->layout('layouts.admin');
    }
}
