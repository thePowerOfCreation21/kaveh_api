<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\BranchAction;

class BranchController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new BranchAction())->store_by_request($request);

        return response()->json([
            'message' => 'branch added successfully'
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
            (new BranchAction())->get_by_request($request)
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
            (new BranchAction())->get_by_id($id)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete_by_id (string $id): JsonResponse
    {
        (new BranchAction())->delete_by_id($id);

        return response()->json([
            'message' => 'branch deleted successfully'
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function update (Request $request, string $id): JsonResponse
    {
        (new BranchAction())->update_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'branch updated successfully'
        ]);
    }
}
