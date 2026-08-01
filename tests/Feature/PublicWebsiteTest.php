<?php

use App\ArticleStatus;
use App\CampaignStatus;
use App\CampaignType;
use App\DonationStatus;
use App\Models\Article;
use App\Models\BloodCenter;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;

test('public information pages are available in both supported languages', function () {
    $routes = [
        'home',
        'about',
        'contact',
        'donate',
        'download',
        'eligibility',
        'faq',
        'impact',
        'news.index',
        'publications',
        'services',
        'centers.index',
        'campaigns.index',
    ];

    foreach ($routes as $routeName) {
        $this->get(route($routeName))->assertOk();
    }

    $this->withSession(['locale' => 'sw'])
        ->get(route('about'))
        ->assertOk()
        ->assertSee('Mfumo wa taifa');
});

test('center directory only exposes active centers and supports validated filters', function () {
    $visibleCenter = BloodCenter::factory()->create([
        'city' => 'Dodoma',
        'name' => 'Dodoma Regional Blood Centre',
    ]);
    $otherCenter = BloodCenter::factory()->create([
        'city' => 'Mwanza',
        'name' => 'Mwanza Regional Blood Centre',
    ]);
    $inactiveCenter = BloodCenter::factory()->inactive()->create([
        'city' => 'Dodoma',
        'name' => 'Closed Donation Point',
    ]);

    $this->get(route('centers.index', ['city' => 'Dodoma']))
        ->assertOk()
        ->assertSee($visibleCenter->name)
        ->assertDontSee($otherCenter->name)
        ->assertDontSee($inactiveCenter->name);

    $this->get(route('centers.show', $visibleCenter))
        ->assertOk()
        ->assertSee($visibleCenter->phone);

    $this->get(route('centers.show', $inactiveCenter))->assertNotFound();

    $this->get(route('centers.index', ['q' => str_repeat('x', 101)]))
        ->assertSessionHasErrors('q');
});

test('campaign directory exposes current campaigns and prioritizes emergency appeals', function () {
    $center = BloodCenter::factory()->create();
    $standardCampaign = Campaign::factory()->recycle($center)->create([
        'title' => 'Community Donation Day',
        'campaign_type' => CampaignType::Standard,
    ]);
    $emergencyCampaign = Campaign::factory()->recycle($center)->emergency()->create([
        'title' => 'Emergency O Negative Appeal',
    ]);
    $completedCampaign = Campaign::factory()->recycle($center)->create([
        'title' => 'Completed Campaign',
        'start_date' => now()->subDays(2),
        'end_date' => now()->subDay(),
        'status' => CampaignStatus::Completed,
    ]);

    $response = $this->get(route('campaigns.index'))
        ->assertOk()
        ->assertSee($emergencyCampaign->title)
        ->assertSee($standardCampaign->title)
        ->assertDontSee($completedCampaign->title);

    expect(strpos($response->getContent(), $emergencyCampaign->title))
        ->toBeLessThan(strpos($response->getContent(), $standardCampaign->title));

    $this->get(route('campaigns.index', ['type' => CampaignType::Emergency->value]))
        ->assertOk()
        ->assertSee($emergencyCampaign->title)
        ->assertDontSee($standardCampaign->title);

    $this->get(route('campaigns.show', $completedCampaign))->assertNotFound();
});

test('news directory and detail pages expose published content only', function () {
    $publishedArticle = Article::factory()->published()->create([
        'category' => 'Donor Education',
        'title' => 'Hydration Before Donation',
    ]);
    $draftArticle = Article::factory()->create([
        'title' => 'Unapproved Draft Guidance',
    ]);

    $this->get(route('news.index', ['q' => 'Hydration']))
        ->assertOk()
        ->assertSee($publishedArticle->title)
        ->assertDontSee($draftArticle->title);

    $this->get(route('news.show', $publishedArticle))
        ->assertOk()
        ->assertSeeText(str($publishedArticle->body)->before("\n\n")->toString());

    $this->get(route('news.show', $draftArticle))->assertNotFound();
});

test('publication library includes only published articles with attachments', function () {
    $publication = Article::factory()->publication()->create([
        'title' => 'National Donor Guidance',
    ]);
    $newsOnly = Article::factory()->published()->create([
        'title' => 'News Without Download',
    ]);
    Article::factory()->create([
        'attachment_path' => 'publications/draft.pdf',
        'status' => ArticleStatus::Draft,
        'title' => 'Draft Publication',
    ]);

    $this->get(route('publications'))
        ->assertOk()
        ->assertSee($publication->title)
        ->assertDontSee($newsOnly->title)
        ->assertDontSee('Draft Publication');
});

test('impact page uses aggregate completed donation data', function () {
    $donor = User::factory()->donor()->create();
    $center = BloodCenter::factory()->create();

    Donation::factory()->recycle([$donor, $center])->create([
        'status' => DonationStatus::Completed,
        'volume_ml' => 500,
    ]);
    Donation::factory()->recycle([$donor, $center])->create([
        'status' => DonationStatus::Failed,
        'volume_ml' => 500,
    ]);

    $this->get(route('impact'))
        ->assertOk()
        ->assertSee('Completed donations')
        ->assertSee('Potential lives supported');
});
