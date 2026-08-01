<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->string('author_name')->nullable()->after('body');
            $table->string('source_name')->nullable()->after('author_name');
            $table->string('source_url')->nullable()->after('source_name');
            $table->string('attachment_path')->nullable()->after('image_path');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime')->nullable()->after('attachment_name');
            $table->boolean('is_featured')->default(false)->after('attachment_mime');
            $table->unsignedSmallInteger('reading_time_minutes')->default(1)->after('is_featured');
            $table->text('meta_description')->nullable()->after('reading_time_minutes');
        });

        $usedSlugs = [];

        DB::table('articles')
            ->orderBy('id')
            ->get(['id', 'title', 'slug', 'body', 'summary'])
            ->each(function (object $article) use (&$usedSlugs): void {
                $base = Str::slug($article->slug ?: $article->title) ?: 'news-' . $article->id;
                $slug = $base;
                $suffix = 2;

                while (in_array($slug, $usedSlugs, true)) {
                    $slug = $base . '-' . $suffix;
                    $suffix++;
                }

                $usedSlugs[] = $slug;
                $plainBody = trim(strip_tags((string) ($article->body ?: $article->summary)));
                $readingTime = max(1, (int) ceil(str_word_count($plainBody) / 220));

                DB::table('articles')
                    ->where('id', $article->id)
                    ->update([
                        'slug' => $slug,
                        'reading_time_minutes' => $readingTime,
                    ]);
            });

        Schema::table('articles', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'author_name',
                'source_name',
                'source_url',
                'attachment_path',
                'attachment_name',
                'attachment_mime',
                'is_featured',
                'reading_time_minutes',
                'meta_description',
            ]);
        });
    }
};
