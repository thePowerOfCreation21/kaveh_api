<?php

namespace App\Actions;

use App\Models\Admin;
use App\Models\AdminChangesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function App\Helpers\time_to_custom_date;

class AdminActions
{
    /**
     * @param Request $request
     * @return Admin
     */
    public static function register (Request $request): Admin
    {
        $request->validate([
            'user_name' => 'required|max:25',
            'password' => 'required|min:6',
            'privileges' => 'array',
            'privileges.*' => 'distinct|in:'.implode(",", Admin::$privileges_list)
        ]);

        $current_time = time();

        if (Admin::where('user_name', $request->input('user_name'))->exists())
        {
            response()->json([
                'code' => 1,
                'message' => 'this user_name is already taken'
            ], 400)->send();
            die();
        }

        $admin_data = [
            'user_name' => $request->input('user_name'),
            'password' => Hash::make($request->input('password')),
            'created_at' => $current_time,
            'updated_at' => $current_time
        ];
        (! empty($request->input('privileges'))) ? $admin_data['privileges'] = $request->input('privileges') : $admin_data['privileges'] = [];

        $admin = Admin::create($admin_data);
        AdminChangesHistory::create([
            'doer_id' => (isset($request->user()->id)) ? $request->user()->id : null,
            'subject_id' => $admin->id,
            'action' => 'register',
            'date' => $current_time
        ]);

        return $admin;
    }

    /**
     * this function returns token object if login is successful
     *
     * @param Request $request
     * @return object $admin->createToken('auth_token')
     */
    public static function login (Request $request)
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
                return $admin->createToken('auth_token');
            }
        }

        response()->json([
            'code' => 3,
            'message' => 'user_name or password is wrong'
        ], 400)->send();
        die();
    }

    /**
     * get all admins with pagination (can take 50 admins at max)
     *
     * @param Request $request
     * @return object
     */
    public static function get_all (Request $request)
    {
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50'
        ]);

        $skip = (! empty($request->input('skip'))) ? $request->input('skip') : 0;
        $limit = (! empty($request->input('limit'))) ? $request->input('limit') : 50;

        return (object) [
            'count' => Admin::count(),
            'admins' => Admin::orderBy('id', 'DESC')->skip($skip)->take($limit)->get()
        ];
    }

    /**
     * get admin by id (returns 404 http response if id is wrong then dies)
     *
     * @param string $id
     * @return Admin
     */
    public static function get_by_id (string $id): Admin
    {
        $admin = Admin::where('id', $id)->first();
        if (empty($admin))
        {
            response()->json([
                'message' => 'admin not found'
            ], 404)->send();
            die();
        }
        return $admin;
    }

    /**
     * update admin by id (returns 404 http response if id is wrong then dies)
     *
     * @param Request $request
     * @param string $id
     * @return Admin
     */
    public static function update (Request $request, string $id): Admin
    {
        $request->validate([
            'user_name' => 'string|max:25',
            'password' => 'string',
        ]);

        $time = time();
        $admin = Admin::where('id', $id)->first();

        if (empty($admin))
        {
            response()->json([
                'message' => 'could not find admin with this id'
            ], 404)->send();
            die();
        }

        if (isset($request->user()->id) && $request->user()->id == $id)
        {
            response()->json([
                'code' => 11,
                'message' => 'you can not edit your account'
            ], 403)->send();
            die();
        }

        $update_data = [
            'updated_at' => $time
        ];

        (! empty($request->input('password'))) && $update_data['password'] = Hash::make($request->input('password'));

        if (! empty($request->input('user_name')))
        {
            $update_data['user_name'] = $request->input('user_name');
        }
        else if (Admin::where('id', '!=', $id)->where('user_name', $request->input('user_name'))->exists())
        {
            response()->json([
                'code' => 6,
                'message' => 'this user_name is already taken'
            ], 400)->send();
            die();
        }

        $admin->update($update_data);
        AdminChangesHistory::create([
            'doer_id' => $request->user()->id,
            'subject_id' => $admin->id,
            'action' => 'update',
            'date' => $time
        ]);

        return $admin;
    }
}