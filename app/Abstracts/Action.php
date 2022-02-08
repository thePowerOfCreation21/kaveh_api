<?php

namespace App\Abstracts;

use App\Exceptions\CustomException;
use App\Services\PaginationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

abstract class Action
{
    protected $model = null;

    protected $validation_roles = [];

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @param array $options
     * options: bool throw_exception (default value: true)
     * @return array
     * @throws CustomException
     */
    protected function get_data_from_request (Request $request, $validation_role, array $options = []): array
    {
        return $request->validate(
            $this->get_validation_role(
                $validation_role,
                $options['throw_exception'] ?? true
            )
        );
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return Model
     * @throws CustomException
     */
    protected function store_by_request (Request $request, $validation_role = 'store'): Model
    {
        $data = $this->get_data_from_request($request, $validation_role);

        $data = $this->change_request_data_before_store_or_update($data, $request);

        return $this->store($data);
    }

    /**
     * @param array $data
     * @return Model
     */
    protected function store (array $data): Model
    {
        return $this->model::create($data);
    }

    /**
     * @param string|array $validation_role
     * @param bool $throw_exception
     * @return array|mixed
     * @throws CustomException
     */
    protected function get_validation_role ($validation_role, bool $throw_exception = true)
    {
        if (is_string($validation_role))
        {
            if (isset($this->validation_roles[$validation_role]))
            {
                return $this->validation_roles[$validation_role];
            }
            else
            {
                if ($throw_exception)
                {
                    throw new CustomException(
                        "validation role '$validation_role' is not set for ".get_class($this),
                        65, 500
                    );
                }
                return [];
            }
        }
        else if (is_array($validation_role))
        {
            return $validation_role;
        }

        if ($throw_exception)
        {
            throw new CustomException(
                "wrong validation role passed to ".get_class($this),
                66, 500
            );
        }
        return [];
    }

    /**
     * @param array $data
     * @param Request $request
     * @param null|Builder|Model $eloquent
     * @return array
     */
    protected function change_request_data_before_store_or_update (array $data, Request $request, $eloquent = null): array
    {
        return $data;
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
     * @param null|Model|Builder $eloquent
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    protected function get_by_request (
        Request $request,
        $query_validation_role = 'get_query',
        $eloquent = null,
        array $order_by = ['id' => 'DESC']
    ): object
    {
        $eloquent = $this->query_to_eloquent(
            $this->get_data_from_request($request, $query_validation_role, [
                'throw_exception' => false
            ]),
            $eloquent
        );

        $eloquent = $this->add_order_to_eloquent($order_by, $eloquent);

        return PaginationService::paginate_with_request(
            $request,
            $eloquent
        );
    }

    /**
     * @param array $orders
     * @param Builder|Model $eloquent
     * @return mixed
     */
    protected function add_order_to_eloquent (array $orders, $eloquent)
    {
        foreach ($orders AS $key => $value)
        {
            $eloquent = $eloquent->orderBy($key, $value);
        }
        return $eloquent;
    }

    /**
     * converts query to laravel eloquent
     * filters by: id
     *
     * @param array $query
     * @param null|Builder|Model $eloquent
     * @return Builder|Model
     */
    protected function query_to_eloquent (array $query, $eloquent = null)
    {
        if ($eloquent === null)
        {
            $eloquent = new $this->model();
        }

        if (isset($query['id']))
        {
            $eloquent = $eloquent->where('id', $query['id']);
        }

        return $eloquent;
    }

    /**
     * @param array $query
     * @return Model
     * @throws CustomException
     */
    protected function get_entity (array $query): Model
    {
        $entity = $this->query_to_eloquent($query)->first();

        if (empty($entity))
        {
            throw new CustomException(
                "entity not found",
                67, 404
            );
        }

        return $entity;
    }

    /**
     * @param string $id
     * @return Model
     * @throws CustomException
     */
    protected function get_by_id (string $id): Model
    {
        return $this->get_entity(['id' => $id]);
    }

    /**
     * @param string $field
     * @param string $value
     * @return Model
     * @throws CustomException
     */
    protected function get_by_field (string $field, string $value): Model
    {
        $entity = $this->model::where($field, $value)->first();

        if (empty($entity))
        {
            throw new CustomException(
                "could not find $field with value $value in {$this->model}",
                84,
                404
            );
        }

        return $entity;
    }

    /**
     * @param array $query
     * @return bool|int|null
     */
    protected function delete (array $query)
    {
        return $this->query_to_eloquent($query)->delete();
    }

    /**
     * @param string $id
     * @return bool|int|null
     */
    protected function delete_by_id (string $id)
    {
        return $this->delete(['id' => $id]);
    }

    /**
     * @param Request $request
     * @param array $query
     * @param null $eloquent
     * @param string|array $validation_role
     * @return bool|int
     * @throws CustomException
     */
    protected function update_by_request (Request $request, array $query = [], $eloquent = null, $validation_role = 'update')
    {
        $data = $this->get_data_from_request($request, $validation_role);
        $data = $this->change_request_data_before_store_or_update($data, $request, $eloquent);
        return $this->update($data, $query, $eloquent);
    }

    /**
     * @param array $data
     * @param array $query
     * @param null $eloquent
     * @return bool|int
     */
    protected function update (array $data, array $query = [], $eloquent = null)
    {
        return $this->query_to_eloquent($query, $eloquent)->update($data);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return bool|int
     * @throws CustomException
     */
    protected function update_entity_by_request_and_id (Request $request, string $id)
    {
        $entity = $this->get_by_id($id);
        return $this->update_by_request($request, [], $entity);
    }
}
