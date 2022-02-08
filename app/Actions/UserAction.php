<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\User;
use App\Services\PaginationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

class UserAction extends Action
{
    protected $validation_roles = [
        'login' => [
            'phone_number' => 'required|string|max:11',
            'password' => 'required|string|max:100'
        ],
        'register_by_admin' => [
            'name' => 'required|string|max:64',
            'last_name' => 'required|string|max:64',
            'phone_number' => 'required|string',
            'password' => 'required|string|min:6',
            'area' => 'required|string|max:255'
        ],
        'update_by_admin' => [
            'name' => 'string|max:64',
            'last_name' => 'string|max:64',
            'phone_number' => 'regex:/09\d{9}/',
            'password' => 'string|min:6',
            'area' => 'string|max:255'
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

    public function __construct()
    {
        $this->model = User::class;
    }

    /**
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function get_by_id(string $id): Model
    {
        return parent::get_by_id($id);
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
     * @param null|Model|Builder $eloquent
     * @param array|string[] $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request(
        Request $request,
        $query_validation_role = 'get_query',
        $eloquent = null,
        array $order_by = ['id' => 'DESC']
    ): object
    {
        return parent::get_by_request($request, $query_validation_role, $eloquent, $order_by);
    }

    /**
     * @param array $query
     * @param null $eloquent
     * @return Model|Builder
     */
    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

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
        Request $request,
        string $id,
        $validation_role = 'get_notifications_query'
    ): object
    {
        $user = $this->get_by_id($id);

        return PaginationService::paginate_with_request(
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
     * @return Model|Builder|mixed
     */
    public function notifications_query_to_eloquent (array $query, $eloquent)
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
    public function block_by_request_and_id (Request $request, string $id, $validation_role = 'block_user'): Model
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
    public function login_by_request (Request $request, $validation_role = 'login'): NewAccessToken
    {
        return $this->login(
            $this->get_data_from_request($request, $validation_role)
        );
    }

    /**
     * @param array $data
     * @return NewAccessToken
     * @throws CustomException
     */
    public function login (array $data): NewAccessToken
    {
        $user = $this->model::where('phone_number', $data['phone_number'])->first();

        if (!empty($user) && Hash::check($data['password'], $user->password))
        {
            return $user->createToken('auth_token');
        }

        throw new CustomException('login data was not valid', 83, 400);
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
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'register_by_admin'): Model
    {
        return parent::store_by_request($request, $validation_role);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function update_entity_by_request_and_id(Request $request, string $id, $validation_role = 'update_by_admin'): Model
    {
        $data = $this->get_data_from_request($request, $validation_role);
        return $this->update_by_id($data, $id);
    }

    /**
     * @param array $update_data
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function update_by_id (array $update_data, string $id): Model
    {
        $user = $this->get_by_id($id);

        isset($update_data['password']) && ($update_data['password'] = Hash::make($update_data['password']));

        if (isset($update_data['phone_number']))
        {
            $update_data['phone_number'] = $this->check_phone_number($update_data['phone_number']);

            if (
                User::where('id', '!=', $id)->where('phone_number', $update_data['phone_number'])->exists()
            )
            {
                throw new CustomException('this phone number is already taken', 18, 400);
            }
        }

        $user->update($update_data);

        return $user;
    }

    /**
     * @param array $data
     * @return Model
     * @throws CustomException
     */
    public function store (array $data): Model
    {
        $data['phone_number'] = $this->check_phone_number($data['phone_number']);

        if (
            User::where('phone_number', $data['phone_number'])->exists()
        )
        {
            throw new CustomException('this phone number is already taken', 17, 400);
        }

        !empty($data['password']) && $data['password'] = Hash::make($data['password']);

        return $this->model::create($data);
    }

    /**
     * @param string $phone_number
     * @return string
     * @throws CustomException
     */
    public function check_phone_number (string $phone_number): string
    {
        preg_match("/09\d{9}/", $phone_number, $phone_numbers);

        if (empty($phone_numbers))
        {
            throw new CustomException('could not match phone number with required regex pattern', 30, 400);
        }

        return $phone_numbers[0];
    }

    /**
     * @param array $query
     * @return string[]
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
     * gets list of user ids and check if all users is in DB or not
     *
     * @param array $ids
     * @return bool
     * @throws CustomException
     */
    public static function check_if_users_exists (array $ids): bool
    {
        $users = DB::table('users')
            ->select('phone_number')
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
}

