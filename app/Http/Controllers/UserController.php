<?php

namespace App\Http\Controllers;

use App\Actions\UserAction;
use Illuminate\Http\Request;
use App\Actions\UserActions;

class UserController extends Controller
{
    public function add (Request $request)
    {
        (new UserAction())->store_by_request($request);

        return response()->json([
            'message' => 'user added successfully'
        ]);
    }

    public function update_by_id (Request $request, string $id)
    {
        (new UserAction())->update_entity_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'user updated successfully'
        ]);
    }

    public function block_user_by_id (Request $request, string $id)
    {
        (new UserAction())->block_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'user blocked successfully'
        ]);
    }

    public function unblock_user_by_id (string $id)
    {
        (new UserAction())->unblock_by_id($id);

        return response()->json([
            'message' => 'user unblocked successfully'
        ]);
    }

    public function get (Request $request)
    {
        return UserActions::get_users_with_request($request);
    }

    public function get_notifications_by_id (Request $request, string $id)
    {
        return response()->json(
            UserActions::get_notifications_by_request_and_id($request, $id)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new UserAction())->get_by_id($id)
        );
    }
}
