<?php

namespace App\Services;

use App\Models\AdminChangesHistory;
use Illuminate\Http\Request;
use App\Models\Admin;
use function App\Helpers\time_to_custom_date;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public static function get_all (Request $request)
    {
        $skip = (! empty($request->input('skip'))) ? $request->input('skip') : 0;
        $limit = (! empty($request->input('limit'))) ? $request->input('limit') : 50;
        return (object) [
            'count' => Admin::count(),
            'admins' => Admin::orderBy('id', 'DESC')->skip($skip)->take($limit)->get()
        ];
    }

    public static function update (Request $request, $id)
    {
        $time = time_to_custom_date();
        $admin = Admin::where('id', $id)->first();

        if (empty($admin))
        {
            return response()->json([
                'message' => 'could not find admin with this id'
            ], 404);
        }

        $update_data = [
            'updated_at' => $time
        ];
        if (! empty($request->input('user_name')) && ! Admin::where('id', '!=', $id)->where('user_name', $request->input('user_name'))->exists())
        {
            $update_data['user_name'] = $request->input('user_name');
        }
        else if (! empty($request->input('user_name')))
        {
            return response()->json([
                'code' => 6,
                'message' => 'this user_name is already taken'
            ], 400);
        }
        (! empty($request->input('password'))) && $update_data['password'] = Hash::make($request->input('password'));
        if ($request->input('is_primary') !== null && $request->user()->is_primary)
        {
            $update_data['is_primary'] = $request->input('is_primary');
        }
        else if ($request->input('is_primary') !== null)
        {
            return response()->json([
                'code' => 7,
                'message' => 'only primary admins can change is_primary field'
            ], 403);
        }

        $admin->update($update_data);
        AdminChangesHistory::create([
            'doer_id' => $request->user()->id,
            'subject_id' => $admin->id,
            'action' => 'update',
            'date' => $time
        ]);

        return response()->json([
            'message' => 'admin updated successfully'
        ]);
    }
}
