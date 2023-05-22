<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;
use App\Models\Tag;
use App\Models\Genre;


class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Movie::factory()->has(Genre::factory()->count(3))->has(Tag::factory()->count(3))->count(5)->create();
    }
}
