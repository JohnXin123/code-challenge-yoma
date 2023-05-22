<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Author;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $imagePath = public_path('images/one.jpg');
        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);

        return [
            'title' => $this->faker->sentence,
            'summary' => $this->faker->paragraph,
            'imdb_rating' => $this->faker->randomFloat(1, 0, 10),
            'pdf_link' => $this->faker->url,
            'cover_image' => $base64Image,
            'author_id' => Author::factory()->create()->id,
        ];
    }
}
