<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BloodCenter;
use App\Models\Campaign;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function donate()
    {
        return view('web.donate');
    }

    public function services()
    {
        return view('web.services');
    }

    public function news()
    {
        $query = Article::published();

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($articleQuery) use ($search) {
                $articleQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('summary', 'like', '%' . $search . '%')
                    ->orWhere('body', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }

        $categories = Article::published()
            ->selectRaw('category, count(*) as total')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $articleStats = [
            'total' => Article::published()->count(),
            'categories' => $categories->count(),
            'attachments' => Article::published()->whereNotNull('attachment_path')->count(),
            'featured' => Article::published()->where('is_featured', true)->count(),
        ];

        $featuredArticle = Article::published()
            ->where('is_featured', true)
            ->orderedForPublic()
            ->first() ?: Article::published()->orderedForPublic()->first();

        $articles = $query
            ->orderedForPublic()
            ->paginate(8)
            ->withQueryString();

        $campaigns = Campaign::with('bloodCenter')
            ->whereIn('status', ['ongoing', 'upcoming'])
            ->latest('start_date')
            ->take(3)
            ->get();

        return view('web.news', compact('articles', 'campaigns', 'categories', 'featuredArticle', 'articleStats'));
    }

    public function newsShow(Article $article)
    {
        abort_unless($article->isPubliclyVisible(), 404);

        $relatedArticles = Article::published()
            ->whereKeyNot($article->id)
            ->when($article->category, fn ($query) => $query->where('category', $article->category))
            ->orderedForPublic()
            ->take(3)
            ->get();

        if ($relatedArticles->count() < 3) {
            $extraArticles = Article::published()
                ->whereKeyNot($article->id)
                ->whereNotIn('id', $relatedArticles->pluck('id'))
                ->orderedForPublic()
                ->take(3 - $relatedArticles->count())
                ->get();

            $relatedArticles = $relatedArticles->merge($extraArticles);
        }

        return view('web.news-show', compact('article', 'relatedArticles'));
    }

    public function publications()
    {
        return view('web.publications');
    }

    public function faq()
    {
        return view('web.faq');
    }

    public function contact()
    {
        $centers = BloodCenter::where('is_active', true)
            ->latest()
            ->take(6)
            ->get();

        return view('web.contact', compact('centers'));
    }
}
