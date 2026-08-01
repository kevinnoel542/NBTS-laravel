<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ArticleIndexRequest;
use App\Http\Resources\Api\V1\PublicationResource;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PublicationController extends Controller
{
    public function index(ArticleIndexRequest $request): AnonymousResourceCollection
    {
        $query = Article::query()
            ->published()
            ->whereNotNull('attachment_path')
            ->where('attachment_path', '!=', '');

        if (($search = $request->search()) !== null) {
            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('attachment_name', 'like', "%{$search}%");
            });
        }

        if (($category = $request->category()) !== null) {
            $query->where('category', $category);
        }

        if (($featured = $request->featured()) !== null) {
            $query->where('is_featured', $featured);
        }

        return PublicationResource::collection(
            $query->orderedForPublic()
                ->paginate($request->perPage())
                ->withQueryString(),
        );
    }

    public function show(Article $article): PublicationResource
    {
        abort_unless(
            $article->isPubliclyVisible()
            && $article->attachment_path !== null
            && $article->attachment_path !== '',
            404,
        );

        return new PublicationResource($article);
    }
}
