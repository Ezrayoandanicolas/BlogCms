<?php

namespace App\Repositories;

use App\Models\Post;

class PostRepository extends BaseRepository
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    private function _publishedScope($q)
    {
        return $q->where(function ($sq) {
            $sq->whereNull('published_at')
               ->orWhere('published_at', '<=', now());
        });
    }

    public function findPublished(array $columns = ['*'], array $relations = [])
    {
        return $this->model->where('status', 'published')
            ->where(function ($q) { $q->whereNull('published_at')->orWhere('published_at', '<=', now()); })
            ->with($relations)
            ->orderBy('published_at', 'desc')
            ->get($columns);
    }

    public function paginatePublished(int $perPage = 15)
    {
        return $this->model->where('status', 'published')
            ->where(function ($q) { $q->whereNull('published_at')->orWhere('published_at', '<=', now()); })
            ->with(['user', 'category', 'tags'])
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function findByCategory(string $slug, int $perPage = 15)
    {
        return $this->model->whereHas('category', function ($q) use ($slug) {
            $q->where('slug', $slug);
        })->where('status', 'published')
            ->where(function ($q) { $q->whereNull('published_at')->orWhere('published_at', '<=', now()); })
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function findByTag(string $slug, int $perPage = 15)
    {
        return $this->model->whereHas('tags', function ($q) use ($slug) {
            $q->where('slug', $slug);
        })->where('status', 'published')
            ->where(function ($q) { $q->whereNull('published_at')->orWhere('published_at', '<=', now()); })
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function search(string $query, int $perPage = 15)
    {
        return $this->model->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function incrementViews(int $id): void
    {
        $this->model->where('id', $id)->increment('views');
    }

    public function getPopular(int $limit = 5)
    {
        return $this->model->where('status', 'published')
            ->where(function ($q) { $q->whereNull('published_at')->orWhere('published_at', '<=', now()); })
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'views', 'published_at']);
    }

    public function getPrevPublished(string $publishedAt): ?Post
    {
        return $this->model->where('status', 'published')
            ->where('published_at', '<', $publishedAt)
            ->orderBy('published_at', 'desc')
            ->first(['id', 'title', 'slug']);
    }

    public function getNextPublished(string $publishedAt): ?Post
    {
        return $this->model->where('status', 'published')
            ->where('published_at', '>', $publishedAt)
            ->orderBy('published_at', 'asc')
            ->first(['id', 'title', 'slug']);
    }
}
