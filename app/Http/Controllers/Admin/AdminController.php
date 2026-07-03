<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\DomainTheme;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Theme;
use App\Services\CommentService;
use App\Services\PostService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected CommentService $commentService
    ) {}

    public function login()
    {
        return admin_view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    public function dashboard()
    {
        $stats = [
            'posts' => Post::count(),
            'published' => Post::where('status', 'published')->count(),
            'drafts' => Post::where('status', 'draft')->count(),
            'categories' => Category::count(),
            'tags' => Tag::count(),
            'comments' => Comment::count(),
            'pending_comments' => Comment::pending()->count(),
        ];

        $counts = [
            'total' => Comment::count(),
            'pending' => Comment::pending()->count(),
            'approved' => Comment::approved()->count(),
            'rejected' => Comment::where('status', 'rejected')->count(),
            'spam' => Comment::where('status', 'spam')->count(),
        ];

        // Extra stats
        $totalViews = (int) Post::sum('views');
        $postsThisWeek = Post::where('status', 'published')
            ->where('published_at', '>=', now()->subDays(7))
            ->count();

        // Posts per day for last 30 days (for chart)
        $chartData = Post::where('status', 'published')
            ->where('published_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(published_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartValues[] = $chartData[$date] ?? 0;
        }

        // Per-domain stats
        $domainStats = DomainTheme::orderBy('domain')->get()->map(function ($d) {
            return [
                'domain' => $d->domain,
                'posts' => Post::withoutTenant()->where('domain_id', $d->id)->where('status', 'published')->count(),
                'views' => (int) Post::withoutTenant()->where('domain_id', $d->id)->sum('views'),
                'comments' => Comment::withoutTenant()->where('domain_id', $d->id)->count(),
            ];
        });

        return admin_view('dashboard', compact(
            'stats', 'counts', 'totalViews', 'postsThisWeek',
            'chartLabels', 'chartValues', 'domainStats'
        ));
    }

    public function posts(Request $request)
    {
        $posts = Post::with(['user', 'category'])->orderBy('created_at', 'desc')->paginate(20);
        return admin_view('posts.index', compact('posts'));
    }

    public function postCalendar()
    {
        $month = request('month', now()->month);
        $year = request('year', now()->year);
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $posts = Post::whereBetween('published_at', [$start, $end])
            ->orWhere(function ($q) use ($start, $end) {
                $q->where('status', 'published')->whereNull('published_at');
            })
            ->orderBy('published_at')
            ->get(['id', 'title', 'slug', 'status', 'published_at'])
            ->groupBy(fn($p) => $p->published_at?->format('Y-m-d') ?? 'no-date');

        $daysInMonth = $start->daysInMonth;
        $firstDayOfWeek = $start->dayOfWeek; // 0=Sun, 1=Mon, ...

        return admin_view('posts.calendar', compact(
            'posts', 'month', 'year', 'start', 'end',
            'daysInMonth', 'firstDayOfWeek'
        ));
    }

    public function postCreate()
    {
        $categories = Category::all();
        $tags = Tag::all();
        $siteTopic = Setting::withoutTenant()->where('key', 'site_topic')->first()?->value ?? '';
        return admin_view('posts.form', compact('categories', 'tags', 'siteTopic'));
    }

    public function postStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'nullable|max:255|unique:posts,slug',
            'content' => 'required',
            'excerpt' => 'nullable',
            'category_id' => 'nullable|exists:categories,id',
            'published_at' => 'nullable|date',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|url',
            'seo_title' => 'nullable|max:255',
            'seo_description' => 'nullable',
            'seo_keywords' => 'nullable',
        ]);

        $data['user_id'] = auth()->id();
        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']);
        if ($request->filled('published_at')) {
            $data['published_at'] = $request->published_at;
        } elseif ($request->status === 'published') {
            $data['published_at'] = now();
        }

        $post = Post::create($data);
        ActivityLog::log('post_created', "Post '{$post->title}' created", $post);
        return redirect('/admin/posts')->with('success', 'Post created.');
    }

    public function postEdit(int $id)
    {
        $post = Post::findOrFail($id);
        $categories = Category::all();
        $tags = Tag::all();
        $siteTopic = Setting::withoutTenant()->where('key', 'site_topic')->first()?->value ?? '';
        return admin_view('posts.form', compact('post', 'categories', 'tags', 'siteTopic'));
    }

    public function postUpdate(Request $request, int $id)
    {
        $post = Post::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'nullable|max:255|unique:posts,slug,' . $id,
            'content' => 'required',
            'excerpt' => 'nullable',
            'category_id' => 'nullable|exists:categories,id',
            'published_at' => 'nullable|date',
            'status' => 'required|in:draft,published',
            'featured_image' => 'nullable|url',
            'seo_title' => 'nullable|max:255',
            'seo_description' => 'nullable',
            'seo_keywords' => 'nullable',
        ]);

        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']);
        if ($request->filled('published_at')) {
            $data['published_at'] = $request->published_at;
        } elseif ($request->status === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        if ($request->status === 'draft') {
            $data['published_at'] = null;
        }

        $post->update($data);
        ActivityLog::log('post_updated', "Post '{$post->title}' updated", $post);
        return redirect('/admin/posts')->with('success', 'Post updated.');
    }

    public function postDelete(int $id)
    {
        $post = Post::findOrFail($id);
        ActivityLog::log('post_deleted', "Post '{$post->title}' deleted", $post);
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }

    public function postBulk(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids) || !$action) {
            return back()->with('error', 'Select posts and an action.');
        }

        $posts = Post::whereIn('id', $ids);

        match ($action) {
            'publish' => $posts->update(['status' => 'published', 'published_at' => now()]),
            'draft' => $posts->update(['status' => 'draft']),
            'delete' => $posts->delete(),
            default => null,
        };

        return back()->with('success', count($ids) . ' posts ' . $action . 'ed.');
    }

    public function comments(Request $request)
    {
        $status = $request->get('status');
        $comments = $status
            ? $this->commentService->paginateByStatus($status)
            : $this->commentService->paginateAll();
        $counts = $this->commentService->counts();
        return admin_view('comments.index', compact('comments', 'counts', 'status'));
    }

    public function commentApprove(int $id)
    {
        $this->commentService->approve($id);
        return back()->with('success', 'Comment approved.');
    }

    public function commentReject(int $id)
    {
        $this->commentService->reject($id);
        return back()->with('success', 'Comment rejected.');
    }

    public function commentDelete(int $id)
    {
        $this->commentService->delete($id);
        return back()->with('success', 'Comment deleted.');
    }

    public function categories()
    {
        $categories = Category::withCount('posts')->orderBy('name')->paginate(20);
        return admin_view('categories.index', compact('categories'));
    }

    public function categoryStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255|unique:categories,name',
            'slug' => 'nullable|max:255|unique:categories,slug',
            'description' => 'nullable',
        ]);

        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);
        $category = Category::create($data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['id' => $category->id, 'name' => $category->name]);
        }

        return redirect('/admin/categories')->with('success', "Category '{$category->name}' created");
    }

    public function categoryUpdate(Request $request, int $id)
    {
        $cat = Category::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $id,
            'slug' => 'nullable|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable',
        ]);
        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);
        $cat->update($data);
        return back()->with('success', 'Category updated.');
    }

    public function categoryDelete(int $id)
    {
        Category::findOrFail($id)->delete();
        return back()->with('success', 'Category deleted.');
    }

    public function tags()
    {
        $tags = Tag::withCount('posts')->orderBy('name')->paginate(20);
        return admin_view('tags.index', compact('tags'));
    }

    public function tagStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255|unique:tags,name',
            'slug' => 'nullable|max:255|unique:tags,slug',
        ]);
        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);
        Tag::create($data);
        return back()->with('success', 'Tag created.');
    }

    public function tagUpdate(Request $request, int $id)
    {
        $tag = Tag::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|max:255|unique:tags,name,' . $id,
            'slug' => 'nullable|max:255|unique:tags,slug,' . $id,
        ]);
        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);
        $tag->update($data);
        return back()->with('success', 'Tag updated.');
    }

    public function tagDelete(int $id)
    {
        Tag::findOrFail($id)->delete();
        return back()->with('success', 'Tag deleted.');
    }

    public function settings()
    {
        $domainThemes = DomainTheme::orderBy('domain')->get();
        $themes = Theme::all();
        $siteSettings = [
            'site_name' => Setting::where('key', 'site_name')->first()?->value ?? config('app.name'),
            'site_description' => Setting::where('key', 'site_description')->first()?->value ?? '',
            'site_topic' => Setting::where('key', 'site_topic')->first()?->value ?? '',
        ];
        return admin_view('settings', compact('domainThemes', 'themes', 'siteSettings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'site_name' => 'required|max:255',
            'site_description' => 'nullable|max:500',
            'site_topic' => 'nullable|max:255',
        ]);

        foreach (['site_name', 'site_description', 'site_topic'] as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $data[$key] ?? '']
            );
        }

        return redirect('/admin/settings')->with('success', 'Site settings updated.');
    }

    public function updateDomainSettings(Request $request, int $id)
    {
        $data = $request->validate([
            'site_name' => 'required|max:255',
            'site_description' => 'nullable|max:500',
            'site_topic' => 'nullable|max:255',
        ]);

        foreach (['site_name', 'site_description', 'site_topic'] as $key) {
            Setting::withoutTenant()->updateOrCreate(
                ['key' => $key, 'domain_id' => $id],
                ['value' => $data[$key] ?? '']
            );
        }

        return back()->with('success', 'Domain settings updated.');
    }

    public function getDomainSettings(int $id)
    {
        $siteName = Setting::withoutTenant()->where('key', 'site_name')->where('domain_id', $id)->first()?->value ?? config('app.name');
        $siteDescription = Setting::withoutTenant()->where('key', 'site_description')->where('domain_id', $id)->first()?->value ?? '';
        $siteTopic = Setting::withoutTenant()->where('key', 'site_topic')->where('domain_id', $id)->first()?->value ?? '';

        return response()->json([
            'site_name' => $siteName,
            'site_description' => $siteDescription,
            'site_topic' => $siteTopic,
        ]);
    }

    public function domainThemeStore(Request $request)
    {
        $data = $request->validate([
            'domain' => 'required|max:255|unique:domain_themes,domain',
            'theme_slug' => 'required|exists:themes,slug',
        ]);
        DomainTheme::create($data + ['active' => true]);
        return back()->with('success', 'Domain mapping created.');
    }

    public function domainThemeUpdate(Request $request, int $id)
    {
        $mapping = DomainTheme::findOrFail($id);
        $data = $request->validate([
            'domain' => 'required|max:255|unique:domain_themes,domain,' . $id,
            'theme_slug' => 'required|exists:themes,slug',
            'active' => 'boolean',
        ]);
        $mapping->update($data);
        return back()->with('success', 'Domain mapping updated.');
    }

    public function domainThemeDelete(int $id)
    {
        DomainTheme::findOrFail($id)->delete();
        return back()->with('success', 'Domain mapping deleted.');
    }

    public function switchDomain(Request $request, int $id = 0)
    {
        if (!$id) {
            $id = $request->input('domain_id', 0);
        }
        if (!$id) {
            return redirect('/admin');
        }
        $domain = DomainTheme::findOrFail($id);
        session(['admin_domain_id' => $domain->id]);
        $redirect = $request->input('redirect', '/admin');
        return redirect($redirect)->with('success', "Switched to {$domain->domain}");
    }

    public function media()
    {
        $query = Media::orderBy('created_at', 'desc');
        if ($domainId = config('app.domain_id')) {
            $query->where('domain_id', $domainId);
        }
        $media = $query->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'filename' => $m->filename,
                'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($m->path),
                'size' => $m->size,
                'mime' => $m->mime_type,
            ];
        });
        return admin_view('media', compact('media'));
    }

    public function mediaUpload(Request $request)
    {
        return app(\App\Http\Controllers\Api\V1\MediaController::class)->upload($request);
    }

    public function mediaDelete(int $id, Request $request)
    {
        $request->merge(['id' => $id]);
        return app(\App\Http\Controllers\Api\V1\MediaController::class)->delete($request);
    }
}
