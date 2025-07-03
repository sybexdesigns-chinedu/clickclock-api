<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interest>
 */
class InterestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Technology', 'Art', 'Travel', 'Food', 'Movies', 'Reading', 'Gaming', 'Fitness', 'Photography',
                'Dancing', 'Coding', 'Fashion', 'Writing', 'Science', 'History', 'Theater', 'Finance',
                'Automobiles', 'Politics', 'DIY', 'Spirituality', 'Anime', 'Pets', 'Social Activism',
                'Podcasts', 'Hiking', 'Astronomy', 'Comedy'
            ]),
            'description' => $this->faker->sentence(), // Generates a single sentence instead of an array
            'icon' => $this->faker->randomElement([
                'fas fa-camera', 'fas fa-gamepad', 'fas fa-book', 'fas fa-code', 'fas fa-music',
                'fas fa-palette', 'fas fa-hiking', 'fas fa-globe', 'fas fa-laptop', 'fas fa-dumbbell'
            ])
        ];
    }
}
