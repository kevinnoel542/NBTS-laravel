<?php

use App\ArticleStatus;
use App\BloodGroup;
use App\CampaignStatus;
use App\Models\Article;
use App\Models\BloodCenter;
use App\Models\Campaign;
use Illuminate\Support\Facades\Storage;

test('campaign discovery exposes only current or future campaigns at active centers', function () {
    $center = BloodCenter::factory()->create(['name' => 'Dodoma Blood Center']);
    $inactiveCenter = BloodCenter::factory()->inactive()->create();
    $emergency = Campaign::factory()->emergency()->create([
        'blood_center_id' => $center,
        'title' => 'Dodoma B Positive Emergency Appeal',
        'target_blood_group' => BloodGroup::BPositive,
        'image_path' => 'https://cdn.example.test/campaign.jpg',
    ]);
    Campaign::factory()->create([
        'blood_center_id' => $center,
        'title' => 'Community Donor Day',
    ]);
    Campaign::factory()->ongoing()->create([
        'blood_center_id' => $center,
        'title' => 'Expired stale campaign',
        'end_date' => now()->subMinute(),
    ]);
    Campaign::factory()->create([
        'blood_center_id' => $center,
        'status' => CampaignStatus::Completed,
    ]);
    Campaign::factory()->create(['blood_center_id' => $inactiveCenter]);

    $this->getJson(route('api.v1.campaigns.index'))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $emergency->id)
        ->assertJsonPath('data.0.category', 'emergency')
        ->assertJsonPath('data.0.blood_group', 'B+')
        ->assertJsonPath('data.0.center_name', 'Dodoma Blood Center')
        ->assertJsonPath('data.0.urgent', true)
        ->assertJsonPath('data.0.image_url', 'https://cdn.example.test/campaign.jpg')
        ->assertJsonPath('meta.total', 2);
});

test('campaign discovery supports validated filters and hides unavailable detail records', function () {
    $center = BloodCenter::factory()->create();
    $visible = Campaign::factory()->emergency()->create([
        'blood_center_id' => $center,
        'title' => 'O Negative Appeal',
        'target_blood_group' => BloodGroup::ONegative,
    ]);
    Campaign::factory()->create(['blood_center_id' => $center]);
    $hidden = Campaign::factory()->create([
        'blood_center_id' => $center,
        'status' => CampaignStatus::Cancelled,
    ]);

    $this->getJson(route('api.v1.campaigns.index', [
        'q' => 'Negative',
        'type' => 'emergency',
        'blood_group' => 'O-',
        'center_id' => $center->id,
    ]))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visible->id);

    $this->getJson(route('api.v1.campaigns.show', $visible))
        ->assertOk()
        ->assertJsonPath('data.id', $visible->id);
    $this->getJson(route('api.v1.campaigns.show', $hidden))->assertNotFound();
    $this->getJson(route('api.v1.campaigns.index', ['status' => 'completed']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

test('article discovery returns only already published content with flutter aliases', function () {
    $featured = Article::factory()->published()->create([
        'title' => 'Prepare Before Donation',
        'category' => 'Donor Education',
        'summary' => 'Practical preparation guidance.',
        'body' => 'Drink water and eat before attending the center.',
        'is_featured' => true,
    ]);
    Article::factory()->published()->create(['is_featured' => false]);
    Article::factory()->create();
    Article::factory()->create([
        'status' => ArticleStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $this->getJson(route('api.v1.articles.index', [
        'q' => 'preparation',
        'category' => 'Donor Education',
        'featured' => 1,
    ]))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $featured->id)
        ->assertJsonPath('data.0.article_id', $featured->id)
        ->assertJsonPath('data.0.summary', 'Practical preparation guidance.')
        ->assertJsonPath('data.0.content', 'Drink water and eat before attending the center.')
        ->assertJsonPath('data.0.status', 'published');
});

test('article detail hides draft and future scheduled content', function () {
    $published = Article::factory()->published()->create();
    $draft = Article::factory()->create();
    $future = Article::factory()->create([
        'status' => ArticleStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $this->getJson(route('api.v1.articles.show', $published))
        ->assertOk()
        ->assertJsonPath('data.id', $published->id);
    $this->getJson(route('api.v1.articles.show', $draft))->assertNotFound();
    $this->getJson(route('api.v1.articles.show', $future))->assertNotFound();
});

test('publication discovery reuses only approved articles with public attachments', function () {
    Storage::fake('public');
    $publication = Article::factory()->publication()->create([
        'title' => 'National Donor Guidance',
        'category' => 'Guidance',
        'attachment_path' => 'publications/donor-guidance.pdf',
        'attachment_name' => 'Donor Guidance.pdf',
    ]);
    Article::factory()->published()->create(['attachment_path' => null]);
    Article::factory()->publication()->create([
        'status' => ArticleStatus::Draft,
        'published_at' => null,
    ]);

    $expectedUrl = Storage::disk('public')->url('publications/donor-guidance.pdf');

    $this->getJson(route('api.v1.publications.index', ['category' => 'Guidance']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $publication->id)
        ->assertJsonPath('data.0.file_name', 'Donor Guidance.pdf')
        ->assertJsonPath('data.0.download_url', $expectedUrl);

    $this->getJson(route('api.v1.publications.show', $publication))
        ->assertOk()
        ->assertJsonPath('data.download_url', $expectedUrl);
});

test('donation schedules derive from visible campaign and center records', function () {
    $center = BloodCenter::factory()->create([
        'name' => 'Mwanza Blood Center',
        'city' => 'Mwanza',
        'opening_hours' => '08:00-16:00',
    ]);
    $schedule = Campaign::factory()->create([
        'blood_center_id' => $center,
        'title' => 'Lake Zone Collection Day',
        'location' => null,
    ]);
    Campaign::factory()->create([
        'blood_center_id' => $center,
        'end_date' => now()->subDay(),
    ]);

    $this->getJson(route('api.v1.schedules.index', ['q' => 'Mwanza']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $schedule->id)
        ->assertJsonPath('data.0.center_name', 'Mwanza Blood Center')
        ->assertJsonPath('data.0.city', 'Mwanza')
        ->assertJsonPath('data.0.opening_hours', '08:00-16:00')
        ->assertJsonPath('data.0.location', $center->address);

    $this->getJson(route('api.v1.schedules.show', $schedule))
        ->assertOk()
        ->assertJsonPath('data.schedule_id', $schedule->id);
});

test('public discovery endpoints enforce bounded query validation', function () {
    $this->getJson(route('api.v1.articles.index', ['per_page' => 51]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('per_page');

    $this->getJson(route('api.v1.schedules.index', ['blood_group' => 'X']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('blood_group');
});
