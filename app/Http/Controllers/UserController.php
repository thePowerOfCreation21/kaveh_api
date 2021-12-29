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
        UserActions::update_user_by_admin($request, $id);

        return response()->json([
            'message' => 'user updated successfully'
        ]);
    }

    public function block_user_by_id (Request $request, string $id)
    {
        UserActions::block_user_by_admin($request, $id);

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
        return response()->json(
            UserActions::get_users_by_admin($request)
        );
    }
}
