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
}
