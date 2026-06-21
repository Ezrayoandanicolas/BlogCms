<?php

namespace App\Services;

use App\Repositories\ThemeRepository;

class ThemeService extends BaseService
{
    public function __construct(ThemeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getActive()
    {
        return $this->repository->getActive();
    }

    public function activate(int $id): \App\Models\Theme
    {
        return $this->repository->activate($id);
    }
}
