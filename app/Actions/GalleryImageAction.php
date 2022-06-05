<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Models\GalleryImage;
use App\Services\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class GalleryImageAction extends Action
{
    protected array $validation_roles = [
        'store' => [
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000'
        ]
    ];

    protected array $unusual_fields = [
        'image' => 'file'
    ];

    public function __construct()
    {
        $this->model = GalleryImage::class;
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
            $updating = function ($eloquent, $update_data)
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
     * @param string $id
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    public function delete_by_id(string $id, array $query = [], callable $deleting = null): bool|int|null
    {
        return parent::delete_by_id($id, $query, $deleting);
    }
}
