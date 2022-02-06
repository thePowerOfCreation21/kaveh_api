<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use Illuminate\Http\Request;
use App\Actions\AdminActions;
use App\Actions\AdminAction;

class AdminController extends Controller
{
    public function register (Request $request)
    {
        (new AdminAction())->store_by_request($request);

        return response()->json([
            'message' => 'admin registered successfully'
        ]);
    }

    public function login (Request $request)
    {
        return response()->json([
            'token' => (new AdminAction())->login_by_request($request)->plainTextToken
        ]);

    }

    public function get_all (Request $request)
    {
        return response()->json(
            (new AdminAction())->get_by_request($request)
        );
    }

    public function get_by_id (string $id)
    {
        return response()->json(
            (new AdminAction())->get_by_id($id)
        );
    }

    public function update (Request $request, string $id)
    {
        (new AdminAction())->update_entity_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'admin updated successfully'
        ]);
    }

    public function delete (string $id)
    {
        (new AdminAction())->delete_by_id($id);

        return response()->json([
            'message' => 'admin deleted successfully'
        ]);
    }

    public function get_info (Request $request)
    {
        return response()->json($request->user());
    }
}
