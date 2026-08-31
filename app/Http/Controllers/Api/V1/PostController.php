<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $posts = $this->postService->paginate($perPage);

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function published(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $posts = $this->postService->paginatePublished($perPage);

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $post = $this->postService->findById((int) $id, ['*'], ['user', 'category', 'tags']);

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    public function showBySlug($slug): JsonResponse
    {
        $post = $this->postService->findBySlug($slug, ['*'], ['user', 'category', 'tags']);

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
            'domain_id' => 'nullable|integer',
        ]);

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        if (!isset($validated['user_id'])) {
            $validated['user_id'] = $request->user()->id;
        }

        if (isset($validated['domain_id'])) {
            config(['app.domain_id' => $validated['domain_id']]);
            unset($validated['domain_id']);
        }

        $post = $this->postService->createWithTags($validated, $tags);

        return response()->json([
            'message' => 'Post created',
            'data' => new PostResource($post),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|in:draft,published,archived',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $post = $this->postService->updateWithTags((int) $id, $validated, $tags);

        return response()->json([
            'message' => 'Post updated',
            'data' => new PostResource($post),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->postService->delete((int) $id);

        return response()->json([
            'message' => 'Post deleted',
        ]);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'posts' => 'required|array',
            'posts.*.title' => 'required|string|max:255',
            'posts.*.content' => 'nullable|string',
            'posts.*.status' => 'nullable|in:draft,published,archived',
        ]);

        $posts = [];
        foreach ($validated['posts'] as $data) {
            $posts[] = $this->postService->createWithTags($data);
        }

        return response()->json([
            'message' => 'Posts created',
            'data' => PostResource::collection(collect($posts)),
        ], 201);
    }

    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'required|string',
            'external_id' => 'required|string',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'status' => 'nullable|in:draft,published,archived',
            'category_id' => 'nullable|exists:categories,id',
            'featured_image' => 'nullable|string',
        ]);

        $post = $this->postService->syncExternal($validated);

        return response()->json([
            'message' => 'Post synced',
            'data' => new PostResource($post),
        ]);
    }

    public function bulkSync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'posts' => 'required|array',
            'posts.*.source' => 'required|string',
            'posts.*.external_id' => 'required|string',
            'posts.*.title' => 'required|string|max:255',
        ]);

        $results = [];
        foreach ($validated['posts'] as $data) {
            $results[] = $this->postService->syncExternal($data);
        }

        return response()->json([
            'message' => 'Bulk sync completed',
            'data' => PostResource::collection(collect($results)),
        ]);
    }

    public function byCategory($slug, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $posts = $this->postService->findByCategory($slug, $perPage);

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function byTag($slug, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $posts = $this->postService->findByTag($slug, $perPage);

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string']);
        $perPage = $request->get('per_page', 15);
        $posts = $this->postService->search($request->get('q'), $perPage);

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function popular(): JsonResponse
    {
        $limit = request()->get('limit', 5);
        $posts = $this->postService->getPopular((int) $limit);

        return response()->json([
            'data' => PostResource::collection($posts),
        ]);
    }
}
