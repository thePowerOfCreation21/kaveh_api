<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\ProductActions;

class ProductController extends Controller
{
    public function store (Request $request)
    {
        ProductActions::store_by_request($request);

        return response()->json([
            'message' => 'product added successfully'
        ]);
    }
}
