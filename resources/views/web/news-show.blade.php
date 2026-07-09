@extends('layouts.app')

@section('title', $article->title . ' - NBTS News')
@section('meta_description', $article->meta_description ?: ($article->summary ?: 'Soma taarifa ya NBTS iliyochapishwa kwenye public news center.'))

@section('content')
<section class="pharma-hero news-detail-hero">
    <div class="section-shell">
        <div class="pharma-hero-top">
            <div class="pharma-hero-copy">
                <a href="{{ route('news') }}" class="news-back-link">Back to News</a>
                <div class="pharma-label-row">
                    <span>{{ $article->category ?? 'NBTS News' }}</span>
                    <span>{{ optional($article->published_at)->format('d M Y') ?? 'Published' }}</span>
                </div>
                <span class="pharma-kicker">News article</span>
                <h1>{{ $article->title }}</h1>
                <p class="pharma-lead">{{ $article->summary }}</p>
            </div>
            <aside class="pharma-hero-summary news-detail-summary">
                <span>Article details</span>
                <div class="news-detail-facts">
                    <div>
                        <span>Read time</span>
                        <strong>{{ $article->reading_time_minutes }} min</strong>
                    </div>
                    <div>
                        <span>Author</span>
                        <strong>{{ $article->author_name ?? 'NBTS Tanzania' }}</strong>
                    </div>
                    <div>
                        <span>Source</span>
                        <strong>{{ $article->source_name ?? 'NBTS' }}</strong>
                    </div>
                </div>
                @if($article->attachment_path)
                    <a href="{{ $article->attachmentUrl() }}" class="primary-btn" target="_blank" rel="noopener">Download File</a>
                @endif
            </aside>
        </div>
    </div>
</section>

<section class="pharma-section pharma-neutral">
    <div class="section-shell">
        <div class="news-detail-layout">
            <article class="news-article-shell">
                @if($article->image_path)
                    <figure class="news-detail-image">
                        <img src="{{ $article->imageUrl() }}" alt="{{ $article->title }}">
                    </figure>
                @endif

                <div class="news-rich-content">
                    @if($article->body)
                        {!! $article->body !!}
                    @else
                        <p>{{ $article->summary }}</p>
                    @endif
                </div>
            </article>

            <aside class="news-aside-stack">
                @if($article->attachment_path)
                    <div class="news-aside-panel news-attachment-card">
                        <span class="pharma-kicker">Attachment</span>
                        <h2>{{ $article->attachment_name ?: 'Approved public file' }}</h2>
                        <p>Faili hii imetoka kwenye backend article record na inaonekana kwa public kwa sababu article imechapishwa.</p>
                        <a href="{{ $article->attachmentUrl() }}" class="primary-btn" target="_blank" rel="noopener">Download File</a>
                    </div>
                @endif

                <div class="news-aside-panel">
                    <span class="pharma-kicker">Public record</span>
                    <div class="center-info-list">
                        <div>
                            <span>Category</span>
                            <strong>{{ $article->category ?? 'News' }}</strong>
                        </div>
                        <div>
                            <span>Published</span>
                            <strong>{{ optional($article->published_at)->format('d M Y') ?? 'Published' }}</strong>
                        </div>
                        <div>
                            <span>Status</span>
                            <strong>{{ str($article->status)->headline() }}</strong>
                        </div>
                    </div>
                </div>

                @if($article->source_url)
                    <div class="news-aside-panel">
                        <span class="pharma-kicker">Source</span>
                        <h2>{{ $article->source_name ?? 'Source link' }}</h2>
                        <a href="{{ $article->source_url }}" class="secondary-btn" target="_blank" rel="noopener">Open Source</a>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>

@if($relatedArticles->isNotEmpty())
    <section class="pharma-section">
        <div class="section-shell">
            <div class="pharma-heading">
                <span class="pharma-kicker">Related news</span>
                <h2>Endelea kusoma taarifa nyingine.</h2>
            </div>
            <div class="news-list-grid">
                @foreach($relatedArticles as $related)
                    <article class="news-card">
                        <a href="{{ route('news.show', $related) }}" class="news-card-media" aria-label="Read {{ $related->title }}">
                            @if($related->image_path)
                                <img src="{{ $related->imageUrl() }}" alt="{{ $related->title }}">
                            @else
                                <div class="news-card-fallback">
                                    <span>{{ $related->category ?? 'NBTS' }}</span>
                                    <strong>NBTS News</strong>
                                    <small>{{ optional($related->published_at)->format('d M Y') ?? 'Published' }}</small>
                                </div>
                            @endif
                        </a>
                        <div class="news-card-body">
                            <div class="news-meta-row">
                                <span>{{ $related->category ?? 'News' }}</span>
                                <span>{{ $related->reading_time_minutes }} min read</span>
                            </div>
                            <a href="{{ route('news.show', $related) }}" class="news-title-link">
                                <h3>{{ $related->title }}</h3>
                            </a>
                            <p>{{ $related->summary }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
