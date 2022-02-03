<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\UserActions;

class UserController extends Controller
{
    public function add (Request $request)
    {
        UserActions::add_user_by_admin($request);

        return response()->json([
            'message' => 'user added successfully'
        ]);
    }

    public function update_by_id (Request $request, string $id)
    {
        UserActions::update_user_with_request($request, $id);

        return response()->json([
            'message' => 'user updated successfully'
        ]);
    }

    public function block_user_by_id (Request $request, string $id)
    {
        UserActions::block_user_with_request($request, $id);

        return response()->json([
            'message' => 'user blocked successfully'
        ]);
    }

    public function unblock_user_by_id (string $id)
    {
        UserActions::unblock_user($id);

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
}
