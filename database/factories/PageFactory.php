<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'type' => 'default',
            'page_type' => 'expose',
            'status' => 'draft',
            'source_type' => 'manual',
            'user_id' => User::factory(),
        ];
    }
}
