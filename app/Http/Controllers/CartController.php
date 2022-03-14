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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_cart_products (Request $request): JsonResponse
    {
        return response()->json(
            (new CartAction())->get_cart_products_by_request($request)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function empty_the_cart (Request $request): JsonResponse
    {
        (new CartAction())->empty_the_cart_by_request($request);

        return response()->json([
            'message' => 'cart is empty now'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_cart_total_type (Request $request): JsonResponse
    {
        return response()->json(
            (new CartAction())->get_cart_total_type_by_request($request)
        );
    }
}
