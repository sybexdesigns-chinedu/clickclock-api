<?php

namespace Database\Factories;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->userName(),
            'name' => $this->faker->firstName,
            'dob' => $this->faker->date('Y-m-d', '2005-01-01'), // Random DOB before 2005
            'gender' => Arr::random(['Male', 'Female']),
            'phone' => $this->faker->phoneNumber,
            'image' => $this->faker->imageUrl(200, 200, 'people'), // Fake image URL
            'city' => $this->faker->city,
            'country' => $this->faker->randomElement(['UK', 'USA', 'Nigeria', 'India', 'Russia', 'Brazil', 'Canada', 'Germany', 'France']),
        ];
    }
}
