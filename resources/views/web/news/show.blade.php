@extends('layouts.public')

@section('title', $article->title)
@section('meta_description', $article->meta_description ?: str($article->summary ?: $article->body)->limit(155))

@section('content')
    <article>
        <header class="article-hero">
            <div class="public-shell article-hero__grid">
                <div class="article-hero__copy">
                    <a class="back-link" href="{{ route('news.index') }}"><x-public.icon name="arrow-right" :size="17" /> {{ __('public.actions.back_to_news') }}</a>
                    <div class="meta-row">
                        <span>{{ $article->category ?: __('public.nav.news') }}</span>
                        <span>{{ __('public.labels.reading_time', ['count' => $article->reading_time_minutes]) }}</span>
                    </div>
                    <h1>{{ $article->title }}</h1>
                    <p>{{ $article->summary }}</p>
                    <div class="article-byline">
                        <strong>{{ $article->author_name ?: __('public.brand.name') }}</strong>
                        <span>{{ __('public.labels.published', ['date' => $article->published_at?->locale(app()->getLocale())->translatedFormat('j F Y')]) }}</span>
                    </div>
                </div>
                <figure class="article-hero__media">
                    <img src="{{ $article->image_path ? asset('storage/'.$article->image_path) : asset('images/public/laboratory-testing.png') }}" alt="" fetchpriority="high">
                </figure>
            </div>
        </header>

        <section class="public-section">
            <div class="public-shell article-reading-layout">
                <div class="article-body">
                    {!! nl2br(e($article->body)) !!}
                </div>
                <aside class="article-aside">
                    <div>
                        <span>{{ __('public.news.source') }}</span>
                        <strong>{{ $article->source_name ?: __('public.brand.name') }}</strong>
                        @if ($article->source_url)
                            <a class="text-link" href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer">
                                {{ __('public.actions.view_details') }} <x-public.icon name="external-link" :size="16" />
                            </a>
                        @endif
                    </div>
                    @if ($article->attachment_path)
                        <a class="button button--primary" href="{{ asset('storage/'.$article->attachment_path) }}" download>
                            <x-public.icon name="download" :size="18" /> {{ __('public.actions.download') }}
                        </a>
                    @endif
                </aside>
            </div>
        </section>
    </article>

    @if ($relatedArticles->isNotEmpty())
        <section class="public-section public-section--muted">
            <div class="public-shell">
                <x-public.section-heading :title="__('public.news.related_title')" eyebrow="More from NBTS" />
                <div class="article-grid">
                    @foreach ($relatedArticles as $relatedArticle)
                        <x-public.article-card :article="$relatedArticle" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
