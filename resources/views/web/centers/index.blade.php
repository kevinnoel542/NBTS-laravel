@extends('layouts.public')

@section('title', __('public.centers.title'))
@section('meta_description', __('public.centers.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.centers.eyebrow')"
        :title="__('public.centers.hero_title')"
        :copy="__('public.centers.hero_copy')"
    >
        <x-slot:aside>
            <div class="directory-summary">
                <x-public.icon name="map-pin" :size="26" />
                <strong>{{ number_format($centers->total()) }}</strong>
                <span>{{ __('public.centers.directory_title') }}</span>
                <small>{{ $cities->count() }} {{ str('city')->plural($cities->count()) }}</small>
            </div>
        </x-slot:aside>
    </x-public.page-hero>

    <section class="directory-section">
        <div class="public-shell">
            <form class="filter-bar" action="{{ route('centers.index') }}" method="GET">
                <label class="search-field">
                    <span class="sr-only">{{ __('public.labels.search_centers') }}</span>
                    <x-public.icon name="search" :size="19" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('public.labels.search_centers') }}">
                </label>
                <label>
                    <span class="sr-only">{{ __('public.labels.all_cities') }}</span>
                    <select name="city">
                        <option value="">{{ __('public.labels.all_cities') }}</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="button button--primary" type="submit">{{ __('public.actions.search') }}</button>
                @if (request()->hasAny(['q', 'city']))
                    <a class="button button--text" href="{{ route('centers.index') }}">{{ __('public.actions.clear') }}</a>
                @endif
            </form>

            <div class="directory-heading">
                <h2>{{ __('public.centers.directory_title') }}</h2>
                <span>{{ __('public.labels.results', ['count' => $centers->total()]) }}</span>
            </div>

            @if ($centers->isNotEmpty())
                <div class="center-directory-grid">
                    @foreach ($centers as $center)
                        <x-public.center-card :center="$center" class="reveal-on-scroll" />
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $centers->links() }}</div>
            @else
                <x-public.empty-state :title="__('public.centers.empty_title')" :copy="__('public.centers.empty_copy')">
                    <a class="button button--secondary" href="{{ route('centers.index') }}">{{ __('public.actions.clear') }}</a>
                </x-public.empty-state>
            @endif
        </div>
    </section>
@endsection
