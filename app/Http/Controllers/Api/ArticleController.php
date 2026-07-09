<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('status', 'published')
            ->published()
            ->orderedForPublic()
            ->get();

        return ArticleResource::collection($articles);
    }

    public function show($id)
    {
        $article = Article::published()->find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return new ArticleResource($article);
    }
}
