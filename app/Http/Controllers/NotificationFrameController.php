<?php

namespace App\Http\Controllers;

use App\Actions\NotificationFrameAction;
use Illuminate\Http\Request;

class NotificationFrameController extends Controller
{
    public function store (Request $request)
    {
        (new NotificationFrameAction())->store_by_request($request);

        return response()->json([
            'message' => 'stored successfully'
        ]);
    }

    public function get (Request $request)
    {
        return response()->json(
            (new NotificationFrameAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new NotificationFrameAction())->get_by_id($id)
        );
    }

    public function update_by_id (Request $request, string $id)
    {
        (new NotificationFrameAction())->update_entity_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'updated successfully'
        ]);
    }

    public function delete_by_id (string $id)
    {
        (new NotificationFrameAction())->delete_by_id($id);

        return response()->json([
            'message' => 'deleted successfully'
        ]);
    }
}
