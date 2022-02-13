<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\InformativeProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class InformativeProductCategoryAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'title' => 'required|string|max:255'
        ]
    ];

    public function __construct()
    {
        $this->model = InformativeProductCategory::class;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return Model|mixed
     * @throws CustomException
     */
    public function store_by_request(Request $request, $validation_role = 'store')
    {
        return parent::store_by_request($request, $validation_role);
    }

    /**
     * @param Request $request
     * @param string|array $query_validation_role
     * @param $eloquent
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
}
