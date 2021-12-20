<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public static function register (Request $request)
    {
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
        (! empty($request->input('privileges'))) ? $admin_data['privileges'] = $request->input('privileges') : $admin_data['privileges'] = [];

        Admin::create($admin_data);

        return response()->json([
            'message' => 'admin registered successfully'
        ]);
    }

    public static function login (Request $request)
    {
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
