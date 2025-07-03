<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interests = [
            'Technology', 'Art', 'Travel', 'Food', 'Movies', 'Reading', 'Gaming', 'Fitness', 'Photography',
            'Dancing', 'Coding', 'Fashion', 'Writing', 'Science', 'History', 'Theater', 'Finance',
            'Automobiles', 'Politics', 'DIY', 'Spirituality', 'Anime', 'Pets', 'Social Activism',
            'Podcasts', 'Hiking', 'Astronomy', 'Comedy'
        ];

        foreach ($interests as $name) {
            Interest::firstOrCreate(['name' => $name], [
                'description' => fake()->sentence(),
                'icon' => fake()->randomElement([
                    'fas fa-camera', 'fas fa-gamepad', 'fas fa-book', 'fas fa-code', 'fas fa-music',
                    'fas fa-palette', 'fas fa-hiking', 'fas fa-globe', 'fas fa-laptop', 'fas fa-dumbbell'
                ])
            ]);
        }
    }
}
