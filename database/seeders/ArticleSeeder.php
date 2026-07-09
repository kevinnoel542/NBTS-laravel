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
                'title' => 'Maandalizi kabla ya kuchangia damu',
                'slug' => 'before-you-donate',
                'category' => 'Elimu ya Mchangiaji',
                'summary' => 'Kula chakula cha kutosha, kunywa maji, beba kitambulisho, na fuata maelekezo ya wahudumu baada ya kuchangia.',
                'body' => '<p>Kabla ya kwenda kuchangia damu, mchangiaji anatakiwa kujisikia vizuri, kula chakula cha kutosha, kunywa maji, na kubeba kitambulisho.</p><p>Baada ya kuchangia, pumzika kituoni, pata vinywaji au chakula chepesi, na epuka mazoezi mazito mara baada ya kuchangia. Uamuzi wa mwisho wa usalama hufanywa na wahudumu baada ya screening.</p>',
                'author_name' => 'NBTS Tanzania',
                'source_name' => 'National Blood Transfusion Service',
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Kwa nini donation interval ni muhimu',
                'slug' => 'why-waiting-periods-matter',
                'category' => 'Afya',
                'summary' => 'Muda wa kusubiri baada ya kuchangia husaidia kulinda afya ya mchangiaji wakati mwili unarejesha damu.',
                'body' => '<p>Mchangiaji anatakiwa kusubiri kabla ya kuchangia tena ili mwili upate muda wa kurejesha damu. Kwa kawaida wanaume huweza kuchangia tena baada ya takriban miezi 3, na wanawake baada ya takriban miezi 4.</p><p>Wahudumu wa kituo huthibitisha muda sahihi kulingana na historia ya uchangiaji, hali ya afya, na screening ya siku husika.</p>',
                'author_name' => 'NBTS Tanzania',
                'source_name' => 'National Blood Transfusion Service',
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Taarifa za uhitaji wa damu kwenye kampeni',
                'slug' => 'urgent-blood-requests',
                'category' => 'Kampeni',
                'summary' => 'Taarifa za kampeni husaidia donors kujua maeneo ambayo yanahitaji mwitikio zaidi wa uchangiaji.',
                'body' => '<p>NBTS inaweza kutumia news na campaign updates kuwajulisha wananchi kuhusu kampeni za uchangiaji damu. Taarifa zinapaswa kuonyesha mahali, muda, kituo husika, na kama kuna blood group inayolengwa.</p><p>Donors wanashauriwa kuthibitisha eligibility, kula na kunywa maji, na kufuata maelekezo ya staff kituoni.</p>',
                'author_name' => 'NBTS Tanzania',
                'source_name' => 'National Blood Transfusion Service',
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDay(),
            ],
        ];

        foreach ($articles as $article) {
            Article::updateOrCreate(
                ['slug' => $article['slug']],
                $article
            );
        }
    }
}
