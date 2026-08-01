@props(['article'])

<article {{ $attributes->class('article-card') }}>
    <a class="article-card__media" href="{{ route('news.show', $article) }}" tabindex="-1" aria-hidden="true">
        <img src="{{ $article->image_path ? asset('storage/'.$article->image_path) : asset('images/public/laboratory-testing.png') }}" alt="" loading="lazy">
    </a>
    <div class="article-card__body">
        <div class="meta-row">
            <span>{{ $article->category ?: __('public.nav.news') }}</span>
            <span>{{ __('public.labels.reading_time', ['count' => $article->reading_time_minutes]) }}</span>
        </div>
        <h3><a href="{{ route('news.show', $article) }}">{{ $article->title }}</a></h3>
        <p>{{ str($article->summary ?: $article->body)->limit(150) }}</p>
        <a class="text-link" href="{{ route('news.show', $article) }}">
            {{ __('public.actions.read_more') }}
            <x-public.icon name="arrow-right" :size="17" />
        </a>
    </div>
</article>
