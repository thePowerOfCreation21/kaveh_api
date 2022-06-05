<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Models\OrderTimeLimit;
use App\Models\Product;
use App\Services\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use function App\Helpers\convert_to_boolean;

class ProductAction extends Action
{
    protected array $validation_roles = [
        'store' => [
            'title' => 'required|string|max:128',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000',
            'description' => 'string|max:500',
            'price' => 'required|numeric|min:1|max:1000000',
            'discount_percentage' => 'numeric|min:0|max:100',
            'type' => 'required|in:limited,unlimited',
            'stock' => 'required_if:type,==,limited|numeric|min:0|max:100000'
        ],
        'update' => [
            'title' => 'string|max:128',
            'image' => 'file|mimes:png,jpg,jpeg,gif|max:10000',
            'description' => 'string|max:500',
            'before_discount_price' => 'integer|min:1|max:1000000',
            'price' => 'numeric|min:1|max:1000000',
            'discount_percentage' => 'numeric|min:0|max:99',
            'type' => 'in:limited,unlimited',
            'stock' => 'numeric|min:0|max:100000'
        ],
        'get_query' => [
            'search' => 'string|max:128',
            'type' => 'in:limited,unlimited',
            'can_be_ordered_now' => 'in:true,false'
        ]
    ];

    protected array $unusual_fields = [
        'image' => 'file'
    ];

    public function __construct()
    {
        $this->model = Product::class;
    }

    /**
     * @param array $data
     * @param callable|null $storing
     * @return mixed
     */
    public function store(array $data, callable $storing = null): mixed
    {
        $data['before_discount_price'] = $data['price'];

        if (isset($data['discount_percentage']))
        {
            $data['price'] = $data['before_discount_price'] - (($data['before_discount_price'] / 100) * $data['discount_percentage']);
        }

        return parent::store($data, $storing);
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
        return parent::get_by_request($request, $validation_role, $query_addition, $eloquent, $relations, $order_by);
    }

    /**
     * @param array $query
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object|null
     * @throws CustomException
     */
    public function query_to_eloquent(array $query, object $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): object|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent, $relations, $order_by);

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
     * @param string $id
     * @param array|string $validation_role
     * @param callable|null $updating
     * @return int|bool
     * @throws CustomException
     */
    public function update_by_request_and_id(Request $request, string $id, array|string $validation_role = 'update', callable $updating = null): int|bool
    {
        if (is_null($updating))
        {
            $updating = function ($eloquent, &$update_data)
            {
                $product = $this->get_first_by_eloquent($eloquent);

                if (isset($update_data['image']) && is_file($product->getAttribute('image')))
                {
                    unlink($product->getAttribute('image'));
                }

                if ($product->getAttribute('type') == 'unlimited' && isset($update_data['type']) && $update_data['type'] == 'limited')
                {
                    $update_data['stock'] = $update_data['stock'] ?? max($product->getAttributes()['stock'], 0);
                }

                if (isset($update_data['price']))
                {
                    $update_data['before_discount_price'] = $update_data['price'];
                }

                if (isset($update_data['discount_percentage']))
                {
                    $update_data['before_discount_price'] = $update_data['before_discount_price'] ?? $data['before_discount_price'] ?? $product->getAttribute('before_discount_price');

                    if ($update_data['before_discount_price'] < 1)
                    {
                        $update_data['before_discount_price'] = $update_data['price'] ?? $product->getAttribute('price');
                    }

                    $update_data['price'] = $update_data['before_discount_price'] - (($update_data['before_discount_price'] / 100) * $update_data['discount_percentage']);
                }
            };
        }
        return parent::update_by_request_and_id($request, $id, $validation_role, $updating);
    }
}
