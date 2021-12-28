<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\BranchActions;

class BranchController extends Controller
{
    public function store (Request $request)
    {
        BranchActions::store($request);

        return response()->json([
            'message' => 'branch added successfully'
        ]);
    }
}
