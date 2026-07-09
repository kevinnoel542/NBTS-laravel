@extends('layouts.app')

@section('title', 'Habari na Elimu - NBTS Tanzania')
@section('meta_description', 'Soma habari, elimu ya mchangiaji, updates za kampeni, na taarifa za umma zilizochapishwa na NBTS kutoka kwenye backend news management system.')

@section('content')
@php
    $activeCategory = request('category');
    $hasFilters = request('search') || request('category');
    $latestDate = optional($featuredArticle?->published_at)->format('d M Y') ?? 'Published data';
@endphp

<section class="pharma-hero news-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <div class="pharma-label-row">
                    <span>Jamhuri ya Muungano wa Tanzania</span>
                    <span>Wizara ya Afya</span>
                </div>
                <span class="pharma-kicker">News center</span>
                <h1>Habari na elimu kutoka NBTS.</h1>
                <p class="pharma-lead">Soma taarifa zilizochapishwa kutoka kwenye backend kuhusu donation guidance, kampeni, huduma, na elimu ya mchangiaji.</p>
            </div>
            <div class="pharma-hero-summary news-command-panel">
                <span>Backend publishing</span>
                <p class="pharma-lead">Public page inaonyesha records zenye status ya published pekee. Draft na archived articles hazionekani kwa wageni.</p>
                <div class="pharma-action-row">
                    <a href="#published-news" class="primary-btn">Read News</a>
                    <a href="{{ route('campaigns.index') }}" class="secondary-btn">View Campaigns</a>
                    <a href="{{ route('publications') }}" class="pharma-link">Publications</a>
                </div>
            </div>
        </div>

        <div class="news-ledger" aria-label="News publishing summary">
            <div>
                <span>Published</span>
                <strong>{{ number_format($articleStats['total']) }} articles</strong>
            </div>
            <div>
                <span>Categories</span>
                <strong>{{ number_format($articleStats['categories']) }} groups</strong>
            </div>
            <div>
                <span>Files</span>
                <strong>{{ number_format($articleStats['attachments']) }} attachments</strong>
            </div>
            <div>
                <span>Latest</span>
                <strong>{{ $latestDate }}</strong>
            </div>
        </div>
    </div>
</section>

@if($featuredArticle)
    <section class="pharma-section">
        <div class="section-shell">
            <div class="news-feature-grid">
                <article class="news-feature-card">
                    <div class="news-feature-copy">
                        <span class="pharma-kicker">{{ $featuredArticle->category ?? 'Featured news' }}</span>
                        <h2>{{ $featuredArticle->title }}</h2>
                        <p>{{ $featuredArticle->summary }}</p>
                        <div class="news-meta-row">
                            <span>{{ optional($featuredArticle->published_at)->format('d M Y') ?? 'Published' }}</span>
                            <span>{{ $featuredArticle->reading_time_minutes }} min read</span>
                            @if($featuredArticle->attachment_path)
                                <span>Attachment</span>
                            @endif
                        </div>
                        <div class="pharma-action-row">
                            <a href="{{ route('news.show', $featuredArticle) }}" class="primary-btn">Read Article</a>
                            @if($featuredArticle->attachment_path)
                                <a href="{{ $featuredArticle->attachmentUrl() }}" class="secondary-btn" target="_blank" rel="noopener">Download File</a>
                            @endif
                        </div>
                    </div>
                    <div class="news-feature-media">
                        @if($featuredArticle->image_path)
                            <img src="{{ $featuredArticle->imageUrl() }}" alt="{{ $featuredArticle->title }}">
                        @else
                            <div class="news-card-fallback">
                                <span>{{ $featuredArticle->category ?? 'NBTS' }}</span>
                                <strong>NBTS News</strong>
                                <small>{{ optional($featuredArticle->published_at)->format('d M Y') ?? 'Public update' }}</small>
                            </div>
                        @endif
                    </div>
                </article>
            </div>
        </div>
    </section>
@endif

