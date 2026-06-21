<?php

namespace App\Repositories;

use App\Models\Theme;

class ThemeRepository extends BaseRepository
{
    public function __construct(Theme $model)
    {
        parent::__construct($model);
    }

    public function getActive()
    {
        return $this->model->where('status', true)->first();
    }

    public function activate(int $id): Theme
    {
        $this->model->where('status', true)->update(['status' => false]);
        $theme = $this->findById($id);
        $theme->update(['status' => true]);
        return $theme;
    }
}
