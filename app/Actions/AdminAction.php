<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

class AdminAction extends Action
{
    protected $validation_roles = [
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
            'privileges' => 'array|max:'.count(Admin::$privileges_list)
        ];

        $this->validation_roles['update'] = [
            'user_name' => 'string|max:25',
            'password' => 'string',
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
     * @param string $validation_role
     * @return Admin
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'store'): Admin
    {
        $data = $this->get_data_from_request($request, $validation_role);

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
     * @param Request $request
     * @param string $id
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function update_entity_by_request_and_id (Request $request, string $id, $validation_role = 'update'): Model
    {
        return $this->update_by_id(
            $this->get_data_from_request($request, $validation_role),
            $id
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
        $admin = $this->get_by_id($id);

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

        $admin->update($update_data);

        return $admin;
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
     * converts query to laravel eloquent
     * filters by: (parent filters) + search
     *
     * @param array $query
     * @param null $eloquent
     * @return Model|Builder
     */
    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('user_name', 'LIKE', "%{$query['search']}%");
        }

        return $eloquent;
    }
}
