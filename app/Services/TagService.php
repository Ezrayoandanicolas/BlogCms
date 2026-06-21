<?php

namespace App\Services;

use App\Repositories\TagRepository;
use Illuminate\Support\Str;

class TagService extends BaseService
{
    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): \App\Models\Tag
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): \App\Models\Tag
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
