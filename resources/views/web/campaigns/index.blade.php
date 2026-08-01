@extends('layouts.public')

@section('title', __('public.campaigns.title'))
@section('meta_description', __('public.campaigns.meta'))

@section('content')
    <x-public.page-hero
        :eyebrow="__('public.campaigns.eyebrow')"
        :title="__('public.campaigns.hero_title')"
        :copy="__('public.campaigns.hero_copy')"
    >
        <x-slot:aside>
            <div class="directory-summary directory-summary--red">
                <x-public.icon name="calendar-days" :size="26" />
                <strong>{{ number_format($campaigns->total()) }}</strong>
                <span>{{ __('public.campaigns.directory_title') }}</span>
                <small>{{ __('public.home.hero_note') }}</small>
            </div>
        </x-slot:aside>
    </x-public.page-hero>

    <section class="directory-section">
        <div class="public-shell">
            <form class="filter-bar filter-bar--wide" action="{{ route('campaigns.index') }}" method="GET">
                <label class="search-field">
                    <span class="sr-only">{{ __('public.labels.search_campaigns') }}</span>
                    <x-public.icon name="search" :size="19" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('public.labels.search_campaigns') }}">
                </label>
                <label>
                    <span class="sr-only">{{ __('public.labels.all_types') }}</span>
                    <select name="type">
                        <option value="">{{ __('public.labels.all_types') }}</option>
                        <option value="standard" @selected(request('type') === 'standard')>{{ __('public.labels.standard') }}</option>
                        <option value="emergency" @selected(request('type') === 'emergency')>{{ __('public.labels.emergency') }}</option>
                    </select>
                </label>
                <label>
                    <span class="sr-only">{{ __('public.labels.all_blood_groups') }}</span>
                    <select name="blood_group">
                        <option value="">{{ __('public.labels.all_blood_groups') }}</option>
                        @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodGroup)
                            <option value="{{ $bloodGroup }}" @selected(request('blood_group') === $bloodGroup)>{{ $bloodGroup }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="button button--primary" type="submit">{{ __('public.actions.search') }}</button>
                @if (request()->hasAny(['q', 'type', 'blood_group']))
                    <a class="button button--text" href="{{ route('campaigns.index') }}">{{ __('public.actions.clear') }}</a>
                @endif
            </form>

            <div class="directory-heading">
                <h2>{{ __('public.campaigns.directory_title') }}</h2>
                <span>{{ __('public.labels.results', ['count' => $campaigns->total()]) }}</span>
            </div>

            @if ($campaigns->isNotEmpty())
                <div class="campaign-grid">
                    @foreach ($campaigns as $campaign)
                        <x-public.campaign-card :campaign="$campaign" class="reveal-on-scroll" />
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $campaigns->links() }}</div>
            @else
                <x-public.empty-state :title="__('public.campaigns.empty_title')" :copy="__('public.campaigns.empty_copy')" icon="calendar-days">
                    <a class="button button--secondary" href="{{ route('campaigns.index') }}">{{ __('public.actions.clear') }}</a>
                    <a class="button button--primary" href="{{ route('centers.index') }}">{{ __('public.actions.find_center') }}</a>
                </x-public.empty-state>
            @endif
        </div>
    </section>
@endsection
