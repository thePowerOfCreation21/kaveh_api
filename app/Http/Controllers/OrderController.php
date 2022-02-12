<?php

namespace App\Http\Controllers;

use App\Actions\OrderAction;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store (Request $request)
    {
        (new OrderAction())->store_by_request($request);

        return response()->json([
            'message' => 'order added successfully'
        ]);
    }
}
