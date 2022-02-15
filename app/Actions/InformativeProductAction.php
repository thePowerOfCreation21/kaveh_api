<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\InformativeProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class InformativeProductAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'category_id' => 'exists:informative_product_categories,id',
            'title' => 'required|string|max:128',
            'image' => 'required|file|mimes:png,jpg,jpeg|max:10000',
            'description' => 'string|max:1500'
        ],
        'update' => [
            'category_id' => 'exists:informative_product_categories,id',
            'title' => 'string|max:128',
            'image' => 'file|mimes:png,jpg,jpeg|max:10000',
            'description' => 'string|max:1500'
        ],
        'get_query' => [
            'search' => 'string|max:100'
        ]
    ];

    public function __construct()
    {
        $this->model = InformativeProduct::class;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return mixed
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
     * @param Request $request
     * @param string $query_validation_role
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
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    public function get_by_id(string $id): Model
    {
        return parent::get_by_id($id);
    }

    /**
     * @param array $data
     * @param Request $request
     * @param null $eloquent
     * @return array
     */
    public function change_request_data_before_store_or_update(array $data, Request $request, $eloquent = null): array
    {
        if (!empty($request->file('image')))
        {
            $data['image'] = $request->file('image')->store('/uploads');

            if (isset($eloquent->image) && is_file($eloquent->image))
            {
                unlink($eloquent->image);
            }
        }

        return $data;
    }

    /**
     * @param array $query
     * @param null $eloquent
     * @return mixed|null
     */
    public function query_to_eloquent(array $query, $this_eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $this_eloquent);

        if (is_null($this_eloquent))
        {
            $eloquent = $eloquent->with('category');

            if (isset($query['search']))
            {
                $eloquent = $eloquent->where('title', 'LIKE', "%{$query['search']}%");
            }
        }

        return $eloquent;
    }

    /**
     * @param string $id
     * @return bool|null
     * @throws CustomException
     */
    public function delete_by_id(string $id): ?bool
    {
        $entity = $this->get_by_id($id);

        if (isset($entity->image) && is_file($entity->image))
        {
            unlink($entity->image);
        }

        return $entity->delete();
    }
}
