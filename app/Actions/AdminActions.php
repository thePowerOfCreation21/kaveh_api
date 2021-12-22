<?php

namespace App\Actions;

use App\Models\Admin;
use App\Models\AdminChangesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function App\Helpers\time_to_custom_date;

class AdminActions
{
    public static function register (Request $request)
    {
        $request->validate([
            'user_name' => 'required|max:25',
            'password' => 'required|min:6',
            'is_primary' => 'boolean',
            'privileges' => 'array',
            'privileges.*' => 'distinct|in:'.implode(",", (isset($request->user()->privileges) && ! $request->user()->is_primary) ? $request->user()->privileges : Admin::$privileges_list)
        ]);

        $current_time = time_to_custom_date();

        if (Admin::where('user_name', $request->input('user_name'))->exists())
        {
            response()->json([
                'code' => 1,
                'message' => 'this user_name is already taken'
            ], 400)->send();
            die();
        }

        $admin_data = [
            'is_primary' => false,
            'user_name' => $request->input('user_name'),
            'password' => Hash::make($request->input('password')),
            'created_at' => $current_time,
            'updated_at' => $current_time
        ];
        (! empty($request->input('is_primary'))) && $admin_data['is_primary'] = $request->input('is_primary');
        (! empty($request->input('privileges'))) ? $admin_data['privileges'] = $request->input('privileges') : $admin_data['privileges'] = [];

        if ((! empty($request->user()) && ! $request->user()->is_primary) && $admin_data['is_primary'])
        {
            response()->json([
                'code' => 5,
                'message' => 'only primary admins can register primary accounts'
            ], 400)->send();
            die();
        }

        $admin = Admin::create($admin_data);
        AdminChangesHistory::create([
            'doer_id' => (isset($request->user()->id)) ? $request->user()->id : null,
            'subject_id' => $admin->id,
            'action' => 'register',
            'date' => $current_time
        ]);

        return $admin;
    }
}
