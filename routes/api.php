<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BacklinkController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController as AdminCommentController;
use App\Http\Controllers\Api\V1\GuestPostController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\ThemeController;
use App\Http\Controllers\Api\V1\DomainController;
use App\Http\Controllers\Api\V1\ToolsController;
use App\Http\Controllers\Api\V1\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API First Architecture - All features available via API
| Version: v1
|
*/

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Public posts
    Route::get('posts', [PostController::class, 'published']);
    Route::get('posts/popular', [PostController::class, 'popular']);
    Route::get('posts/category/{slug}', [PostController::class, 'byCategory']);
    Route::get('posts/tag/{slug}', [PostController::class, 'byTag']);
    Route::get('posts/search', [PostController::class, 'search']);
    Route::get('posts/{slug}', [PostController::class, 'showBySlug']);

    // Public categories & tags
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{id}', [TagController::class, 'show']);

    // Public pages
    Route::get('pages', [PageController::class, 'index']);
    Route::get('pages/{id}', [PageController::class, 'show']);

    // Public theme
    Route::get('themes', [ThemeController::class, 'index']);
    Route::get('theme-settings', [ThemeController::class, 'settings']);

    // Public site settings (topic, name, desc for automation)
    Route::get('settings', [\App\Http\Controllers\Api\V1\SettingController::class, 'index']);

    // Public domains list (for automation)
    Route::get('domains', [DomainController::class, 'index']);

    // Public OG image
    Route::get('og-image', [\App\Http\Controllers\Api\V1\OGImageController::class, 'generate']);

    // API Key protected routes (domain management)
    Route::middleware('api.key')->group(function () {
        Route::post('domains', [DomainController::class, 'store']);
        Route::put('domains/{id}', [DomainController::class, 'update']);
        Route::delete('domains/{id}', [DomainController::class, 'destroy']);
        Route::put('settings', [\App\Http\Controllers\Api\V1\SettingController::class, 'update']);
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Posts - Full CRUD
        Route::post('posts', [PostController::class, 'store']);
        Route::put('posts/{id}', [PostController::class, 'update']);
        Route::delete('posts/{id}', [PostController::class, 'destroy']);
        Route::post('posts/bulk', [PostController::class, 'bulkStore']);
        Route::post('posts/sync', [PostController::class, 'sync']);
        Route::post('posts/sync-bulk', [PostController::class, 'bulkSync']);

        // Categories
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

        // Tags
        Route::post('tags', [TagController::class, 'store']);
        Route::put('tags/{id}', [TagController::class, 'update']);
        Route::delete('tags/{id}', [TagController::class, 'destroy']);

        // Pages
        Route::post('pages', [PageController::class, 'store']);
        Route::put('pages/{id}', [PageController::class, 'update']);
        Route::delete('pages/{id}', [PageController::class, 'destroy']);

        // Theme
        Route::post('themes/activate', [ThemeController::class, 'activate']);
        Route::put('theme-settings', [ThemeController::class, 'updateSettings']);

        // Widgets
        Route::get('widgets', [WidgetController::class, 'index']);
        Route::post('widgets', [WidgetController::class, 'store']);
        Route::put('widgets/{id}', [WidgetController::class, 'update']);
        Route::delete('widgets/{id}', [WidgetController::class, 'destroy']);

        // Media
        Route::get('media', [MediaController::class, 'index']);
        Route::post('media/upload', [MediaController::class, 'upload']);
        Route::delete('media/{id}', [MediaController::class, 'delete']);

        // Backlinks
        Route::get('backlinks', [BacklinkController::class, 'index']);
        Route::post('backlinks', [BacklinkController::class, 'store']);
        Route::put('backlinks/{id}', [BacklinkController::class, 'update']);
        Route::delete('backlinks/{id}', [BacklinkController::class, 'destroy']);
        Route::get('backlinks/sites', [BacklinkController::class, 'sites']);
        Route::post('backlinks/sites', [BacklinkController::class, 'storeSite']);
        Route::post('backlinks/{id}/check', [BacklinkController::class, 'check']);

        // Guest Post Orders
        Route::get('guest-posts', [GuestPostController::class, 'index']);
        Route::post('guest-posts', [GuestPostController::class, 'store']);
        Route::put('guest-posts/{id}', [GuestPostController::class, 'update']);
        Route::delete('guest-posts/{id}', [GuestPostController::class, 'destroy']);

        // Comments
        Route::get('comments', [AdminCommentController::class, 'index']);
        Route::get('comments/counts', [AdminCommentController::class, 'counts']);
        Route::get('comments/{id}', [AdminCommentController::class, 'show']);
        Route::put('comments/{id}/approve', [AdminCommentController::class, 'approve']);
        Route::put('comments/{id}/reject', [AdminCommentController::class, 'reject']);
        Route::delete('comments/{id}', [AdminCommentController::class, 'destroy']);

        // Tools
        Route::post('tools/generate-sitemap', [ToolsController::class, 'generateSitemap']);
        Route::post('tools/clear-cache', [ToolsController::class, 'clearCache']);
        Route::post('tools/rebuild-seo', [ToolsController::class, 'rebuildSeo']);
        Route::post('tools/rebuild-search', [ToolsController::class, 'rebuildSearch']);
    });
});
