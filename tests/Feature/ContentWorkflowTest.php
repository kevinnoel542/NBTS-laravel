<?php

use App\Actions\Content\SaveArticle;
use App\ArticleStatus;
use App\Data\SaveArticleData;
use App\Livewire\Operations\Workspace;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('an administrator can publish content and its visibility change is audited', function () {
    $admin = User::factory()->nbtsAdmin()->create();
    $publishAt = CarbonImmutable::now()->addHour();

    $article = app(SaveArticle::class)->execute(
        $admin,
        new SaveArticleData(
            title: 'Preparing for your first blood donation',
            slug: 'preparing-for-your-first-blood-donation',
            category: 'Donor Education',
            summary: 'A concise guide explaining what first-time donors should expect at an NBTS center.',
            body: 'Bring a valid form of identification, eat a balanced meal, drink water, and allow enough time for screening and recovery.',
            authorName: 'NBTS Tanzania',
            sourceName: null,
            sourceUrl: null,
            imagePath: null,
            attachmentPath: null,
            attachmentName: null,
            attachmentMime: null,
            isFeatured: true,
            status: ArticleStatus::Published,
            publishedAt: $publishAt,
            metaDescription: 'Official preparation guidance for first-time blood donors in Tanzania.',
        ),
    );

    expect($article->status)->toBe(ArticleStatus::Published)
        ->and($article->published_at?->toIso8601String())->toBe($publishAt->toIso8601String())
        ->and($article->reading_time_minutes)->toBeGreaterThanOrEqual(1)
        ->and(AuditLog::query()->where('action', 'content.article_created')->count())->toBe(1);
});

test('published content requires a reason before it is removed from public view', function () {
    $admin = User::factory()->nbtsAdmin()->create();
    $article = Article::factory()->published()->create();

    $data = new SaveArticleData(
        title: $article->title,
        slug: $article->slug,
        category: $article->category ?? 'News',
        summary: $article->summary ?? 'A sufficiently detailed public summary.',
        body: $article->body ?? 'Sufficient body text for the public content record.',
        authorName: $article->author_name,
        sourceName: $article->source_name,
        sourceUrl: $article->source_url,
        imagePath: $article->image_path,
        attachmentPath: $article->attachment_path,
        attachmentName: $article->attachment_name,
        attachmentMime: $article->attachment_mime,
        isFeatured: $article->is_featured,
        status: ArticleStatus::Archived,
        publishedAt: null,
        metaDescription: $article->meta_description,
    );

    expect(fn () => app(SaveArticle::class)->execute($admin, $data, $article))
        ->toThrow(ValidationException::class)
        ->and($article->refresh()->status)->toBe(ArticleStatus::Published)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('staff without content management permission cannot update an article', function () {
    $staff = User::factory()->staff()->create();
    $article = Article::factory()->create();

    $data = new SaveArticleData(
        title: $article->title,
        slug: $article->slug,
        category: $article->category ?? 'News',
        summary: $article->summary ?? 'A sufficiently detailed public summary.',
        body: $article->body ?? 'Sufficient body text for the public content record.',
        authorName: $article->author_name,
        sourceName: null,
        sourceUrl: null,
        imagePath: null,
        attachmentPath: null,
        attachmentName: null,
        attachmentMime: null,
        isFeatured: false,
        status: ArticleStatus::Draft,
        publishedAt: null,
        metaDescription: null,
    );

    expect(fn () => app(SaveArticle::class)->execute($staff, $data, $article))
        ->toThrow(AuthorizationException::class)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('the content workspace uploads and publishes a publication document', function () {
    Storage::fake('public');
    $admin = User::factory()->nbtsAdmin()->create();
    $document = UploadedFile::fake()->create('donor-safety-guide.pdf', 120, 'application/pdf');

    Livewire::actingAs($admin)
        ->test(Workspace::class, ['workspace' => 'content'])
        ->set('tab', 'publications')
        ->call('openArticleEditor')
        ->set('articleTitle', 'Donor safety guide')
        ->set('articleSlug', 'donor-safety-guide')
        ->set('articleSummary', 'Official practical guidance for a safe and comfortable blood donation visit.')
        ->set('articleBody', 'This guide covers preparation, screening, collection, immediate aftercare, and when a donor should contact a health professional.')
        ->set('articleStatus', ArticleStatus::Published->value)
        ->set('articlePublishedAt', now()->format('Y-m-d\TH:i'))
        ->set('articleAttachmentUpload', $document)
        ->call('saveArticle')
        ->assertHasNoErrors()
        ->assertSet('articleEditorId', null);

    $article = Article::query()->sole();

    expect($article->category)->toBe('Publication')
        ->and($article->attachment_name)->toBe('donor-safety-guide.pdf')
        ->and($article->attachment_path)->not->toBeNull()
        ->and($article->status)->toBe(ArticleStatus::Published);

    Storage::disk('public')->assertExists($article->attachment_path);
});

test('content tabs keep news faqs and public pages in their intended queues', function () {
    $admin = User::factory()->nbtsAdmin()->create();
    $news = Article::factory()->create(['category' => 'Health', 'title' => 'Safe donation preparation']);
    $faq = Article::factory()->create(['category' => 'FAQ', 'title' => 'Donation frequency question']);
    $publicPage = Article::factory()->create(['category' => 'Public Page', 'title' => 'National service standards']);

    Livewire::actingAs($admin)
        ->test(Workspace::class, ['workspace' => 'content'])
        ->assertSee($news->title)
        ->assertDontSee($faq->title)
        ->assertDontSee($publicPage->title)
        ->set('tab', 'faqs')
        ->assertSee($faq->title)
        ->assertDontSee($news->title)
        ->assertDontSee($publicPage->title)
        ->set('tab', 'public_pages')
        ->assertSee($publicPage->title)
        ->assertDontSee($news->title)
        ->assertDontSee($faq->title);
});
