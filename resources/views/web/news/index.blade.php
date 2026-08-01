@extends('layouts.public')

@section('title', __('public.news.title'))
@section('meta_description', __('public.news.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.news.eyebrow')"
        :title="__('public.news.hero_title')"
        :copy="__('public.news.hero_copy')"
        :image="asset('images/public/laboratory-testing.png')"
        image-alt="NBTS laboratory professionals testing donated blood"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('publications') }}">{{ __('public.nav.publications') }}</a>
            <a class="button button--secondary" href="{{ route('campaigns.index') }}">{{ __('public.actions.view_campaigns') }}</a>
        </x-slot:actions>
    </x-public.page-hero>

    <section class="directory-section">
        <div class="public-shell">
            <form class="filter-bar" action="{{ route('news.index') }}" method="GET">
                <label class="search-field">
                    <span class="sr-only">{{ __('public.labels.search_news') }}</span>
                    <x-public.icon name="search" :size="19" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('public.labels.search_news') }}">
                </label>
                <label>
                    <span class="sr-only">{{ __('public.labels.all_categories') }}</span>
                    <select name="category">
                        <option value="">{{ __('public.labels.all_categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="button button--primary" type="submit">{{ __('public.actions.search') }}</button>
                @if (request()->hasAny(['q', 'category']))
                    <a class="button button--text" href="{{ route('news.index') }}">{{ __('public.actions.clear') }}</a>
                @endif
            </form>

            <div class="directory-heading">
                <h2>{{ __('public.news.directory_title') }}</h2>
                <span>{{ __('public.labels.results', ['count' => $articles->total()]) }}</span>
            </div>

            @if ($articles->isNotEmpty())
                <div class="article-grid">
                    @foreach ($articles as $article)
                        <x-public.article-card :article="$article" class="reveal-on-scroll" />
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $articles->links() }}</div>
            @else
                <x-public.empty-state :title="__('public.news.empty_title')" :copy="__('public.news.empty_copy')" icon="newspaper">
                    <a class="button button--secondary" href="{{ route('news.index') }}">{{ __('public.actions.clear') }}</a>
                </x-public.empty-state>
            @endif
        </div>
    </section>
@endsection
