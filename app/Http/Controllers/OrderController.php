<?php

namespace App\Http\Controllers;

use App\Actions\OrderAction;
use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new OrderAction())->store_by_request($request);

        return response()->json([
            'message' => 'order added successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get (Request $request): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_by_request($request)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_todays_orders (Request $request): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_todays_orders_by_request($request)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_todays_order_by_id (string $id): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_todays_order_by_id($id)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_by_id (string $id): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_by_id($id)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_product_stats (Request $request): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_product_stats_by_request($request)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_user_orders (Request $request): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_user_orders_by_request($request)
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_user_order_by_id (Request $request, string $id): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_user_order_by_request_and_id($request, $id)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_user_product_stats (Request $request): JsonResponse
    {
        return response()->json(
            (new OrderAction())->get_user_product_stats_by_request($request)
        );
    }
}
