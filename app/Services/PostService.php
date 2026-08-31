<?php

namespace App\Services;

use App\Repositories\PostRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    protected CategoryRepository $categoryRepository;
    protected TagRepository $tagRepository;

    public function __construct(
        PostRepository $repository,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository
    ) {
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
    }

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginatePublished($perPage);
    }

    public function findByCategory(string $slug, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByCategory($slug, $perPage);
    }

    public function findByTag(string $slug, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findByTag($slug, $perPage);
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($query, $perPage);
    }

    public function getPopular(int $limit = 5): Collection
    {
        return $this->repository->getPopular($limit);
    }

    public function createWithTags(array $data, array $tags = []): \App\Models\Post
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        $slug = $data['slug'];
        $counter = 1;
        while (\App\Models\Post::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $data['slug'] . '-' . $counter;
            $counter++;
        }
        $data['slug'] = $slug;

        if (!isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        if (($data['status'] ?? '') === 'published' && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = $this->repository->create($data);

        if (!empty($tags)) {
            $tagIds = $this->resolveTagIds($tags);
            $post->tags()->sync($tagIds);
        }

        return $post;
    }

    public function updateWithTags(int $id, array $data, array $tags = []): \App\Models\Post
    {
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $post = $this->repository->update($id, $data);

        if (!empty($tags)) {
            $tagIds = $this->resolveTagIds($tags);
            $post->tags()->sync($tagIds);
        }

        return $post;
    }

    protected function resolveTagIds(array $tags): array
    {
        return array_map(function ($tag) {
            if (is_numeric($tag)) {
                return (int) $tag;
            }
            $slug = Str::slug($tag);
            $existing = \App\Models\Tag::withoutGlobalScopes()->where('slug', $slug)->first();
            if ($existing) {
                return $existing->id;
            }
            $finalSlug = $slug;
            $counter = 1;
            while (\App\Models\Tag::withoutGlobalScopes()->where('slug', $finalSlug)->exists()) {
                $finalSlug = $slug . '-' . $counter;
                $counter++;
            }
            return $this->tagRepository->create([
                'name' => $tag,
                'slug' => $finalSlug,
            ])->id;
        }, $tags);
    }

    public function incrementViews(int $id): void
    {
        $this->repository->incrementViews($id);
    }

    public function getPrevPublished(?string $publishedAt)
    {
        if (!$publishedAt) return null;
        return $this->repository->getPrevPublished($publishedAt);
    }

    public function getNextPublished(?string $publishedAt)
    {
        if (!$publishedAt) return null;
        return $this->repository->getNextPublished($publishedAt);
    }

    public function syncExternal(array $data): \App\Models\Post
    {
        $externalPost = \App\Models\ExternalPost::where('source', $data['source'])
            ->where('external_id', $data['external_id'])
            ->first();

        $postData = [
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'content' => $data['content'] ?? '',
            'excerpt' => $data['excerpt'] ?? '',
            'status' => $data['status'] ?? 'draft',
            'user_id' => $data['user_id'] ?? auth()->id(),
            'category_id' => $data['category_id'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
        ];

        if ($externalPost) {
            $post = $this->repository->update($externalPost->post_id, $postData);
            $externalPost->update(['last_sync' => now()]);
        } else {
            $post = $this->repository->create($postData);
            \App\Models\ExternalPost::create([
                'source' => $data['source'],
                'external_id' => $data['external_id'],
                'post_id' => $post->id,
                'last_sync' => now(),
            ]);
        }

        return $post;
    }
}
