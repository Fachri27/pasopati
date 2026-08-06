<?php

namespace Database\Factories;

use App\Models\Petition;
use App\Models\PetitionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetitionTranslationFactory extends Factory
{
    protected $model = PetitionTranslation::class;

    public function definition(): array
    {
        return [
            'petition_id' => Petition::factory(),
            'locale' => 'id',
            'title' => $this->faker->sentence(5),
            'description' => $this->faker->paragraphs(3, true),
        ];
    }
}
