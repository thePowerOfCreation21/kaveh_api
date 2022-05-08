<?php

namespace App\Actions;

use App\Services\Action;
use App\Exceptions\CustomException;
use App\Models\NotificationFrame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class NotificationFrameAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'title' => 'string|max:255',
            'text' => 'required|string|max:1500'
        ],
        'update' => [
            'title' => 'string|max:255',
            'text' => 'required|string|max:1500'
        ]
    ];

    public function __construct(){
        $this->model = NotificationFrame::class;
    }

    /**
     * @param Request $request
     * @param string $validation_role
     * @return mixed
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'store')
    {
        return parent::store_by_request($request, $validation_role);
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
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
     * @param string $id
     * @return bool|int|null
     */
    public function delete_by_id(string $id)
    {
        return parent::delete_by_id($id);
    }
}
