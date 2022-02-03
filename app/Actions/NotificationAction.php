<?php

namespace App\Actions;

use App\Abstracts\Action;
use App\Jobs\SendSMSToUsers;
use App\Jobs\StoreNotificationUsers;
use App\Services\PaginationService;
use App\Services\SendSMSService;
use Illuminate\Http\Request;
use App\Models\Notification;
use function App\Helpers\convert_to_boolean;

class NotificationAction extends Action
{
    protected $validation_roles = [
        'send' => [
            'type' => 'required|in:message,toast,sms',
            'text' => 'required|string|max:1500',
            'users' => 'array|max:1000',
            'users.*' => 'numeric|max:25'
        ],
        'get_query' => [
            'type' => 'in:message,toast',
            'search' => 'string|max:100',
            'user_id' => 'numeric|max:11'
        ],
        'get_users_query' => [
            'is_seen' => 'in:true,false',
            'search' => 'string|max:100'
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

        if ($data['type'] == 'sms')
        {
            SendSMSToUsers::dispatch(
                $data['text'],
                $data['users'] ?? "*"
            );

            return [
                'code' => 73,
                'message' => 'sms will be sent (it is now in queue)'
            ];
        }

        $notification = $this->model::create($data);

        if (!$data['is_for_all_users'])
        {
            StoreNotificationUsers::dispatch($notification->id, $data['users']);
        }

        return $notification;
    }

    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        if (isset($query['type']))
        {
            $eloquent = $eloquent->where('type', $query['type']);
        }

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('text', 'LIKE', "%{$query['search']}%");
        }

        if (isset($query['user_id']))
        {
            $eloquent = $eloquent->select("notifications.*")
                ->leftJoin('notification_users', function ($join){
                    $join->on('notifications.id', 'notification_users.notification_id');
                })
                ->where("notifications.is_for_all_users", true)
                ->orWhere("notification_users.user_id", $query['user_id']);
        }

        return $eloquent;
    }

    public function get_users_by_request_and_id (Request $request, $id)
    {
        $notification = $this->get_by_id($id);

        return $this->get_users_by_request(
            $request,
            $notification->users()
        );
    }

    public function get_users_by_request (
        Request $request,
        $eloquent,
        $query_validation_role = 'get_users_query',
        $order_by = ['user_id' => 'DESC']
    )
    {
        $eloquent = $this->users_query_to_eloquent(
            $this->get_data_from_request($request, $query_validation_role),
            $eloquent
        );

        $eloquent = $this->add_order_to_eloquent($order_by, $eloquent);

        return PaginationService::paginate_with_request(
            $request,
            $eloquent
        );
    }

    public function users_query_to_eloquent (array $query, $eloquent)
    {
        $eloquent = UserActions::query_to_eloquent($query, $eloquent);

        if (isset($query['is_seen']))
        {
            $query['is_seen'] = convert_to_boolean($query['is_seen']);

            if ($query['is_seen'])
            {
                $eloquent = $eloquent->where('is_seen', $query['is_seen']);
            }
            else
            {
                $eloquent = $eloquent->where('is_seen', "=", null)->orWhere('is_seen', false);
            }
        }

        return $eloquent;
    }
}
