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
        return BranchActions::get_with_request($request);
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

    public function update (Request $request, string $id)
    {
        BranchActions::update($request, $id);

        return response()->json([
            'message' => 'branch updated successfully'
        ]);
    }
}
