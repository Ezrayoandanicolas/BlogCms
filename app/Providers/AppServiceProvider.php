<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::addNamespace('theme', resource_path('themes'));

        $sharedWidgets = function ($view) {
            $view->with('categories', Category::withCount('posts')->orderBy('name')->get());
            $view->with('tags', Tag::withCount('posts')->orderBy('name')->get());
            $view->with('recentPosts', Post::where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'published_at'])
            );
            $view->with('popularPosts', Post::where('status', 'published')
                ->orderBy('views', 'desc')
                ->limit(5)
                ->get(['id', 'title', 'slug', 'views'])
            );
        };

        View::composer('theme::*.pages.home', $sharedWidgets);
        View::composer('theme::*.pages.single', $sharedWidgets);
        View::composer('theme::*.pages.category', $sharedWidgets);
        View::composer('theme::*.pages.search', $sharedWidgets);
    }
}
