<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Models\NotificationFrame;

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
}
