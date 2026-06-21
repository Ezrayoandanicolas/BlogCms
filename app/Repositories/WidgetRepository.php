<?php

namespace App\Repositories;

use App\Models\Widget;

class WidgetRepository extends BaseRepository
{
    public function __construct(Widget $model)
    {
        parent::__construct($model);
    }
}
