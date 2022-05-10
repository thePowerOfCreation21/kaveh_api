<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\ContactUsMessageAction;

class ContactUsMessageController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function store (Request $request): JsonResponse
    {
        (new ContactUsMessageAction())->store_by_request($request);

        return response()->json([
            'message' => 'message sent successfully'
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
            (new ContactUsMessageAction())->get_by_request($request)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete_by_id (string $id): JsonResponse
    {
        (new ContactUsMessageAction())->delete_by_id($id);

        return response()->json([
            'message' => 'message deleted successfully'
        ]);
    }
}
