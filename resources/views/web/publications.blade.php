@extends('layouts.public')

@section('title', __('public.publications.title'))
@section('meta_description', __('public.publications.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.publications.eyebrow')"
        :title="__('public.publications.hero_title')"
        :copy="__('public.publications.hero_copy')"
    >
        <x-slot:actions>
            <a class="button button--primary" href="{{ route('news.index') }}">{{ __('public.nav.news') }}</a>
            <a class="button button--secondary" href="{{ route('contact') }}">{{ __('public.actions.contact') }}</a>
        </x-slot:actions>
        <x-slot:aside>
            <div class="directory-summary">
                <x-public.icon name="file-text" :size="26" />
                <strong>{{ number_format($publications->total()) }}</strong>
                <span>{{ __('public.publications.library_title') }}</span>
                <small>Reviewed public documents</small>
            </div>
        </x-slot:aside>
    </x-public.page-hero>

    <section class="directory-section">
        <div class="public-shell">
            <div class="directory-heading">
                <h2>{{ __('public.publications.library_title') }}</h2>
                <span>{{ __('public.labels.results', ['count' => $publications->total()]) }}</span>
            </div>

            @if ($publications->isNotEmpty())
                <div class="publication-list">
                    @foreach ($publications as $publication)
                        <article class="reveal-on-scroll">
                            <span class="publication-list__icon"><x-public.icon name="file-text" :size="24" /></span>
                            <div>
                                <div class="meta-row">
                                    <span>{{ $publication->category ?: __('public.nav.publications') }}</span>
                                    <span>{{ __('public.labels.published', ['date' => $publication->published_at?->locale(app()->getLocale())->translatedFormat('j M Y')]) }}</span>
                                </div>
                                <h3>{{ $publication->title }}</h3>
                                <p>{{ $publication->summary }}</p>
                                @if ($publication->attachment_name)
                                    <small class="publication-list__filename">{{ $publication->attachment_name }}</small>
                                @endif
                            </div>
                            <a class="button button--secondary" href="{{ asset('storage/'.$publication->attachment_path) }}" download>
                                <x-public.icon name="download" :size="18" /> {{ __('public.actions.download') }}
                            </a>
                        </article>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $publications->links() }}</div>
            @else
                <x-public.empty-state :title="__('public.publications.empty_title')" :copy="__('public.publications.empty_copy')" icon="file-text" />
            @endif
        </div>
    </section>
@endsection
