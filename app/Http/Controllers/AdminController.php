<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function register (Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'password' => 'required|min:6',
            'is_primary' => 'boolean',
            'privilege' => 'array',
            'privilege.*' => 'distinct|in:manage_users,manage_admins,manage_orders'
        ]);

        if (Admin::where('user_name', $request->input('user_name'))->exists())
        {
            return response()->json([
                'code' => 1,
                'message' => 'this user_name is already taken'
            ], 400);
        }

        $admin_data = [
            'user_name' => $request->input('user_name'),
            'password' => Hash::make($request->input('password'))
        ];
        (! empty($request->input('is_primary'))) && $admin_data['is_primary'] = $request->input('is_primary');
        (! empty($request->input('privilege'))) ? $admin_data['privilege'] = $request->input('privilege') : $admin_data['privilege'] = [];

        Admin::create($admin_data);

        return response()->json([
            'message' => 'admin registered successfully'
        ]);
    }

    public function login (Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'password' => 'required'
        ]);

        $admin = Admin::where('user_name', $request->input('user_name'))->first();

        if (! empty($admin))
        {
            if (Hash::check($request->input('password'), $admin->password))
            {
                return response()->json([
                    'token' => $admin->createToken('auth_token')->plainTextToken
                ]);
            }
        }

        return response()->json([
            'code' => 3,
            'message' => 'user_name or password is wrong'
        ]);
    }
}
