<?php

namespace App\Http\Controllers;

use App\Actions\DiscountCodeAction;
use Illuminate\Http\Request;
use App\Actions\DiscountCodeActions;

class DiscountCodeController extends Controller
{
    public function store (Request $request)
    {
        (new DiscountCodeAction())->store_by_request($request);

        return response()->json([
            'message' => 'discount added successfully'
        ]);
    }

    public function get (Request $request)
    {
        return response()->json(
            (new DiscountCodeAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            DiscountCodeActions::get_by_id($id)
        );
    }

    public function get_users (Request $request, string $id)
    {
        return response()->json(
            DiscountCodeActions::get_users_with_request($request, $id)
        );
    }

    public function delete_by_id (string $id)
    {
        DiscountCodeActions::delete_by_id($id);

        return response()->json([
            'message' => 'discount deleted successfully'
        ]);
    }
}
