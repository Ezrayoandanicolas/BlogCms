<?php

use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CommentController;
use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{slug}', [BlogController::class, 'show']);
Route::post('/blog/{slug}/comments', [CommentController::class, 'store'])->name('comments.store');
Route::get('/category/{slug}', [BlogController::class, 'category']);
Route::get('/tag/{slug}', [BlogController::class, 'tag']);
Route::get('/author/{name}', [BlogController::class, 'author']);
Route::get('/search', [BlogController::class, 'search']);
Route::get('/about', [\App\Http\Controllers\PageController::class, 'about']);
Route::get('/contact', [\App\Http\Controllers\PageController::class, 'contact']);
Route::post('/contact', [\App\Http\Controllers\PageController::class, 'contactSend']);
Route::post('/newsletter', [\App\Http\Controllers\PageController::class, 'newsletter']);
Route::get('/archive/{year}/{month?}', [BlogController::class, 'archive']);
Route::get('/feed', [BlogController::class, 'feed']);

Route::get('/robots.txt', function () {
    $disallow = '';
    if (app()->environment('local')) {
        $disallow = "Disallow: /";
    }

    return response("User-agent: *\n{$disallow}\nAllow: /\nSitemap: " . url('/sitemap.xml') . "\n")
        ->header('Content-Type', 'text/plain');
});

Route::get('/sitemap.xml', function () {
    $domainId = config('app.domain_id');
    $posts = \App\Models\Post::where('status', 'published')
        ->where(function ($q) { $q->whereNull('published_at')->orWhere('published_at', '<=', now()); })
        ->when($domainId, fn($q) => $q->where('domain_id', $domainId))
        ->orderBy('published_at', 'desc')
        ->get(['slug', 'updated_at']);

    $domain = url('/');
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $xml .= "<url><loc>{$domain}</loc><priority>1.0</priority></url>";
    $xml .= "<url><loc>{$domain}/blog</loc><priority>0.8</priority></url>";
    foreach ($posts as $post) {
        $xml .= "<url><loc>{$domain}/blog/{$post->slug}</loc><lastmod>{$post->updated_at->format('Y-m-d')}</lastmod><priority>0.6</priority></url>";
    }
    $xml .= '</urlset>';
    return response($xml)->header('Content-Type', 'application/xml');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\AdminController::class, 'login'])->name('admin.login');
    Route::post('/login', [\App\Http\Controllers\Admin\AdminController::class, 'authenticate']);
    Route::post('/logout', [\App\Http\Controllers\Admin\AdminController::class, 'logout'])->name('admin.logout');

    Route::middleware(['admin', 'admin.domain'])->group(function () {
        Route::match(['GET', 'POST'], '/switch-domain/{id?}', [\App\Http\Controllers\Admin\AdminController::class, 'switchDomain'])->name('admin.switch-domain');
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/posts', [\App\Http\Controllers\Admin\AdminController::class, 'posts'])->name('admin.posts');
        Route::post('/posts/bulk', [\App\Http\Controllers\Admin\AdminController::class, 'postBulk'])->name('admin.posts.bulk');
        Route::get('/posts/calendar', [\App\Http\Controllers\Admin\AdminController::class, 'postCalendar'])->name('admin.posts.calendar');
        Route::get('/posts/create', [\App\Http\Controllers\Admin\AdminController::class, 'postCreate'])->name('admin.posts.create');
        Route::post('/posts', [\App\Http\Controllers\Admin\AdminController::class, 'postStore']);
        Route::get('/posts/{id}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'postEdit'])->name('admin.posts.edit');
        Route::put('/posts/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'postUpdate']);
        Route::delete('/posts/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'postDelete']);
        Route::get('/comments', [\App\Http\Controllers\Admin\AdminController::class, 'comments'])->name('admin.comments');
        Route::put('/comments/{id}/approve', [\App\Http\Controllers\Admin\AdminController::class, 'commentApprove']);
        Route::put('/comments/{id}/reject', [\App\Http\Controllers\Admin\AdminController::class, 'commentReject']);
        Route::delete('/comments/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'commentDelete']);
        Route::get('/categories', [\App\Http\Controllers\Admin\AdminController::class, 'categories'])->name('admin.categories');
        Route::post('/categories', [\App\Http\Controllers\Admin\AdminController::class, 'categoryStore']);
        Route::put('/categories/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'categoryUpdate']);
        Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'categoryDelete']);
        Route::get('/tags', [\App\Http\Controllers\Admin\AdminController::class, 'tags'])->name('admin.tags');
        Route::post('/tags', [\App\Http\Controllers\Admin\AdminController::class, 'tagStore']);
        Route::put('/tags/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'tagUpdate']);
        Route::delete('/tags/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'tagDelete']);
        Route::get('/media', [\App\Http\Controllers\Admin\AdminController::class, 'media'])->name('admin.media');
        Route::post('/media/upload', [\App\Http\Controllers\Admin\AdminController::class, 'mediaUpload']);
        Route::delete('/media/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'mediaDelete']);
        Route::get('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::post('/settings/domain/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'updateDomainSettings'])->name('admin.settings.domain');
        Route::get('/settings/domain/{id}/data', [\App\Http\Controllers\Admin\AdminController::class, 'getDomainSettings']);
        Route::get('/api-docs', [\App\Http\Controllers\Admin\ApiDocsController::class, 'index'])->name('admin.api-docs');
        Route::post('/api-docs/regenerate', [\App\Http\Controllers\Admin\ApiDocsController::class, 'regenerateToken']);
        Route::post('/domain-themes', [\App\Http\Controllers\Admin\AdminController::class, 'domainThemeStore']);
        Route::put('/domain-themes/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'domainThemeUpdate']);
        Route::delete('/domain-themes/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'domainThemeDelete']);
    });
});

Route::get('/sitemap-{type}.xml', function ($type) {
    $path = public_path("storage/sitemap-{$type}.xml");
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'application/xml']);
    }
    abort(404);
});
