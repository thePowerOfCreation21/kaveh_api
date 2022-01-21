<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\CustomException;

class UserActions
{
    /**
     * insert user into DB by request
     *
     * @param Request $request
     * @return User
     * @throws CustomException
     */
    public static function add_user_by_admin (Request $request): User
    {
        $user_data = $request->validate([
            'name' => 'required|string|max:64',
            'last_name' => 'required|string|max:64',
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6',
            'area' => 'required|string|max:255'
        ]);

        preg_match("/09\d{9}/", $user_data['phone_number'], $phone_number);

        if (empty($phone_number))
        {
            throw new CustomException('could not match phone number with required regex pattern', 30, 400);

        }

        $user_data['phone_number'] = $phone_number[0];

        return self::add_user($user_data);
    }

    /**
     * inserts user into DB
     * returns 400 http response if phone number is already taken
     *
     * @param array $user_data
     * @return User
     * @throws CustomException
     */
    public static function add_user (array $user_data): User
    {
        $user_data = (new User())->format_user_data_array($user_data);

        if (
            User::where('phone_number', $user_data['phone_number'])->exists()
        )
        {
            throw new CustomException('this phone number is already taken', 17, 400);
        }

        !empty($user_data['password']) && $user_data['password'] = Hash::make($user_data['password']);

        return User::create(array_filter($user_data));
    }

    /**
     * update user by request
     *
     * @param Request $request
     * @param string $id
     * @return User
     * @throws CustomException
     */
    public static function update_user_with_request (Request $request, string $id): User
    {
        $user_data = $request->validate([
            'name' => 'string|max:64',
            'last_name' => 'string|max:64',
            'phone_number' => 'regex:/09\d{9}/',
            'password' => 'string|min:6',
            'area' => 'string|max:255'
        ]);

        preg_match("/09\d{9}/", $user_data['phone_number'], $phone_number);

        if (empty($phone_number))
        {
            throw new CustomException('could not match phone number with required regex pattern', 30, 400);

        }

        $user_data['phone_number'] = $phone_number[0];

        return self::update_user($user_data, $id);
    }

    /**
     * update user by id
     * returns 400 http response if phone number is taken
     *
     * @param array $user_data
     * @param string $id
     * @return User
     * @throws CustomException
     */
    public static function update_user (array $user_data, string $id): User
    {
        $user = self::get_user_by_id($id);
        $user_data = (new User())->format_user_data_array($user_data);
        $update = [];

        !empty($user_data['name']) && $update['name'] = $user_data['name'];
        !empty($user_data['last_name']) && $update['last_name'] = $user_data['last_name'];
        !empty($user_data['second_phone_number']) && $update['second_phone_number'] = $user_data['second_phone_number'];
        !empty($user_data['area']) && $update['area'] = $user_data['area'];
        !empty($user_data['password']) && $update['password'] = hash::make($user_data['password']);
        if (!empty($user_data['phone_number']))
        {
            if (
                User::where('id', '!=', $id)->where('phone_number', $user_data['phone_number'])->exists()
            )
            {
                throw new CustomException('this phone number is already taken', 18, 400);
            }
            $update['phone_number'] = $user_data['phone_number'];
        }

        $user->update($update);

        return $user;
    }

    /**
     * get user by id
     * returns 404 http response if id is wrong
     *
     * @param string $id
     * @return User
     * @throws CustomException
     */
    public static function get_user_by_id (string $id): User
    {
        $user = User::where('id', $id)->first();

        if (empty($user))
        {
            throw new CustomException('could not find user with this id', 18, 404);
        }

        return $user;
    }

    /**
     * block user by request
     *
     * @param Request $request
     * @param string $id
     * @return int
     */
    public static function block_user_by_admin (Request $request, string $id): int
    {
        return self::block_user(
            $id,
            $request->validate([
                'reason_for_blocking' => 'required|string|max:255'
            ])['reason_for_blocking']
        );
    }

    /**
     * block user by id
     *
     * @param string $id
     * @param string $reason_for_blocking
     * @return int
     * @throws CustomException
     */
    public static function block_user (string $id, string $reason_for_blocking = ""): int
    {
        $user = self::get_user_by_id($id);

        return $user->update([
            'is_blocked' => true,
            'reason_for_blocking' => $reason_for_blocking
        ]);
    }

    /**
     * unblock user by id
     *
     * @param string $id
     * @return int
     * @throws CustomException
     */
    public static function unblock_user (string $id): int
    {
        $user = self::get_user_by_id($id);

        return $user->update([
            'is_blocked' => false,
            'reason_for_blocking' => null
        ]);
    }

    /**
     * get users by request
     *
     * @param Request $request
     * @return object
     */
    public static function get_users_by_admin (Request $request)
    {
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50',
            'search' => 'string|max:50'
        ]);

        return UserActions::get(
            (! empty($request->input('skip'))) ? $request->input('skip') : 0,
            (! empty($request->input('limit'))) ? $request->input('limit') : 50,
            (string) $request->input('search')
        );
    }

    /**
     * get users from DB (has pagination)
     *
     * @param int $skip
     * @param int $limit
     * @param string $search
     * @return object
     */
    public static function get (int $skip = 0, int $limit = 50, string $search = "")
    {
        $user = new User();

        if (!empty($search))
        {
            $user = $user
                ->where('name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('card_number', 'like', "%{$search}%");
        }

        return (object) [
            'count' => $user->count(),
            'data' => $user
                ->orderBy('id', 'DESC')
                ->skip($skip)
                ->take($limit)
                ->get()
        ];
    }
}
