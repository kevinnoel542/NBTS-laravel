<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ArticleIndexRequest;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

final class ArticleDirectoryController extends Controller
{
    public function index(ArticleIndexRequest $request): View
    {
        $categories = Article::query()
            ->published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $articles = Article::query()
            ->published()
            ->when($request->search(), function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('summary', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%');
                });
            })
            ->when($request->category(), fn (Builder $query, string $category): Builder => $query->where('category', $category))
            ->orderedForPublic()
            ->paginate(9)
            ->withQueryString();

        return view('web.news.index', compact('articles', 'categories'));
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPubliclyVisible(), 404);

        $relatedArticles = Article::query()
            ->published()
            ->whereKeyNot($article->id)
            ->when($article->category, fn (Builder $query, string $category): Builder => $query->where('category', $category))
            ->orderedForPublic()
            ->limit(3)
            ->get();

        return view('web.news.show', compact('article', 'relatedArticles'));
    }

    public function publications(): View
    {
        $publications = Article::query()
            ->published()
            ->whereNotNull('attachment_path')
            ->orderedForPublic()
            ->paginate(12);

        return view('web.publications', compact('publications'));
    }
}
