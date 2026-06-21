<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Services\CommentService;
use App\Services\PostService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected CommentService $commentService
    ) {}

    public function index()
    {
        $posts = $this->postService->paginatePublished(12);

        if (request()->wantsJson()) {
            return response()->json($posts);
        }

        return theme_view('pages.home', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = $this->postService->findBySlug($slug, ['*'], ['user', 'category', 'tags']);
        $this->postService->incrementViews($post->id);

        $prevPost = $post->published_at ? $this->postService->getPrevPublished($post->published_at->toDateTimeString()) : null;
        $nextPost = $post->published_at ? $this->postService->getNextPublished($post->published_at->toDateTimeString()) : null;
        $popularPosts = $this->postService->getPopular(5);
        $relatedPosts = $post->category
            ? Post::where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->limit(4)
                ->get()
            : collect();
        $comments = $this->commentService->getApprovedForPost($post->id);
        $minutes = $this->readingTime($post->content);

        return theme_view('pages.single', compact(
            'post', 'prevPost', 'nextPost', 'popularPosts', 'relatedPosts', 'comments', 'minutes'
        ));
    }

    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $posts = $this->postService->findByCategory($slug, 12);

        return theme_view('pages.category', compact('category', 'posts'));
    }

    public function tag(string $slug)
    {
        $posts = $this->postService->findByTag($slug, 12);

        return theme_view('pages.home', compact('posts'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $posts = $query ? $this->postService->search($query, 10) : collect();

        return theme_view('pages.search', compact('posts', 'query'));
    }

    public function author(string $name)
    {
        $posts = Post::whereHas('user', function ($q) use ($name) {
            $q->where('name', $name);
        })->where('status', 'published')->orderBy('published_at', 'desc')->paginate(10);

        $authorName = $name;

        return theme_view('pages.home', compact('posts', 'authorName'));
    }

    public function archive(string $year, ?string $month = null)
    {
        $posts = Post::where('status', 'published')
            ->whereYear('published_at', $year)
            ->when($month, function ($q) use ($month) {
                $q->whereMonth('published_at', $month);
            })
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        $archiveLabel = $month ? "$year/" . str_pad($month, 2, '0', STR_PAD_LEFT) : $year;

        return theme_view('pages.home', compact('posts', 'archiveLabel'));
    }

    public function feed()
    {
        $posts = Post::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(50)
            ->get();

        $content = theme_view('pages.rss', compact('posts'))->render();

        return response($content)
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    private function readingTime(?string $content): int
    {
        if (!$content) return 0;
        $words = str_word_count(strip_tags($content));
        return max(1, (int) ceil($words / 200));
    }
}
