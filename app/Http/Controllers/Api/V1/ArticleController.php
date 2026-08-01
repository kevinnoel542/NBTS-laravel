<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ArticleIndexRequest;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ArticleController extends Controller
{
    public function index(ArticleIndexRequest $request): AnonymousResourceCollection
    {
        $query = Article::query()->published();

        if (($search = $request->search()) !== null) {
            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (($category = $request->category()) !== null) {
            $query->where('category', $category);
        }

        if (($featured = $request->featured()) !== null) {
            $query->where('is_featured', $featured);
        }

        return ArticleResource::collection(
            $query->orderedForPublic()
                ->paginate($request->perPage())
                ->withQueryString(),
        );
    }

    public function show(Article $article): ArticleResource
    {
        abort_unless($article->isPubliclyVisible(), 404);

        return new ArticleResource($article);
    }
}
