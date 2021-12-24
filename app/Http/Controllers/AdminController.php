<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\AdminActions;

class AdminController extends Controller
{
    public function register (Request $request)
    {
        AdminActions::register($request);

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
        return AdminActions::get_all($request);
    }

    public function get_by_id (string $id)
    {
        return AdminActions::get_by_id($id);
    }

    public function update (Request $request, string $id)
    {
        AdminActions::update($request, $id);

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
}
