<?php

namespace App\Http\Controllers;

use App\Actions\CartAction;
use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @param Request $request
     * @param string $product_id
     * @return JsonResponse
     * @throws CustomException
     */
    public function store_or_update_cart_product (Request $request, string $product_id): JsonResponse
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

    /**
     * @param Request $request
     * @param string $product_id
     * @return JsonResponse
     * @throws CustomException
     */
    public function delete_cart_product (Request $request, string $product_id): JsonResponse
    {
        (new CartAction())->delete_cart_product_by_request_and_product_id($request, $product_id);

        return response()->json([
            'message' => 'product deleted from cart successfully'
        ]);
    }
}
