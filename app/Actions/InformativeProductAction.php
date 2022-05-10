<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Models\InformativeProduct;
use App\Models\RandomInformativeProductUpdateTime;
use App\Services\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class InformativeProductAction extends Action
{
    protected array $validation_roles = [
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
            'timed_randoms' => 'in:1,0,true,false',
            'search' => 'string|max:100'
        ]
    ];

    protected array $unusual_fields = [
        'timed_randoms' => 'boolean',
        'image' => 'file'
    ];

    public function __construct()
    {
        $this->model = InformativeProduct::class;
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
        if (is_null($updating))
        {
            $updating = function($eloquent, $update_data)
            {
                $entity = $this->get_first_by_eloquent($eloquent);

                if (isset($update_data['image']))
                {
                    if (is_file($entity->getAttribute('image')))
                    {
                        unlink($entity->getAttribute('image'));
                    }
                }
            };
        }
        return parent::update_by_request_and_id($request, $id, $validation_role, $updating);
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
    public function get_by_request(Request $request, array|string $validation_role = 'get_query', array $query_addition = [], Model|Builder $eloquent = null, array $relations = ['category'], array $order_by = ['id' => 'DESC']): object
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
    public function get_by_id(string $id, array $query = [], array $relations = ['category']): mixed
    {
        return parent::get_by_id($id, $query, $relations);
    }

    /**
     * @param array $query
     * @param Model|Builder|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return Model|Builder|null
     * @throws CustomException
     */
    public function query_to_eloquent(array $query, Model|Builder $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): Model|Builder|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent, $relations, $order_by);

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('title', 'LIKE', "%{$query['search']}%");
        }

        if (isset($query['timed_randoms']))
        {
            if ($query['timed_randoms'])
            {
                $eloquent = $this->addTimedRandomsToEloquent($eloquent);
            }
            else
            {
                $eloquent = $eloquent->where('is_in_index', false);
            }
        }

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
        return parent::delete_by_id($id, $query, $deleting);
    }

    /**
     * @param Model|Builder $eloquent
     * @param callable|null $deleting
     * @return mixed
     */
    public function delete_by_eloquent(Model|Builder $eloquent, callable $deleting = null): mixed
    {
        if (is_null($deleting))
        {
            $deleting = function($eloquent)
            {
                foreach($eloquent->get() AS $entity)
                {
                    if (is_file($entity->getAttribute('image')))
                    {
                        unlink($entity->getAttribute('image'));
                    }
                }
            };
        }
        return parent::delete_by_eloquent($eloquent, $deleting);
    }

    /**
     * @param $eloquent
     * @return mixed
     * @throws CustomException
     */
    public function addTimedRandomsToEloquent ($eloquent): mixed
    {
        if ((new RandomInformativeProductUpdateTime())->is_time_to_update())
        {
            $this->updateTimedRandoms();
        }

        return $eloquent->where('is_in_index', true);
    }

    /**
     * @return void
     * @throws CustomException
     */
    public function updateTimedRandoms ()
    {
        $this->model::query()->update([
            'is_in_index' => false
        ]);

        $this->model::inRandomOrder()
            ->limit(4)
            ->update([
                'is_in_index' => true
            ]);

        (new RandomInformativeProductUpdateTime())->update((object) [
            'time' => date('Y-m-d H:i:s')
        ]);
    }
}
