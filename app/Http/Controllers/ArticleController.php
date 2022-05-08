<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\ArticleAction;

class ArticleController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new ArticleAction())->store_by_request($request);

        return response()->json([
            'message' => 'article added successfully'
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
            (new ArticleAction())->get_by_request($request)
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
            (new ArticleAction())->get_by_id($id)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete_by_id (string $id): JsonResponse
    {
        (new ArticleAction())->delete_by_id($id);

        return response()->json([
            'message' => 'article deleted successfully'
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function update_by_id (Request $request, $id): JsonResponse
    {
        (new ArticleAction())->update_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'article updated successfully'
        ]);
    }
}
