<?php

namespace App\Http\Controllers;

use App\Actions\CommonQuestionAction;
use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonQuestionController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new CommonQuestionAction())->store_by_request($request);

        return response()->json([
            'message' => 'common question stored successfully'
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
            (new CommonQuestionAction())->get_by_request($request)
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
            (new CommonQuestionAction())->get_by_id($id)
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function edit_by_id (Request $request, string $id): JsonResponse
    {
        (new CommonQuestionAction())->update_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'CommonQuestion updated successfully'
        ]);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete_by_id (string $id): JsonResponse
    {
        (new CommonQuestionAction())->delete_by_id($id);

        return response()->json([
            'message' => 'common question deleted successfully'
        ]);
    }
}
