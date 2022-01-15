<?php

namespace App\Actions;

use Illuminate\Http\Request;

class DiscountCodeActions
{
    public static function store_by_request (Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:App\Models\Dis'
        ]);
    }
}
