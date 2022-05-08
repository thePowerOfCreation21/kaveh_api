<?php

namespace App\Actions;

use App\Services\Action;
use App\Exceptions\CustomException;
use App\Models\InformativeProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class InformativeProductCategoryAction extends Action
{
    protected $validation_roles = [
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
     * @param string|array $validation_role
     * @return Model|mixed
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'store')
    {
        return parent::store_by_request($request, $validation_role);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return bool|int
     * @throws CustomException
     */
    public function update_entity_by_request_and_id(Request $request, string $id)
    {
        return parent::update_entity_by_request_and_id($request, $id);
    }

    /**
     * @param string $id
     * @return bool|int|null
     */
    public function delete_by_id(string $id)
    {
        return parent::delete_by_id($id);
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
     * @param $eloquent
     * @param array $order_by
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

    public function get_by_id(string $id)
    {
        $category = $this->query_to_eloquent(['id' => $id], null, true)->first();

        if (empty($category))
        {
            throw new CustomException("category not found", 110, 404);
        }

        return $category;
    }

    /**
     * @param array $query
     * @param $eloquent
     * @param bool $with_products
     * @return Model|Builder|null
     */
    public function query_to_eloquent(array $query, $eloquent = null, bool $with_products = false)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if ($with_products)
        {
            $eloquent = $eloquent->with('products');
        }

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('title', 'LIKE', "%{$query['search']}%");
        }

        return $eloquent;
    }
}
