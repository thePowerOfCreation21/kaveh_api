<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\InformativeProductAction;

class InformativeProductController extends Controller
{
    public function store (Request $request)
    {
        (new InformativeProductAction())->store_by_request($request);

        return response()->json([
            'message' => 'stored successfully'
        ]);
    }
}
