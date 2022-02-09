<?php

namespace App\Http\Controllers;

use App\Actions\CartAction;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store_or_update_cart_product (Request $request, string $product_id)
    {
        $result = (new CartAction())->store_or_update_cart_product_by_request_and_product_id($request, $product_id);

        if (is_object($result))
        {
            return response()->json([
                'code' => 1,
                'message' => 'stored/updated successfully'
            ]);
        }

        return response()->json([
            'code' => 2,
            'message' => 'product deleted from cart'
        ]);
    }
}
