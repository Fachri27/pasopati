<?php

namespace Database\Factories;

use App\Models\Petition;
use App\Models\PetitionSignature;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetitionSignatureFactory extends Factory
{
    protected $model = PetitionSignature::class;

    public function definition(): array
    {
        return [
            'petition_id' => Petition::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'city' => $this->faker->city(),
            'is_verified' => true,
            'ip_address' => $this->faker->ipv4(),
            'created_at' => now()->subHours(rand(1, 720)),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attrs) => [
            'is_verified' => false,
            'verification_token' => $this->faker->sha256(),
        ]);
    }
}
