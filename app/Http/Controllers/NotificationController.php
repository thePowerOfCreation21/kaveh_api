<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\NotificationAction;

class NotificationController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function send (Request $request): JsonResponse
    {
        (new NotificationAction())->send_by_request($request);

        return response()->json([
            'message' => 'sent successfully'
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
            (new NotificationAction())->get_by_request($request)
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
            (new NotificationAction())->get_by_id($id)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function delete_by_id (string $id): JsonResponse
    {
        (new NotificationAction())->delete_by_id($id);

        return response()->json([
            'message' => 'delete successfully'
        ]);
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
            (new NotificationAction())->get_users_by_request_and_id($request, $id)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_user_notifications (Request $request): JsonResponse
    {
        return response()->json(
            (new NotificationAction())->get_user_notifications_by_request($request)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_user_notifications_count(Request $request): JsonResponse
    {
        return response()->json(
            (new NotificationAction())->get_user_notifications_count_by_request($request)
        );
    }

    /**
     * @param Request $request
     * @param string $notificationId
     * @return JsonResponse
     * @throws CustomException
     */
    public function seen_by_user (Request $request, string $notificationId): JsonResponse
    {
        (new NotificationAction())->seen_by_request_and_notification_id($request, $notificationId);

        return response()->json([
            'message' => 'action done successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function seen_user_notifications (Request $request): JsonResponse
    {
        (new NotificationAction())->seen_by_request($request);

        return response()->json([
            'message' => 'action done successfully'
        ]);
    }
}
