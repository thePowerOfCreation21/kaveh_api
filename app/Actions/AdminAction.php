<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class AdminAction extends Action
{
    protected $validation_roles = [
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

        foreach (Admin::$privileges_list as $privilege)
        {
            $this->validation_roles['store']["privileges.$privilege"] = 'boolean';
        }

        $this->model = Admin::class;
    }

    public function get_data_from_request(Request $request, $validation_role, bool $throw_exception = true): array
    {
        $data = parent::get_data_from_request($request, $validation_role, $throw_exception);

        $data['privileges'] = Admin::fix_privileges(
            (object) (!isset($data['privileges']) ? [] : $data['privileges'])
        );

        return $data;
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