<section id="published-news" class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="pharma-heading">
            <span class="pharma-kicker">Published articles</span>
            <h2>Tafuta na soma records zilizochapishwa na staff.</h2>
            <p>Search na filters zinatumia backend articles table. Kila card inaelekea kwenye detail page ya news husika.</p>
        </div>

        <div class="news-control-bar">
            <form action="{{ route('news') }}" method="GET" class="news-search-form" role="search">
                <label class="sr-only" for="news-search">Search news</label>
                <input id="news-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search news, elimu, kampeni">
                @if($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                @endif
                <button type="submit" class="primary-btn">Search</button>
            </form>

            <div class="news-category-rail" aria-label="News categories">
                <a href="{{ route('news', request('search') ? ['search' => request('search')] : []) }}" class="{{ $activeCategory ? '' : 'is-active' }}">All</a>
                @foreach($categories as $category)
                    <a href="{{ route('news', array_filter(['search' => request('search'), 'category' => $category->category])) }}" class="{{ $activeCategory === $category->category ? 'is-active' : '' }}">
                        {{ $category->category }}
                        <span>{{ $category->total }}</span>
                    </a>
                @endforeach
                @if($hasFilters)
                    <a href="{{ route('news') }}">Clear</a>
                @endif
            </div>
        </div>

        <div class="news-list-grid">
            @forelse($articles as $article)
                <article class="news-card">
                    <a href="{{ route('news.show', $article) }}" class="news-card-media" aria-label="Read {{ $article->title }}">
                        @if($article->image_path)
                            <img src="{{ $article->imageUrl() }}" alt="{{ $article->title }}">
                        @else
                            <div class="news-card-fallback">
                                <span>{{ $article->category ?? 'NBTS' }}</span>
                                <strong>NBTS News</strong>
                                <small>{{ optional($article->published_at)->format('d M Y') ?? 'Published' }}</small>
                            </div>
                        @endif
                    </a>
                    <div class="news-card-body">
                        <div class="news-meta-row">
                            <span>{{ $article->category ?? 'News' }}</span>
                            <span>{{ $article->reading_time_minutes }} min read</span>
                        </div>
                        <a href="{{ route('news.show', $article) }}" class="news-title-link">
                            <h3>{{ $article->title }}</h3>
                        </a>
                        <p>{{ $article->summary }}</p>
                        <div class="news-card-footer">
                            <span>{{ optional($article->published_at)->format('d M Y') ?? 'Published' }}</span>
                            <a href="{{ route('news.show', $article) }}">Read</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="news-empty-state">
                    <span class="pharma-kicker">No records</span>
                    <h2>Hakuna published news inayolingana na search hii.</h2>
                    <p>Badilisha search au category. Staff wakichapisha article mpya kwenye backend itaonekana hapa.</p>
                    <a href="{{ route('news') }}" class="secondary-btn">Show All News</a>
                </div>
            @endforelse
        </div>

        <div class="news-pagination">
            {{ $articles->links() }}
        </div>
    </div>
</section>

<section class="pharma-section">
    <div class="section-shell">
        <div class="news-support-grid">
            <div class="pharma-heading">
                <span class="pharma-kicker">Campaign updates</span>
                <h2>Kampeni zinazohusiana na taarifa za umma.</h2>
                <p>Campaign records zinatoka kwenye backend campaigns table na zinaelekeza donors kwenye campaign detail page.</p>
            </div>
            <div class="news-campaign-list">
                @forelse($campaigns as $campaign)
                    <a href="{{ route('campaigns.show', $campaign) }}">
                        <span>{{ str($campaign->status)->headline() }}</span>
                        <strong>{{ $campaign->title }}</strong>
                        <p>{{ $campaign->bloodCenter->name ?? $campaign->location ?? 'NBTS campaign' }}</p>
                    </a>
                @empty
                    <div>
                        <span>No active campaigns</span>
                        <strong>Hakuna active au upcoming campaign kwa sasa.</strong>
                        <p>Campaign mpya zitaonekana hapa zikichapishwa.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
