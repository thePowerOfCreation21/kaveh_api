<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\InformativeProduct;

class InformativeProductAction extends Action
{
    public function __construct()
    {
        $this->model = InformativeProduct::class;
    }
}
