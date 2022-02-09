<?php

namespace App\Http\Controllers;

use App\Models\OrderTimeLimit;
use Illuminate\Http\Request;

class OrderTimeLimitController extends Controller
{
    public function get ()
    {
        return response()->json(
            (new OrderTimeLimit())->get()
        );
    }

    public function update (Request $request)
    {
        (new OrderTimeLimit())->update_by_request($request);

        return response()->json([
            'message' => 'order time limit updated successfully'
        ]);
    }

    public function get_available_groups ()
    {
        return response()->json(
            (new OrderTimeLimit())->get_available_groups()
        );
    }
}
