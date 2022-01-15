<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\DiscountCodeActions;

class DiscountCodeController extends Controller
{
    public function store (Request $request)
    {
        DiscountCodeActions::store_by_request($request);

        return response()->json([
            'message' => 'discount added successfully'
        ]);
    }
}
