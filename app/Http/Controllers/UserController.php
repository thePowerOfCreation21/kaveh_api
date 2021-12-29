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
}
