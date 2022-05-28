<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use function App\Helpers\convert_to_boolean;

abstract class Action
{
    protected string $model;

    protected array $validation_roles = [];

    protected array $unusual_fields = [];

    /**
     * @param Request $request
     * @return User|mixed
     * @throws CustomException
     */
    protected function get_user_from_request(Request $request): mixed
    {
        $user = $request->user();

        if (empty($user)) {
            throw new CustomException("could not get user from request", 100, 500);
        }

        if (!is_a($user, User::class)) {
            throw new CustomException("user should be instance of " . User::class, 101, 500);
        }

        return $user;
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $options
     * options: bool throw_exception (default value: true)
     * @return array
     * @throws CustomException
     */
    protected function get_data_from_request(Request $request, array|string $validation_role, array $options = []): array
    {
        $data = $request->validate(
            $this->get_validation_role(
                $validation_role,
                $options['throw_exception'] ?? true
            )
        );

        /*
        foreach ($data AS $attribute => $value)
        {
            $data[$attribute] = $request->input($attribute);
        }
        */

        return $this->manage_unusual_fields($data);
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function upload_file(UploadedFile $file): string
    {
        return $file->store('/uploads');
    }

    /**
     * list of unusual field types: file, boolean
     *
     * @param array $data
     * @return array
     * @throws CustomException
     */
    protected function manage_unusual_fields(array $data): array
    {
        foreach ($this->unusual_fields as $unusual_field => $unusual_field_options) {
            $unusual_field_options = explode(':', $unusual_field_options);

            $unusual_field_options = [
                'type' => $unusual_field_options[0],
                'configs' => $unusual_field_options[1] ?? null,
            ];

            if (!isset($data[$unusual_field])) {
                continue;
            }

            switch ($unusual_field_options['type']) {
                case 'file':
                    if (is_a($data[$unusual_field], UploadedFile::class) && !empty($data[$unusual_field])) {
                        $data[$unusual_field] = $this->upload_file(
                            $data[$unusual_field]
                        );
                    }
                    break;
                case 'boolean':
                    $data[$unusual_field] = convert_to_boolean($data[$unusual_field]);
                    break;
                case 'regex':
                    $data[$unusual_field] = $this->check_regex($data[$unusual_field], $unusual_field_options['configs'], $unusual_field);
            }

        }

        return $data;
    }

    /**
     * @param string $string
     * @param string $regex
     * @param string|null $field_name
     * @return string
     * @throws CustomException
     */
    protected function check_regex(string $string, string $regex, string $field_name = null): string
    {
        preg_match($regex, $string, $matches);

        if (empty($matches)) {
            throw new CustomException("could not match $field_name with required regex pattern", 30, 400);
        }

        return $matches[0];
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param callable|null $storing
     * @return Mixed|Model
     * @throws CustomException
     */
    protected function store_by_request(Request $request, array|string $validation_role = 'store', callable $storing = null): mixed
    {
        $data = $this->get_data_from_request($request, $validation_role);

        return $this->store($data, $storing);
    }

    /**
     * @param array $data
     * @param callable|null $storing
     * @return Model|Mixed
     */
    protected function store(array $data, callable $storing = null): mixed
    {
        if (is_callable($storing))
        {
            $storing($data);
        }
        return $this->model::create($data);
    }

    /**
     * @param array|string $validation_role
     * @param bool $throw_exception
     * @return array|mixed
     * @throws CustomException
     */
    protected function get_validation_role(array|string $validation_role, bool $throw_exception = true): mixed
    {
        if (is_string($validation_role)) {
            if (isset($this->validation_roles[$validation_role])) {
                return $this->validation_roles[$validation_role];
            } else {
                if ($throw_exception) {
                    throw new CustomException(
                        "validation role '$validation_role' is not set for " . get_class($this),
                        65, 500
                    );
                }
                return [];
            }
        } else if (is_array($validation_role)) {
            return $validation_role;
        }

        if ($throw_exception) {
            throw new CustomException(
                "wrong validation role passed to " . get_class($this),
                66, 500
            );
        }
        return [];
    }

    /**
     * @param Request $request
     * @param array|string $validation_role
     * @param array $query_addition
     * @param Builder|Model|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    protected function get_by_request(
        Request       $request,
        array|string  $validation_role = 'get_query',
        array         $query_addition = [],
        Model|Builder $eloquent = null,
        array         $relations = [],
        array         $order_by = ['id' => 'DESC']
    ): object
    {
        $eloquent = $this->query_to_eloquent(
            array_merge(
                $this->get_data_from_request($request, $validation_role, [
                    'throw_exception' => false
                ]),
                $query_addition
            ),
            $eloquent,
            $relations,
            $order_by
        );

        return (new PaginationService())->paginate_with_request(
            $request,
            $eloquent
        );
    }

    /**
     * @param array $orders
     * @param Builder|Model $eloquent
     * @return mixed
     */
    protected function add_order_to_eloquent(array $orders, Model|Builder $eloquent): mixed
    {
        foreach ($orders as $key => $value) {
            $eloquent = $eloquent->orderBy($key, $value);
        }
        return $eloquent;
    }

    /**
     * converts query to laravel eloquent
     * filters by: id
     *
     * @param array $query
     * @param Builder|Model|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return Model|Builder|null
     */
    protected function query_to_eloquent(array $query, Model|Builder $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): Model|Builder|null
    {
        if (is_null($eloquent)) {
            $eloquent = new $this->model();
        }

        foreach ($relations as $relation) {
            $eloquent = $eloquent->with($relation);
        }

        if (isset($query['id'])) {
            $eloquent = $eloquent->where('id', $query['id']);
        }

        return $this->add_order_to_eloquent($order_by, $eloquent);
    }

    /**
     * @param string $id
     * @param array $query
     * @param array $relations
     * @return Model|Mixed
     * @throws CustomException
     */
    protected function get_by_id(string $id, array $query = [], array $relations = []): mixed
    {
        $eloquent = $this->query_to_eloquent($query, relations: $relations);
        return $this->get_first_by_eloquent($eloquent->where('id', $id));
    }

    /**
     * @param Model|Builder $eloquent
     * @return Model|Builder
     * @throws CustomException
     */
    protected function get_first_by_eloquent(Model|Builder $eloquent): Model|Builder
    {
        $entity = $eloquent->first();

        if (empty($entity)) {
            throw new CustomException(
                "could not find requested $this->model",
                84,
                404
            );
        }

        return $entity;
    }

    /**
     * @param string $field
     * @param string $value
     * @return Model|Mixed
     * @throws CustomException
     */
    protected function get_by_field(string $field, string $value): mixed
    {
        $entity = $this->query_to_eloquent([])->where($field, $value)->first();

        if (empty($entity)) {
            throw new CustomException(
                "could not find $field with value $value in $this->model",
                84,
                404
            );
        }

        return $entity;
    }

    /**
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    protected function delete_by_query(array $query, callable $deleting = null): mixed
    {
        return $this->delete_by_eloquent(
            $this->query_to_eloquent($query, relations: []),
            $deleting
        );
    }

    /**
     * @param Model|Builder $eloquent
     * @param callable|null $deleting
     * @return bool|mixed|null
     */
    protected function delete_by_eloquent(Model|Builder $eloquent, callable $deleting = null): mixed
    {
        if (is_callable($deleting)) {
            $deleting($eloquent);
        }

        return $eloquent->delete();
    }

    /**
     * @param string $id
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    protected function delete_by_id(string $id, array $query = [], callable $deleting = null): bool|int|null
    {
        return $this->delete_by_query(array_merge(['id' => $id], $query), $deleting);
    }

    /**
     * @param Model|Builder $eloquent
     * @param array $update_data
     * @param callable|null $updating
     * @return bool|int
     */
    protected function update_by_eloquent (Model|Builder $eloquent, array $update_data, callable $updating = null): bool|int
    {
        if (is_callable($updating))
        {
            $updating($eloquent, $update_data);
        }
        return $eloquent->update($update_data);
    }

    /**
     * @param array $query
     * @param array $update_data
     * @param callable|null $updating
     * @return bool|int
     */
    protected function update_by_query (array $query, array $update_data, callable $updating = null): bool|int
    {
        return $this->update_by_eloquent(
            $this->query_to_eloquent($query, relations: []),
            $update_data,
            $updating
        );
    }

    /**
     * @param Request $request
     * @param array $query
     * @param array|string $validation_role
     * @param callable|null $updating
     * @return bool|int
     * @throws CustomException
     */
    protected function update_by_request_and_query (Request $request, array $query = [], array|string $validation_role = 'update', callable $updating = null): bool|int
    {
        return $this->update_by_query(
            $query,
            $this->get_data_from_request($request, $validation_role),
            $updating
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @param array|string $validation_role
     * @param callable|null $updating
     * @return int|bool
     * @throws CustomException
     */
    protected function update_by_request_and_id (Request $request, string $id, array|string $validation_role = 'update', callable $updating = null): int|bool
    {
        return $this->update_by_request_and_query($request, ['id' => $id], $validation_role, $updating);
    }
}
