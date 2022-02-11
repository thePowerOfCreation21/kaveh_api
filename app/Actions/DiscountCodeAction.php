<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\DiscountCode;

class DiscountCodeAction extends Action
{
    public function __construct()
    {
        $this->model = DiscountCode::class;
    }
}
