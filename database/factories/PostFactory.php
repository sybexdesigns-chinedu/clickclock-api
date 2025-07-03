<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $images = $this->faker->optional()->randomElements([
            $this->faker->imageUrl(),
            $this->faker->imageUrl(),
            $this->faker->imageUrl(),
            $this->faker->imageUrl(),
        ], rand(1, 3));

        return [
            'privacy' => $this->faker->randomElement(['public', 'private', 'friends']), // String
            'allow_comments' => $this->faker->boolean(), // Boolean
            'allow_like_counts' => $this->faker->boolean(), // Boolean
            'body' => $this->faker->optional()->sentence(), // Nullable string
            'hashtags' => implode(', ', $this->faker->optional(1)->randomElements(['#fun', '#tech', '#coding', '#travel', '#tgif', '#coding', '#tech', '#developer', '#programming', '#javascript', '#php', '#laravel', '#webdev', '#backend', '#frontend', '#fullstack', '#software', '#cloud', '#ai', '#machinelearning','#datascience', '#opensource', '#startup', '#innovation', '#codinglife'], rand(1, 5), true)), // Nullable array
            'meta_location' => $this->faker->randomElement(['England', 'USA', 'Nigeria', 'India', 'Russia', 'Brazil', 'Germany', 'France']),
            'file_url' => $images = $images ? implode(', ', $images) : null,
            'location' => $this->faker->optional()->city(), // Nullable string
            'user_id' => rand(1, 31), // Integer
        ];
    }
}
