<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\Request;
use App\Actions\AdminAction;

class AdminController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function register (Request $request): \Illuminate\Http\JsonResponse
    {
        (new AdminAction())->store_by_request($request);

        return response()->json([
            'message' => 'admin registered successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function login (Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'token' => (new AdminAction())->login_by_request($request)->plainTextToken
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function get_all (Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            (new AdminAction())->get_by_request($request)
        );
    }

    /**
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function get_by_id (string $id): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            (new AdminAction())->get_by_id($id)
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     * @throws CustomException
     */
    public function update (Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        (new AdminAction())->update_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'admin updated successfully'
        ]);
    }

    /**
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete (string $id): \Illuminate\Http\JsonResponse
    {
        (new AdminAction())->delete_by_id($id);

        return response()->json([
            'message' => 'admin deleted successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_info (Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($request->user());
    }
}
