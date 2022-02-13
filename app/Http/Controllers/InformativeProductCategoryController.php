<?php

namespace App\Http\Controllers;

use App\Actions\InformativeProductCategoryAction;
use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InformativeProductCategoryController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new InformativeProductCategoryAction())->store_by_request($request);

        return response()->json([
            'message' => 'stored successfully'
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
            (new InformativeProductCategoryAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new InformativeProductCategoryAction())->get_by_id($id)
        );
    }
}
