<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Models\InformativeProductCategory;
use App\Services\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class InformativeProductCategoryAction extends Action
{
    protected array $validation_roles = [
        'store' => [
            'title' => 'required|string|max:255'
        ],
        'update' => [
            'title' => 'required|string|max:255'
        ],
        'get_query' => [
            'search' => 'string|max:255'
        ]
    ];

    public function __construct()
    {
        $this->model = InformativeProductCategory::class;
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param callable|null $storing
     * @return mixed
     * @throws CustomException
     */
    public function store_by_request(Request $request, array|string $validation_role = 'store', callable $storing = null): mixed
    {
        return parent::store_by_request($request, $validation_role, $storing);
    }

    /**
     * @param Request $request
     * @param string $id
     * @param array|string $validation_role
     * @param callable|null $updating
     * @return int|bool
     * @throws CustomException
     */
    public function update_by_request_and_id(Request $request, string $id, array|string $validation_role = 'update', callable $updating = null): int|bool
    {
        return parent::update_by_request_and_id($request, $id, $validation_role, $updating);
    }

    /**
     * @param string $id
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    public function delete_by_id(string $id, array $query = [], callable $deleting = null): bool|int|null
    {
        return parent::delete_by_id($id, $query, $deleting);
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param Model|Builder|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_by_request(Request $request, array|string $validation_role = 'get_query', array $query_addition = [], Model|Builder $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): object
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
    public function get_by_id(string $id, array $query = [], array $relations = ['products']): mixed
    {
        return parent::get_by_id($id, $query, $relations);
    }

    /**
     * @param array $query
     * @param Model|Builder|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return Model|Builder|null
     */
    public function query_to_eloquent(array $query, Model|Builder $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): Model|Builder|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent, $relations, $order_by);

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('title', 'LIKE', "%{$query['search']}%");
        }

        return $eloquent;
    }
}
