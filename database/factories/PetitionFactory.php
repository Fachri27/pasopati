<?php

namespace Database\Factories;

use App\Models\Petition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetitionFactory extends Factory
{
    protected $model = Petition::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(3),
            'target_name' => $this->faker->company(),
            'demands' => [$this->faker->sentence(), $this->faker->sentence()],
            'goal_count' => $this->faker->numberBetween(100, 10000),
            'status' => 'active',
            'published_at' => now(),
            'user_id' => User::factory(),
        ];
    }
}
