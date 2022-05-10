<?php

namespace App\Http\Controllers;

use App\Actions\LicenseAction;
use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new LicenseAction())->store_by_request($request);

        return response()->json([
            'message' => 'license stored successfully'
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
            (new LicenseAction())->get_by_request($request)
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
            (new LicenseAction())->get_by_id($id)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete_by_id (string $id): JsonResponse
    {
        (new LicenseAction())->delete_by_id($id);

        return response()->json([
            'message' => 'deleted successfully'
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function update_by_id (Request $request, string $id): JsonResponse
    {
        (new LicenseAction())->update_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'updated successfully'
        ]);
    }
}
