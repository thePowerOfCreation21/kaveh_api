<?php

namespace App\Actions;

use App\Services\Action;
use App\Exceptions\CustomException;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

class AdminAction extends Action
{
    protected array $validation_roles = [
        'login' => [
            'user_name' => 'required|string|max:150',
            'password' => 'required|string|max:150'
        ],
        'get_query' => [
            'search' => 'string|max:100'
        ]
    ];

    public function __construct()
    {
        $this->validation_roles['store'] = [
            'user_name' => 'required|max:25',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'privileges' => 'array|max:'.count(Admin::$privileges_list)
        ];

        $this->validation_roles['update'] = [
            'user_name' => 'string|max:25',
            'password' => 'string',
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'privileges' => 'array|max:'.count(Admin::$privileges_list),
        ];

        foreach (Admin::$privileges_list as $privilege)
        {
            $this->validation_roles['store']["privileges.$privilege"] = 'boolean';
            $this->validation_roles['update']["privileges.$privilege"] = 'boolean';
        }

        $this->model = Admin::class;
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param Model|\Illuminate\Database\Eloquent\Builder|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request(
        Request $request,
        array|string $validation_role = 'get_query',
        array $query_addition = ['is_primary' => false],
        Model|\Illuminate\Database\Eloquent\Builder $eloquent = null,
        array $relations = [],
        array $order_by = ['id' => 'DESC']
    ): object
    {
        return parent::get_by_request($request, $validation_role, $query_addition, $eloquent, $relations, $order_by);
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
        return parent::get_by_id($id, $query, $relations); // TODO: Change the autogenerated stub
    }

    /**
     * @param Request $request
     * @param string $validation_role
     * @return Admin
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'store'): Admin
    {
        $data = $this->get_data_from_request($request, $validation_role);

        $data['password'] = Hash::make($data['password']);

        $data['privileges'] = Admin::fix_privileges(
            (object) (!isset($data['privileges']) ? [] : $data['privileges'])
        );

        return $this->store($data);
    }

    /**
     * @param array $data
     * @return Admin
     * @throws CustomException
     */
    public function store (array $data): Admin
    {
        if (Admin::where('user_name', $data['user_name'])->exists())
        {
            throw new CustomException('this user_name is already taken', 1, 400);
        }

        return $this->model::create($data);
    }

    /**
     * @param Model|\Illuminate\Database\Eloquent\Builder $eloquent
     * @param array $update_data
     * @param callable|null $updating
     * @return bool|int
     * @throws CustomException
     */
    public function update_by_eloquent(
        Model|\Illuminate\Database\Eloquent\Builder $eloquent,
        array $update_data,
        callable $updating = null
    ): bool|int
    {
        if (is_null($updating))
        {
            $updating = function ($eloquent, &$update_data)
            {
                $admin = $this->get_first_by_eloquent($eloquent);

                if ($admin->is_primary)
                {
                    throw new CustomException('primary accounts can not be edited', 11, 400);
                }

                if (
                    isset($update_data['user_name'])
                    &&
                    $this->model::where('user_name', $update_data['user_name'])
                        ->where('id', '!=', $admin->id)
                        ->count() > 0
                )
                {
                    throw new CustomException('this user_name is already taken', 6, 400);
                }

                if (isset($update_data['privileges']))
                {
                    $update_data['privileges'] = Admin::fix_privileges(
                        (object) $update_data['privileges'],
                        Admin::fix_privileges(
                            (object) $admin->privileges
                        )
                    );
                }

                isset($update_data['password']) && $update_data['password'] = Hash::make($update_data['password']);
            };
        }

        return parent::update_by_eloquent($eloquent, $update_data, $updating);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param array|string $validation_role
     * @return int|bool
     * @throws CustomException
     */
    public function update_by_request_and_id (Request $request, string $id, array|string $validation_role = 'update'): int|bool
    {
        return $this->update_by_request_and_query($request, ['id' => $id], $validation_role);
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @return NewAccessToken
     * @throws CustomException
     */
    public function login_by_request (Request $request, array|string $validation_role = 'login'): NewAccessToken
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
        $admin = $this->model::where('user_name', $data['user_name'])->first();

        if (! empty($admin))
        {
            if (Hash::check($data['password'], $admin->password))
            {
                return $admin->createToken('auth_token');
            }
        }

        throw new CustomException('user_name or password is wrong', 3, 400);
    }

    /**
     * @param array $query
     * @param Model|\Illuminate\Database\Eloquent\Builder|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return Model|\Illuminate\Database\Eloquent\Builder|null
     */
    public function query_to_eloquent(
        array $query,
        Model|\Illuminate\Database\Eloquent\Builder $eloquent = null,
        array $relations = [],
        array $order_by = ['id' => 'DESC']
    ): Model|\Illuminate\Database\Eloquent\Builder|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where(function ($q) use ($query){
                $q
                    ->where('user_name', 'LIKE', "%{$query['search']}%")
                    ->orWhere('first_name', 'LIKE', "%{$query['search']}%")
                    ->orWhere('last_name', 'LIKE', "%{$query['search']}%");
            });
        }

        if (isset($query['is_primary']))
            $eloquent = $eloquent->where('is_primary', $query['is_primary']);

        return $eloquent;
    }

    /**
     * @param string $id
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    public function delete_by_id(string $id, array $query = [], callable $deleting = null): bool|int|null
    {
        if (is_null($deleting))
        {
            $deleting = function ($eloquent)
            {
                $admin = $this->get_first_by_eloquent($eloquent);

                if ($admin->is_primary)
                {
                    throw new CustomException('primary accounts can not be deleted', 11, 400);
                }
            };
        }

        return parent::delete_by_id($id, $query, $deleting);
    }
}
