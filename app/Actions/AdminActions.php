<?php

namespace App\Actions;

use App\Models\Admin;
use App\Models\AdminChangesHistory;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\CustomException;

class AdminActions
{
    /**
     * get admins
     * (uses PaginationService to paginate)
     *
     * @param Request $request
     * @return object
     */
    public static function get_with_request (Request $request)
    {
        return PaginationService::paginate_with_request(
            $request,
            Admin::orderBy('id', 'DESC')
        );
    }

    /**
     * get admin by id
     *
     * @param string $id
     * @return Admin
     * @throws CustomException
     */
    public static function get_by_id (string $id): Admin
    {
        $admin = Admin::where('id', $id)->first();
        if (empty($admin))
        {
            throw new CustomException('admin not found', 51, 400);
        }
        return $admin;
    }

    /**
     * register admin with request
     *
     * @param Request $request
     * @return Admin
     * @throws CustomException
     */
    public static function register_with_request (Request $request): Admin
    {
        $admin_data = $request->validate([
            'user_name' => 'required|max:25',
            'password' => 'required|min:6',
            'privileges' => 'array|max:'.count(Admin::$privileges_list),
            'privileges.*' => 'boolean'
        ]);

        $admin_data['privileges'] = Admin::fix_privileges(
            (object) (!isset($admin_data['privileges']) ? [] : $admin_data['privileges'])
        );

        return self::register($admin_data);
    }

    /**
     * register new admin
     *
     * @param array $admin_data
     * @return Admin
     * @throws CustomException
     */
    public static function register (array $admin_data): Admin
    {
        $current_time = date("Y-m-d H:i:s");

        if (Admin::where('user_name', $admin_data)->exists())
        {
            throw new CustomException('this user_name is already taken', 1, 400);
        }

        return Admin::create(array_merge($admin_data, [
            'created_at' => $current_time,
            'updated_at' => $current_time
        ]));
    }

    /**
     * this function returns token object if login is successful
     *
     * @param Request $request
     * @return object $admin->createToken('auth_token')
     * @throws CustomException
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

        throw new CustomException('user_name or password is wrong', 3, 400);
    }

    /**
     * update admin with request
     *
     * @param Request $request
     * @param string $id
     * @return Admin
     * @throws CustomException
     */
    public static function update_with_request (Request $request, string $id): Admin
    {
        $update_data = $request->validate([
            'user_name' => 'string|max:25',
            'password' => 'string',
            'privileges' => 'array|max:'.count(Admin::$privileges_list),
            'privileges.*' => 'boolean'
        ]);

        return self::update($update_data, $id);
    }

    /**
     * update admin
     * could not update primary admin
     *
     * @param array $update_data
     * @param string $id
     * @return Admin
     * @throws CustomException
     */
    public static function update (array $update_data, string $id)
    {
        $time = date('Y-m-d H:i:s');
        $admin = self::get_by_id($id);

        if ($admin->is_primary)
        {
            throw new CustomException('primary accounts can not be edited', 11, 400);
        }

        if (isset($update_data['user_name']) && Admin::where('id', '!=', $id)->where('user_name', $update_data['user_name'])->exists())
        {
            throw new CustomException('this user_name is already taken', 6, 400);
        }

        isset($update_data['password']) && $update_data['password'] = Hash::make($update_data['password']);

        if (isset($update_data['privileges']))
        {
            $update_data['privileges'] = Admin::fix_privileges(
                (object) (!isset($update_data['privileges']) ? [] : $update_data['privileges']),
                Admin::fix_privileges(
                    (object) $admin->privileges
                )
            );
        }

        $admin->update(array_merge($update_data, [
            'updated_at' => $time
        ]));

        return $admin;
    }

    /**
     * delete admin by id (returns 404 http response if id is wrong then dies)
     * no one can delete primary admins (returns 403 http response if admin is primary)
     *
     * @param string $id
     * @return Admin
     * @throws CustomException
     */
    public static function delete (string $id): Admin
    {
        $admin = self::get_by_id($id);

        if ($admin->is_primary)
        {
            throw new CustomException('primary accounts can not be deleted', 11, 400);
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
     * @throws CustomException
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
     * @throws CustomException
     */
    public static function delete_privileges (Request $request, string $id): Admin
    {
        $admin = self::get_by_id($id);

        $request->validate([
            'privileges' => 'required|array',
            'privileges.*' => 'distinct|in:'.implode(',', array_merge(Admin::$privileges_list, $admin->privileges))
        ]);

        $admin_privileges = [];

        foreach ($admin->privileges as $privilege)
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
