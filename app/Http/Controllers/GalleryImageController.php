<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\GalleryImageAction;

class GalleryImageController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new GalleryImageAction())->store_by_request($request);

        return response()->json([
            'message' => 'image successfully added to gallery image(s)'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_all (Request $request): JsonResponse
    {
        return response()->json(
            (new GalleryImageAction())->get_by_request($request)
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
            (new GalleryImageAction())->get_by_id($id)
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function update(Request $request, string $id): JsonResponse
    {
        (new GalleryImageAction())->update_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'image updated successfully'
        ]);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete (string $id): JsonResponse
    {
        (new GalleryImageAction())->delete_by_id($id);

        return response()->json([
            'message' => 'image deleted successfully'
        ]);
    }
}
