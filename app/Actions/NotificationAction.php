<?php

namespace App\Actions;

use App\Services\Action;
use App\Exceptions\CustomException;
use App\Jobs\SendSMSToUsers;
use App\Jobs\StoreNotificationUsers;
use App\Models\NotificationUser;
use App\Services\PaginationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
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
        'seen' => [
            'type' => 'in:message,toast'
        ],
        'get_query' => [
            'type' => 'in:message,toast',
            'search' => 'string|max:100',
            'user_id' => 'numeric|max:11'
        ],
        'get_users_query' => [
            'is_seen' => 'in:true,false',
            'search' => 'string|max:100'
        ],
        'get_user_notifications_query' => [
            'type' => 'in:message,toast',
            'is_seen' => 'in:true,false'
        ]
    ];

    protected $unusual_fields = [
        'is_seen' => 'boolean'
    ];

    public function __construct()
    {
        $this->model = Notification::class;
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
     * @param string|array $validation_role
     * @return array|Model
     * @throws CustomException
     */
    public function send_by_request (Request $request, $validation_role = 'send')
    {
        $data = $this->get_data_from_request($request, $validation_role);

        return $this->send($data);
    }

    /**
     * @param string $id
     * @return bool|int|null
     */
    public function delete_by_id(string $id)
    {
        return parent::delete_by_id($id);
    }

    /**
     * @param array $data
     * @return array|Model
     * @throws CustomException
     */
    public function send (array $data)
    {
        $data['is_for_all_users'] = true;

        if (isset($data['users']))
        {
            $data['is_for_all_users'] = false;
            UserAction::check_if_users_exists($data['users']);
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

    /**
     * @param array $query
     * @param null|Model|Builder $eloquent
     * @return Model|Builder
     */
    public function query_to_eloquent(array $query, $eloquent = null)
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent);

        $eloquent = $eloquent->orderBy('id', 'DESC');

        if (isset($query['user_id']))
        {
            $userId = $query['user_id'];
            $eloquent = $eloquent->selectRaw("notifications.*, notification_users.is_seen")
                ->leftJoin('notification_users', function ($join){
                    $join->on('notifications.id', 'notification_users.notification_id');
                })
                ->where(function($q) use ($userId){
                    $q->where("notifications.is_for_all_users", true)
                        ->orWhere("notification_users.user_id", $userId);
                });

            if (isset($query['is_seen']))
            {
                $eloquent = $eloquent->where('notification_users.is_seen', $query['is_seen']);
            }
        }

        if (isset($query['type']))
        {
            $eloquent = $eloquent->where('type', $query['type']);
        }

        if (isset($query['search']))
        {
            $eloquent = $eloquent->where('text', 'LIKE', "%{$query['search']}%");
        }

        return $eloquent;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return void
     * @throws CustomException
     */
    public function seen_by_request (Request $request, $validation_role = 'seen')
    {
        $user = $this->get_user_from_request($request);
        $notifications = $this->query_to_eloquent(
            $this->get_data_from_request($request, $validation_role)
        )->get();

        $this->seen_by_user_id_and_notifications($user->id, $notifications);
    }

    /**
     * @param $notifications
     * @return array
     */
    public function get_ids ($notifications)
    {
        $ids = [];

        foreach ($notifications AS $notification)
        {
            $ids[] = $notification->id;
        }

        return $ids;
    }

    /**
     * @param Request $request
     * @param string $notificationId
     * @return void
     * @throws CustomException
     */
    public function seen_by_request_and_notification_id (Request $request, string $notificationId)
    {
        $user = $this->get_user_from_request($request);
        $notification = $this->get_by_field('id', $notificationId);

        $this->seen_by_user_id_and_notifications($user->id, $notification);
    }

    /**
     * @param string $userId
     * @param $notifications
     * @return void
     */
    public function seen_by_user_id_and_notifications (string $userId, $notifications)
    {
        if (!isset($notifications->id))
        {
            $notificationsId = $this->get_ids($notifications);
        }
        else
        {
            $notificationsId = [$notifications->id];
        }

        NotificationUser::where('user_id', $userId)
            ->whereIn('notification_id', $notificationsId)
            ->delete();

        foreach ($notificationsId AS $notificationId)
        {
            NotificationUser::create([
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'is_seen' => true
            ]);
        }
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return object
     * @throws CustomException
     */
    public function get_user_notifications_by_request (Request $request, $validation_role = 'get_user_notifications_query'): object
    {
        $user = $this->get_user_from_request($request);
        $query = ['user_id' => $user->id];
        $query = array_merge($query, $this->get_data_from_request($request, $validation_role));
        $eloquent = $this->query_to_eloquent($query);
        $result = PaginationService::paginate_with_request(
            $request,
            $eloquent
        );
        $this->seen_by_user_id_and_notifications($user->id, $eloquent->get());
        return $result;
    }

    /**
     * @param Request $request
     * @param string|array $validation_role
     * @return int
     * @throws CustomException
     */
    public function get_user_notifications_count_by_request (Request $request, $validation_role = 'get_user_notifications_query'): int
    {
        $user = $this->get_user_from_request($request);
        $query = ['user_id' => $user->id];
        $query = array_merge($query, $this->get_data_from_request($request, $validation_role));
        return $this->query_to_eloquent($query)->count();
    }

    /**
     * get notification users by request and notification id
     *
     * @param Request $request
     * @param string $id
     * @return object
     * @throws CustomException
     */
    public function get_users_by_request_and_id (Request $request, string $id): object
    {
        $notification = $this->get_by_id($id);

        return $this->get_users_by_request(
            $request,
            $notification->users()
        );
    }

    /**
     * get users by request
     *
     * @param Request $request
     * @param $eloquent
     * @param string|array $query_validation_role
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_users_by_request (
        Request $request,
        $eloquent,
        $query_validation_role = 'get_users_query',
        array $order_by = ['user_id' => 'DESC']
    ): object
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

    /**
     * @param array $query
     * @param $eloquent
     * @return mixed
     */
    public function users_query_to_eloquent (array $query, $eloquent)
    {
        return (new UserAction())->query_to_eloquent($query, $eloquent);
    }

    /**
     * @param array $query
     * @param Model|Builder $eloquent
     * @return mixed
     */
    public function notification_user_query_to_eloquent (array $query, $eloquent)
    {
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
