<?php

namespace App\Services;

use App\Repositories\BacklinkRepository;

class BacklinkService extends BaseService
{
    public function __construct(BacklinkRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getActive()
    {
        return $this->repository->getActive();
    }

    public function getBySite(int $siteId)
    {
        return $this->repository->getBySite($siteId);
    }
}
