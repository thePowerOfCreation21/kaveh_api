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

    protected $unusual_fields = [
        'image' => 'file'
    ];

    public function __construct()
    {
        $this->model = Product::class;
    }

    public function store(array $data)
    {
        $data['before_discount_price'] = $data['price'];

        if (isset($data['discount_percentage']))
        {
            $data['price'] = $data['before_discount_price'] - (($data['before_discount_price'] / 100) * $data['discount_percentage']);
        }

        return parent::store($data);
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
     * @return Product
     * @throws CustomException
     */
    public function get_by_id(string $id): Product
    {
        return parent::get_by_id($id);
    }

    /**
     * @param string $id
     * @return bool
     * @throws CustomException
     */
    public function delete_by_id(string $id): bool
    {
        $product = $this->get_by_field('id', $id);

        if (is_file($product->image))
        {
            unlink($product->image);
        }

        return $product->delete();
    }

    /**
     * @param array $update_data
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function update_by_id (array $update_data, string $id): Model
    {
        $product = $this->get_by_field('id', $id);

        if (isset($update_data['image']) && is_file($product->image))
        {
            unlink($product->image);
        }

        if ($product->type == 'unlimited' && isset($update_data['type']) && $update_data['type'] == 'limited')
        {
            $update_data['stock'] = $update_data['stock'] ?? max($product->getAttributes()['stock'], 0);
        }

        if (isset($update_data['price']))
        {
            $update_data['before_discount_price'] = $update_data['price'];
        }

        if (isset($update_data['discount_percentage']))
        {
            $update_data['before_discount_price'] = $update_data['before_discount_price'] ?? $data['before_discount_price'] ?? $product->before_discount_price;

            if ($update_data['before_discount_price'] < 1)
            {
                $update_data['before_discount_price'] = $update_data['price'] ?? $product->price;
            }

            $update_data['price'] = $update_data['before_discount_price'] - (($update_data['before_discount_price'] / 100) * $update_data['discount_percentage']);
        }

        $product->update($update_data);

        return $product;
    }

    /**
     * @param Request $request
     * @param string $id
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    public function update_entity_by_request_and_id(Request $request, string $id, $validation_role = 'update'): Model
    {
        return $this->update_by_id(
            $this->get_data_from_request($request, $validation_role),
            $id
        );
    }
}
