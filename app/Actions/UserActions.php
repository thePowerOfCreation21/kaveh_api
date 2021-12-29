<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\User;

class UserActions
{
    /**
     * insert user into DB by request
     *
     * @param Request $request
     * @return User
     */
    public static function add_user_by_admin (Request $request): User
    {
        $user_data = $request->validate([
            'name' => 'required|string|max:64',
            'last_name' => 'required|string|max:64',
            'phone_number' => 'required|regex:/09\d{9}/',
            'password' => 'required|string|min:6',
            'area' => 'required|string|max:255'
        ]);

        return self::add_user($user_data);
    }

    /**
     * inserts user into DB
     * returns 400 http response if phone number is already taken
     *
     * @param array $user_data
     * @return User
     */
    public static function add_user (array $user_data): User
    {
        $user_data = (new User())->format_user_data_array($user_data);

        if (
            User::where('phone_number', $user_data['phone_number'])->exists()
        )
        {
            response()->json([
                'code' => 17,
                'message' => 'this phone number is already taken'
            ] ,400)->send();
            die();
        }

        return User::create($user_data);
    }
}
