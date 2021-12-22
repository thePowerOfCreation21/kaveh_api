<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Services\AdminService;
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

    public function update (Request $request, $id)
    {
        AdminActions::update($request, $id);

        return response()->json([
            'message' => 'admin updated successfully'
        ]);
    }
}
