<?php

namespace Database\Seeders;

use App\ArticleStatus;
use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'How to prepare before donating blood',
                'slug' => 'before-you-donate',
                'category' => 'Donor education',
                'summary' => 'Eat a balanced meal, drink water, carry identification, and follow the care team guidance after donating.',
                'body' => '<p>Before donating blood, make sure you feel well, eat a balanced meal, drink enough water, and carry identification.</p><p>After donating, rest at the center, have a drink or light snack, and avoid strenuous activity. Trained staff make the final safety decision after screening.</p>',
                'author_name' => 'NBTS Tanzania',
                'source_name' => 'National Blood Transfusion Service',
                'is_featured' => true,
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Why donation intervals matter',
                'slug' => 'why-waiting-periods-matter',
                'category' => 'Health',
                'summary' => 'The waiting period protects donors while the body restores the blood that was donated.',
                'body' => '<p>Donors need time between whole-blood donations so the body can recover. The configured NBTS interval is three months for men and four months for women.</p><p>Staff confirm the final date using donation history, current health, and screening results.</p>',
                'author_name' => 'NBTS Tanzania',
                'source_name' => 'National Blood Transfusion Service',
                'is_featured' => false,
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Responding to emergency blood campaigns',
                'slug' => 'urgent-blood-requests',
                'category' => 'Campaigns',
                'summary' => 'Campaign notices help donors find locations where a stronger donation response is needed.',
                'body' => '<p>NBTS campaign updates identify the location, time, responsible center, and any blood group being prioritized.</p><p>Donors should confirm eligibility, eat and drink water, and follow staff instructions at the center.</p>',
                'author_name' => 'NBTS Tanzania',
                'source_name' => 'National Blood Transfusion Service',
                'is_featured' => false,
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($articles as $article) {
            Article::query()->firstOrCreate(
                ['slug' => $article['slug']],
                $article,
            );
        }
    }
}
