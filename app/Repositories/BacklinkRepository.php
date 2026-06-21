<?php

namespace App\Repositories;

use App\Models\Backlink;

class BacklinkRepository extends BaseRepository
{
    public function __construct(Backlink $model)
    {
        parent::__construct($model);
    }

    public function getActive()
    {
        return $this->model->where('status', 'active')->with(['site', 'post'])->get();
    }

    public function getBySite(int $siteId)
    {
        return $this->model->where('site_id', $siteId)->with('post')->get();
    }
}
