<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\InformativeProductCategory;

class InformativeProductCategoryAction extends Action
{
    public function __construct()
    {
        $this->model = InformativeProductCategory::class;
    }
}
