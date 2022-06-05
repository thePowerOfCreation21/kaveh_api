<?php

namespace App\Actions;

use App\Exceptions\CustomException;
use App\Jobs\SendSMSToUsers;
use App\Jobs\StoreNotificationUsers;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Services\Action;
use App\Services\PaginationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use function App\Helpers\convert_to_boolean;

class NotificationAction extends Action
{
    protected array $validation_roles = [
        'send' => [
            'type' => ['required', 'in:message,toast,sms'],
            'text' => ['required', 'string', 'max:1500'],
            'users' => ['array', 'max:1000'],
            'users.*' => ['numeric', 'max:25']
        ],
        'seen' => [
            'type' => ['in:message,toast']
        ],
        'get_query' => [
            'type' => ['in:message,toast'],
            'search' => ['string', 'max:100'],
            'user_id' => ['numeric', 'max:11']
        ],
        'get_users_query' => [
            'is_seen' => ['in:true,false'],
            'search' => ['string', 'max:100']
        ],
        'get_user_notifications_query' => [
            'type' => ['in:message,toast'],
            'is_seen' => ['in:true,false']
        ]
    ];

    protected array $unusual_fields = [
        'is_seen' => 'boolean'
    ];

    public function __construct()
    {
        $this->model = Notification::class;
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
     * @param array|string $validation_role
     * @return array|Model
     * @throws CustomException
     */
    public function send_by_request (Request $request, array|string $validation_role = 'send'): Model|array
    {
        $data = $this->get_data_from_request($request, $validation_role);

        return $this->send($data);
    }

    /**
     * @param array $data
     * @return array|Model
     * @throws CustomException
     */
    public function send (array $data): Model|array
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
     * @param string $id
     * @param array $query
     * @param callable|null $deleting
     * @return bool|int|null
     */
    public function delete_by_id(string $id, array $query = [], callable $deleting = null): bool|int|null
    {
        return parent::delete_by_id($id, $query, $deleting);
    }

    /**
     * @param array $query
     * @param object|null $eloquent
     * @param array $relations
     * @param array $order_by
     * @return object|null
     */
    public function query_to_eloquent(array $query, object $eloquent = null, array $relations = [], array $order_by = ['id' => 'DESC']): object|null
    {
        $eloquent = parent::query_to_eloquent($query, $eloquent, $relations, $order_by);

        if (isset($query['user_id']))
        {
            $userId = $query['user_id'];
            $eloquent = $eloquent->selectRaw("notifications.*, notification_users.is_seen")
                ->rightJoin('notification_users', function ($join){
                    $join->on('notifications.id', 'notification_users.notification_id');
                })
                ->where(function($q) use ($userId){
                    $q
                        ->where("notifications.is_for_all_users", true)
                        ->orWhere(function($q2) use ($userId){
                            $q2->where("notifications.is_for_all_users", false)->where("notification_users.user_id", $userId);
                        });
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
    public function seen_by_request (Request $request, string|array $validation_role = 'seen')
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
    public function get_ids ($notifications): array
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
        $notificationsId = array_unique($notificationsId);
        foreach ($notificationsId AS $notificationId)
        {
            NotificationUser::where('user_id', $userId)->where('notification_id', $notificationId)->delete();
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
    public function get_user_notifications_by_request (Request $request, string|array $validation_role = 'get_user_notifications_query'): object
    {
        $user = $this->get_user_from_request($request);
        $query = ['user_id' => $user->id];
        $query = array_merge($query, $this->get_data_from_request($request, $validation_role));
        $eloquent = $this->query_to_eloquent($query);
        $result = (new PaginationService())->paginate_with_request(
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
    public function get_user_notifications_count_by_request (Request $request, string|array $validation_role = 'get_user_notifications_query'): int
    {
        $user = $this->get_user_from_request($request);
        $query = ['user_id' => $user->id];
        $query = array_merge($query, $this->get_data_from_request($request, $validation_role));
        return $this->query_to_eloquent($query)->count();
    }

    /**
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
     * @param Request $request
     * @param $eloquent
     * @param string|array $query_validation_role
     * @param array $order_by
     * @return object
     * @throws CustomException
     */
    public function get_users_by_request (
        Request      $request,
                     $eloquent,
        string|array $query_validation_role = 'get_users_query',
        array        $order_by = ['user_id' => 'DESC']
    ): object
    {
        $eloquent = $this->users_query_to_eloquent(
            $this->get_data_from_request($request, $query_validation_role),
            $eloquent
        );

        $eloquent = $this->add_order_to_eloquent($order_by, $eloquent);

        return (new PaginationService())->paginate_with_request(
            $request,
            $eloquent
        );
    }

    /**
     * @param array $query
     * @param $eloquent
     * @return object
     */
    public function users_query_to_eloquent (array $query, $eloquent): object
    {
        return (new UserAction())->query_to_eloquent($query, $eloquent, order_by: []);
    }

    /**
     * @param array $query
     * @param $eloquent
     * @return mixed
     */
    public function notification_user_query_to_eloquent (array $query, $eloquent): mixed
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
