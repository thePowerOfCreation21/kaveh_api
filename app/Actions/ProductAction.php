<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\OrderTimeLimit;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use function App\Helpers\convert_to_boolean;

class ProductAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'title' => 'required|string|max:128',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000',
            'description' => 'string|max:500',
            'price' => 'required|numeric|min:1|max:1000000',
            'discount_percentage' => 'numeric|min:0|max:99',
            'type' => 'required|in:limited,unlimited',
            'stock' => 'required_if:type,==,limited|numeric|min:0|max:100000'
        ],
        'get_query' => [
            'search' => 'string|max:128',
            'type' => 'in:limited,unlimited',
            'can_be_ordered_now' => 'in:true,false'
        ]
    ];

    protected $unusual_fields = [
        'image' => 'file'
    ];

    public function __construct()
    {
        $this->model = Product::class;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'store'): Model
    {
        return parent::store_by_request($request, $validation_role);
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
     * @param null $eloquent
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
     * @throws CustomException
     */
    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('title', 'LIKE', "%{$query['search']}%");
        }

        if (isset($query['type']))
        {
            $eloquent = $eloquent->where('type', $query['type']);
        }

        if (isset($query['can_be_ordered_now']))
        {
            $available_types = (new OrderTimeLimit())->get_available_groups();
            $query['can_be_ordered_now'] = convert_to_boolean($query['can_be_ordered_now']);

            if ($query['can_be_ordered_now'])
            {
                $eloquent = $eloquent->whereIn('type', $available_types);
            }
            else
            {
                $eloquent = $eloquent->whereNotIn('type', $available_types);
            }
        }

        return $eloquent;
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
}
