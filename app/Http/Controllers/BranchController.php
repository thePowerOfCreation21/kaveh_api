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

    public function get (Request $request)
    {
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50'
        ]);

        return response()->json(
            BranchActions::get(
                (! empty($request->input('skip'))) ? $request->input('skip') : 0,
                (! empty($request->input('limit'))) ? $request->input('limit') : 50
            )
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            BranchActions::get_by_id($id)
        );
    }

    public function delete_by_id (string $id)
    {
        BranchActions::delete_by_id($id);

        return response()->json([
            'message' => 'branch deleted successfully'
        ]);
    }
}
