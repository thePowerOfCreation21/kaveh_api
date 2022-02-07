<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\License;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class LicenseAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'title' => 'required|string|max:255',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif|max:10000'
        ],
        'update' => [
            'title' => 'string|max:255',
            'image' => 'file|mimes:png,jpg,jpeg,gif|max:10000'
        ]
    ];

    public function __construct()
    {
        $this->model = License::class;
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
     * @param null|Model|Builder $eloquent
     * @param array $order_by
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
     * @param null|Model|Builder $eloquent
     * @return array
     */
    public function change_request_data_before_store_or_update (array $data, Request $request, $eloquent = null): array
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
     * @param string $id
     * @return bool|null
     * @throws CustomException
     */
    public function delete_by_id(string $id)
    {
        $entity = $this->get_by_id($id);

        if (is_file($entity->image))
        {
            unlink($entity->image);
        }

        return $entity->delete();
    }
}
