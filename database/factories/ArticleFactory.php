<?php

namespace Database\Factories;

use App\ArticleStatus;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category' => fake()->randomElement(['Donor Education', 'Health', 'Campaigns']),
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'author_name' => 'NBTS Tanzania',
            'source_name' => null,
            'source_url' => null,
            'image_path' => null,
            'attachment_path' => null,
            'attachment_name' => null,
            'attachment_mime' => null,
            'is_featured' => false,
            'reading_time_minutes' => 1,
            'meta_description' => fake()->sentence(),
            'status' => ArticleStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function publication(): static
    {
        return $this->published()->state(fn (array $attributes): array => [
            'attachment_path' => 'publications/nbts-guidance.pdf',
            'attachment_name' => 'NBTS Guidance.pdf',
            'attachment_mime' => 'application/pdf',
        ]);
    }
}
