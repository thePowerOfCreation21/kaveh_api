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
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50'
        ]);

        return AdminService::get_all($request);
    }

    public function get_by_id ($id)
    {
        $admin = Admin::where('id', $id)->first();
        if (empty($admin))
        {
            return response()->json([
                'message' => 'admin not found'
            ], 404);
        }
        return response()->json($admin);
    }

    public function update (Request $request, $id)
    {
        $request->validate([
            'user_name' => 'string|max:25',
            'password' => 'string',
            'is_primary' => 'boolean'
        ]);

        return AdminService::update($request, $id);
    }
}
