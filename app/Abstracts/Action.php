<?php

namespace App\Abstracts;

use App\Exceptions\CustomException;
use App\Services\PaginationService;
use Illuminate\Http\Request;

abstract class Action
{
    protected $model = null;

    protected $validation_roles = [];

    public function get_data_from_request (Request $request, $validation_role, bool $throw_exception = true)
    {
        return $request->validate(
            $this->get_validation_role($validation_role, $throw_exception)
        );
    }

    public function store_by_request (Request $request, $validation_role = 'store')
    {
        $data = $this->get_data_from_request($request, $validation_role);

        $data = $this->change_request_data_before_store_or_update($data, $request);

        return $this->store($data);
    }

    public function store (array $data)
    {
        return $this->model::create($data);
    }

    public function get_validation_role ($validation_role, bool $throw_exception = true)
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
                        "validation role '{$validation_role}' is not set for ".get_class($this),
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

    public function change_request_data_before_store_or_update (array $data, Request $request, $eloquent = null): array
    {
        return $data;
    }

    public function get_by_request (Request $request, $query_validation_role = 'get_query')
    {
        return PaginationService::paginate_with_request(
            $request,
            $this->query_to_eloquent(
                $this->get_data_from_request($request, $query_validation_role, false)
            )->orderBy('id', 'DESC')
        );
    }

    public function query_to_eloquent (array $query, $eloquent = null)
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

    public function get_entity (array $query)
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

    public function get_by_id (string $id)
    {
        return $this->get_entity(['id' => $id]);
    }

    public function delete (array $query)
    {
        return $this->query_to_eloquent($query)->delete();
    }

    public function delete_by_id (string $id)
    {
        return $this->delete(['id' => $id]);
    }

    public function update_by_request (Request $request, array $query = [], $eloquent = null, $validation_role = 'update')
    {
        $data = $this->get_data_from_request($request, $validation_role);
        $data = $this->change_request_data_before_store_or_update($data, $request, $eloquent);
        return $this->update($data, $query, $eloquent);
    }

    public function update (array $data, array $query = [], $eloquent = null)
    {
        return $this->query_to_eloquent($query, $eloquent)->update($data);
    }

    public function update_entity_by_request_and_id (Request $request, string $id)
    {
        $entity = $this->get_by_id($id);
        return $this->update_by_request($request, [], $entity);
    }
}
