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
