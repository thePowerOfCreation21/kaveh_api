<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\Cart;

class CartAction extends Action
{
    public function __construct()
    {
        $this->model = Cart::class;
    }
}
