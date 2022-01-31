<?php

namespace App\Traits;

use App\Exceptions\CustomException;
use Illuminate\Http\Request;

trait CustomModel
{
    public function store_by_request (Request $request, $validation_role = 'store')
    {
        if (is_string($validation_role))
        {
            if (isset($this->validation_roles[$validation_role]))
            {
                $validation_role = $this->validation_roles[$validation_role];
            }
            else
            {
                throw new CustomException(
                    "validation role '{$validation_role}' is not set for ".get_class($this),
                    65, 500
                );
            }
        }

        $data = $request->validate($validation_role);

        $data = $this->before_store_or_update($data, $request);

        return $this->create($data);
    }

    public function before_store_or_update (array $data, Request $request): array
    {
        return $data;
    }
}
