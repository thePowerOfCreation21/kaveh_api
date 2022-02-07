<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Exceptions\CustomException;
use App\Models\NotificationFrame;
use Illuminate\Http\Request;

class NotificationFrameAction extends Action
{
    protected $validation_roles = [
        'store' => [
            'text' => 'required|string|max:1500'
        ],
        'update' => [
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
}
