<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Jobs\StoreNotificationUsers;
use App\Services\SendSMSService;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationAction extends Action
{
    protected $validation_roles = [
        'send' => [
            'type' => 'required|in:message,toast,sms',
            'text' => 'required|string|max:1500',
            'users' => 'array|max:1000',
            'users.*' => 'numeric|max:25'
        ]
    ];

    public function __construct()
    {
        $this->model = Notification::class;
    }

    public function send_by_request (Request $request, $validation_role = 'send')
    {
        $data = $this->get_data_from_request($request, $validation_role);

        return $this->send($data);
    }

    public function send (array $data)
    {
        $data['is_for_all_users'] = true;

        if (isset($data['users']))
        {
            $data['is_for_all_users'] = false;
            UserActions::check_if_users_exists($data['users']);
        }

        $notification = $this->model::create($data);

        if (!$data['is_for_all_users'])
        {
            StoreNotificationUsers::dispatch($notification->id, $data['users']);
        }

        return $notification;
    }
}
