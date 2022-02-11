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
            (new DiscountCodeAction())->get_by_id($id)
        );
    }

    public function get_users (Request $request, string $id)
    {
        return response()->json(
            (new DiscountCodeAction())->get_users_by_request_and_discount_code_id($request, $id)
        );
    }

    public function delete_by_id (string $id)
    {
        (new DiscountCodeAction())->delete_by_id($id);

        return response()->json([
            'message' => 'discount deleted successfully'
        ]);
    }
}
