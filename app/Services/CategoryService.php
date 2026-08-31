<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): \App\Models\Category
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $domainId = config('app.domain_id', 0);

        $existing = \App\Models\Category::withoutGlobalScopes()
            ->where('name', $data['name'])
            ->where('domain_id', $domainId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $slug = $data['slug'];
        $counter = 1;
        while (\App\Models\Category::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $data['slug'] . '-' . $counter;
            $counter++;
        }
        $data['slug'] = $slug;

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): \App\Models\Category
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $this->repository->update($id, $data);
    }

    public function getWithPostCount()
    {
        return $this->repository->getWithPostCount();
    }
}
