<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\AdminActions;
use App\Actions\AdminAction;

class AdminController extends Controller
{
    public function register (Request $request)
    {
        AdminActions::register_with_request($request);

        return response()->json([
            'message' => 'admin registered successfully'
        ]);
    }

    public function login (Request $request)
    {
        return response()->json([
            'token' => AdminActions::login($request)->plainTextToken
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
        return AdminActions::get_by_id($id);
    }

    public function update (Request $request, string $id)
    {
        AdminActions::update_with_request($request, $id);

        return response()->json([
            'message' => 'admin updated successfully'
        ]);
    }

    public function delete (string $id)
    {
        AdminActions::delete($id);

        return response()->json([
            'message' => 'admin deleted successfully'
        ]);
    }

    public function add_privileges (Request $request, string $id)
    {
        AdminActions::add_privileges($request, $id);

        return response()->json([
            'message' => 'privilege(s) added successfully'
        ]);
    }

    public function delete_privileges (Request $request, string $id)
    {
        AdminActions::delete_privileges($request, $id);

        return response()->json([
            'message' => 'privilege(s) removed successfully'
        ]);
    }

    public function get_info (Request $request)
    {
        return response()->json($request->user());
    }
}
