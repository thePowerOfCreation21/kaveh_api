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


}
