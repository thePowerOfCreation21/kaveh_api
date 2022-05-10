<?php

namespace App\Http\Controllers;

use App\Actions\DiscountCodeAction;
use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountCodeController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new DiscountCodeAction())->store_by_request($request);

        return response()->json([
            'message' => 'discount added successfully'
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
            (new DiscountCodeAction())->get_by_request($request)
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
            (new DiscountCodeAction())->get_by_id($id)
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_users (Request $request, string $id): JsonResponse
    {
        return response()->json(
            (new DiscountCodeAction())->get_users_by_request_and_discount_id($request, $id)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete_by_id (string $id): JsonResponse
    {
        (new DiscountCodeAction())->delete_by_id($id);

        return response()->json([
            'message' => 'discount deleted successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function check_if_user_can_use_the_discount (Request $request): JsonResponse
    {
        $discountCode = (new DiscountCodeAction())->check_if_user_can_use_discount_by_request($request)
            ->details->discountCode;

        return response()->json(
            $discountCode
        );
    }
}
