<?php

namespace App\Services;

use App\Repositories\PageRepository;
use Illuminate\Support\Str;

class PageService extends BaseService
{
    public function __construct(PageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): \App\Models\Page
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): \App\Models\Page
    {
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        return $this->repository->update($id, $data);
    }
}
