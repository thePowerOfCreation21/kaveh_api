<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\CustomException;
use App\Services\PaginationService;
use Illuminate\Support\Facades\DB;
use function App\Helpers\convert_to_boolean;

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
    public static function block_user_with_request (Request $request, string $id): int
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
    public static function get_users_with_request (Request $request)
    {
        $query_from_request = $request->validate([
            'search' => 'string|max:50'
        ]);

        $pagination_values = PaginationService::get_values_from_request($request);

        return UserActions::get(
            $pagination_values['skip'],
            $pagination_values['limit'],
            $query_from_request
        );
    }

    /**
     * get users from DB (has pagination)
     * (using PaginationService to paginate)
     *
     * @param int $skip
     * @param int $limit
     * @param array $query
     * @return object
     */
    public static function get (int $skip = 0, int $limit = 50, array $query = [])
    {
        $user = self::query_to_eloquent($query);

        return PaginationService::paginate(
            $user->orderBy('id', 'DESC'),
            $skip,
            $limit
        );
    }

    /**
     * converts query array to eloquent
     *
     * @param array $query
     * @param null $eloquent
     * @return User
     */
    public static function query_to_eloquent (array $query = [], $eloquent = null)
    {
        if ($eloquent === null)
        {
            $eloquent = new User();
        }


        if (isset($query['search']))
        {
            $eloquent = $eloquent
                ->where('name', 'like', "%{$query['search']}%")
                ->orWhere('last_name', 'like', "%{$query['search']}%")
                ->orWhere('phone_number', 'like', "%{$query['search']}%")
                ->orWhere('card_number', 'like', "%{$query['search']}%");
        }

        if (isset($query['ids']))
        {
            $eloquent = $eloquent->whereIn('id', $query['ids']);
        }

        return $eloquent;
    }

    /**
     * gets list of user ids and check if all users is in DB or not
     *
     * @param array $ids
     * @return bool
     * @throws CustomException
     */
    public static function check_if_users_exists (array $ids)
    {
        $users = DB::select("
            SELECT
            `id`
            FROM `users`
            WHERE
            `id` in(".self::convert_id_array_to_string($ids).")
        ");

        foreach ($ids AS $id)
        {
            foreach ($users AS $user_key => $user)
            {
                $user_was_found = false;
                if ($user->id == $id)
                {
                    $user_was_found = true;
                    unset($users[$user_key]);
                    break;
                }
            }

            if (!$user_was_found)
            {
                throw new CustomException("user id '{$id}' not found", 55, 404);
            }
        }

        return true;
    }

    public static function convert_id_array_to_string (array $ids)
    {
        $string = "";
        $last_index = array_key_last($ids);
        foreach ($ids AS $key => $id)
        {
            $string .= "'{$id}'";
            if ($key != $last_index)
            {
                $string .= ",";
            }
        }
        return $string;
    }

    public static function get_phone_numbers (array $query = [])
    {
        $phone_numbers = [];

        $users = self::query_to_eloquent($query)
            ->select('phone_number')
            ->get();

        foreach ($users AS $user)
        {
            $phone_numbers[] = $user['phone_number'];
        }

        return $phone_numbers;
    }

    public static function get_notifications_by_request_and_id (Request $request, string $id)
    {
        $user = self::get_user_by_id($id);

        return self::get_notifications_by_request(
            $request,
            $user->notifications()
        );
    }

    public static function get_notifications_by_request (Request $request, $eloquent)
    {
        $query = $request->validate([
            'is_seen' => 'in:true,false'
        ]);

        return PaginationService::paginate_with_request(
            $request,
            self::notifications_query_to_eloquent($query, $eloquent)
        );
    }

    public static function notifications_query_to_eloquent (array $query, $eloquent)
    {
        return (new NotificationAction())->notification_users_query_to_eloquent($query, $eloquent);
    }
}
