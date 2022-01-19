<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\AdminActions;
use App\Views\AdminView;

class AdminController extends Controller
{
    public function register (Request $request)
    {
        try {
            AdminActions::register($request);

            return response()->json([
                'message' => 'admin registered successfully'
            ]);
        }
        catch (\Exception $exception)
        {
            return AdminView::get_response_by_exception($exception);
        }

    }

    public function login (Request $request)
    {
        try {
            return response()->json([
                'token' => AdminActions::login($request)->plainTextToken
            ]);
        }
        catch (\Exception $exception)
        {
            return AdminView::get_response_by_exception($exception);
        }

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
