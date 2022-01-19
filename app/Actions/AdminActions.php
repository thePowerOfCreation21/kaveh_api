<?php

namespace App\Actions;

use App\Models\Admin;
use App\Models\AdminChangesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminActions
{
    /**
     * get admin by id
     *
     * @param string $id
     * @return Admin
     * @throws \Exception
     */
    public static function get_by_id (string $id): Admin
    {
        $admin = Admin::where('id', $id)->first();
        if (empty($admin))
        {
            throw new \Exception('admin not found', 51);
        }
        return $admin;
    }

    /**
     * @param Request $request
     * @return Admin
     * @throws \Exception
     */
    public static function register (Request $request): Admin
    {
        $request->validate([
            'user_name' => 'required|max:25',
            'password' => 'required|min:6',
            'privileges' => 'array',
            'privileges.*' => 'distinct|in:'.implode(",", Admin::$privileges_list)
        ]);

        $current_time = date("Y-m-d H:i:s");

        if (Admin::where('user_name', $request->input('user_name'))->exists())
        {
            throw new \Exception('this user_name is already taken', 1);
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
     * @throws \Exception
     */
    public static function login (Request $request)
    {
        //abort(401, 'salam');
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

        throw new \Exception('user_name or password is wrong', 3);

        /*
        response()->json([
            'code' => 3,
            'message' => 'user_name or password is wrong'
        ], 400)->send();
        */
    }

    /**
     * update admin by id (returns 404 http response if id is wrong then dies)
     * no one can edit primary admins (returns 403 http response if admin is primary)
     *
     * @param Request $request
     * @param string $id
     * @return Admin
     * @throws \Exception
     */
    public static function update (Request $request, string $id): Admin
    {
        $request->validate([
            'user_name' => 'string|max:25',
            'password' => 'string',
        ]);

        $time = date('Y-m-d H:i:s');
        $admin = self::get_by_id($id);

        if ($admin->is_primary)
        {
            throw new \Exception('primary accounts can not be edited', 11);
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
            throw new \Exception('this user_namme is already taken', 6);
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

    /**
     * delete admin by id (returns 404 http response if id is wrong then dies)
     * no one can delete primary admins (returns 403 http response if admin is primary)
     *
     * @param Request $request
     * @param string $id
     * @return Admin
     * @throws \Exception
     */
    public static function delete (string $id): Admin
    {
        $admin = self::get_by_id($id);

        if ($admin->is_primary)
        {
            throw new \Exception('primary accounts can not be deleted', 11);
        }

        $admin->delete();

        return $admin;
    }

    /**
     * add privilege(s) to admin by simply sending array of privilege(s) in form of request
     *
     * @param Request $request
     * @param string $id
     * @return Admin
     * @throws \Exception
     */
    public static function add_privileges (Request $request, string $id): Admin
    {
        $request->validate([
            'privileges' => 'required|array',
            'privileges.*' => 'distinct|in:'.implode(',', Admin::$privileges_list)
        ]);

        $admin = self::get_by_id($id);

        foreach ($request->input('privileges') as $privilege)
        {
            if (! in_array($privilege, $admin->privileges))
            {
                $admin->privileges = array_merge($admin->privileges, [$privilege]);
            }
        }

        $admin->update([
            'privileges' => $admin->privileges
        ]);

        return $admin;
    }

    /**
     * remove privilege(s) from admin by simply sending array of privilege(s) in form of request
     *
     * @param Request $request
     * @param string $id
     * @return Admin
     * @throws \Exception
     */
    public static function delete_privileges (Request $request, string $id): Admin
    {
        $admin = self::get_by_id($id);

        $request->validate([
            'privileges' => 'required|array',
            'privileges.*' => 'distinct|in:'.implode(',', array_merge(Admin::$privileges_list, $admin->privileges))
        ]);

        $admin_privileges = [];

        foreach ($admin->privileges as $key => $privilege)
        {
            if (! in_array($privilege, $request->input('privileges')))
            {
                $admin_privileges[] = $privilege;
            }
        }

        $admin->update([
            'privileges' => $admin_privileges
        ]);

        return $admin;
    }
}
