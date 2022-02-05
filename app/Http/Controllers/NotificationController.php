<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\NotificationAction;

class NotificationController extends Controller
{
    public function send (Request $request)
    {
        (new NotificationAction())->send_by_request($request);

        return response()->json([
            'message' => 'sent successfully'
        ]);
    }

    public function get (Request $request)
    {
        return response()->json(
            (new NotificationAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new NotificationAction())->get_by_id($id)
        );
    }

    public function delete_by_id (string $id)
    {
        (new NotificationAction())->delete_by_id($id);

        return response()->json([
            'message' => 'delete successfully'
        ]);
    }

    public function get_users (Request $request, string $id)
    {
        return response()->json(
            (new NotificationAction())->get_users_by_request_and_id($request, $id)
        );
    }
}