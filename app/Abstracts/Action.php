<?php

namespace App\Abstracts;

use App\Exceptions\CustomException;
use Illuminate\Http\Request;

abstract class Action
{
    protected $model = null;

    protected $validation_roles = [];

    public function get_data_from_request (Request $request, $validation_role)
    {
        return $request->validate(
            $this->get_validation_role($validation_role)
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

    public function get_validation_role ($validation_role)
    {
        if (is_string($validation_role))
        {
            if (isset($this->validation_roles[$validation_role]))
            {
                return $this->validation_roles[$validation_role];
            }
            else
            {
                throw new CustomException(
                    "validation role '{$validation_role}' is not set for ".get_class($this),
                    65, 500
                );
            }
        }
        else if (is_array($validation_role))
        {
            return $validation_role;
        }

        throw new CustomException(
            "wrong validation role passed to ".get_class($this),
            66, 500
        );
    }

    public function change_request_data_before_store_or_update (array $data, Request $request): array
    {
        return $data;
    }
}
