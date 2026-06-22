<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Before You Donate',
                'category' => 'Health Tip',
                'summary' => 'Eat well, hydrate, carry ID, and avoid heavy exercise right after donating.',
                'body' => 'A good meal, enough water, and a valid ID help make donation safer and faster.',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Why Waiting Periods Matter',
                'category' => 'Eligibility',
                'summary' => 'Donation waiting periods protect donor health while the body recovers.',
                'body' => 'NBTS uses eligibility rules to protect both donors and patients who receive blood.',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Urgent Blood Requests',
                'category' => 'Alert',
                'summary' => 'Urgent blood type appeals help centers recover low stock quickly.',
                'body' => 'When stock is low, NBTS can publish emergency campaigns for specific blood groups.',
                'status' => 'published',
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($articles as $article) {
            Article::updateOrCreate(
                ['title' => $article['title']],
                $article
            );
        }
    }
}
