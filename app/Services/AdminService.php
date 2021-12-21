<?php

namespace App\Services;

use App\Models\AdminChangesHistory;
use Illuminate\Http\Request;
use App\Models\Admin;
use function App\Helpers\time_to_custom_date;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public static function register (Request $request)
    {
        $current_time = time_to_custom_date();
        if (Admin::where('user_name', $request->input('user_name'))->exists())
        {
            return response()->json([
                'code' => 1,
                'message' => 'this user_name is already taken'
            ], 400);
        }

        $admin_data = [
            'user_name' => $request->input('user_name'),
            'password' => Hash::make($request->input('password')),
            'created_at' => $current_time,
            'updated_at' => $current_time
        ];
        (! empty($request->input('is_primary'))) && $admin_data['is_primary'] = $request->input('is_primary');
        if (! (empty($request->user()) && ! $request->user()->is_primary) && $admin_data['is_primary'])
        {
            return response()->json([
                'code' => 5,
                'message' => 'only primary admins can register primary accounts'
            ], 400);
        }
        (! empty($request->input('privileges'))) ? $admin_data['privileges'] = $request->input('privileges') : $admin_data['privileges'] = [];

        $admin = Admin::create($admin_data);
        AdminChangesHistory::create([
            'doer_id' => (isset($request->user()->id)) ? $request->user()->id : null,
            'subject_id' => $admin->id,
            'action' => 'register',
            'date' => $current_time
        ]);

        return response()->json([
            'message' => 'admin registered successfully',
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
