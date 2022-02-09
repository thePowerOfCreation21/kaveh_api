<?php

namespace App\Http\Controllers;

use App\Actions\ProductAction;
use Illuminate\Http\Request;
use App\Actions\ProductActions;

class ProductController extends Controller
{
    public function store (Request $request)
    {
        (new ProductAction())->store_by_request($request);

        return response()->json([
            'message' => 'product added successfully'
        ]);
    }

    public function get (Request $request)
    {
        return response()->json(
            (new ProductAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new ProductAction())->get_by_id($id)
        );
    }

    public function delete_by_id (string $id)
    {
        (new ProductAction())->delete_by_id($id);

        return response()->json([
            'message' => 'product deleted successfully'
        ]);
    }

    public function edit_by_id (Request $request, string $id)
    {
        ProductActions::edit_by_request($request, $id);

        return response()->json([
            'message' => 'product updated successfully'
        ]);
    }
}
