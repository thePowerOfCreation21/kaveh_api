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
        return response()->json(
            (new UserAction())->get_by_request($request)
        );
    }

    public function get_notifications_by_id (Request $request, string $id)
    {
        return response()->json(
            (new UserAction())->get_user_notifications_by_request_and_id($request, $id)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new UserAction())->get_by_id($id)
        );
    }

    public function login (Request $request)
    {
        return response()->json([
            'token' => (new UserAction())->login_by_request($request)->plainTextToken
        ]);
    }

    public function login_with_OTP (Request $request)
    {
        return response()->json([
            'token' => (new UserAction())->login_with_OTP_by_request($request)->plainTextToken
        ]);
    }

    public function forgot_password (Request $request)
    {
        (new UserAction())->send_one_time_password_by_request($request);

        return response()->json([
            'message' => 'one time password was sent to your phone number, try logging in with OTP'
        ]);
    }

    public function get_user_from_request (Request $request)
    {
        return response()->json($request->user());
    }

    public function change_password (Request $request)
    {
        (new UserAction())->change_password_by_request_and_model($request, $request->user());

        return response()->json([
            'message' => 'password changed successfully'
        ]);
    }

    public function update_by_request (Request $request)
    {
        (new UserAction())->update_user_by_request($request);

        return response()->json([
            'message' => 'updated successfully'
        ]);
    }
}
