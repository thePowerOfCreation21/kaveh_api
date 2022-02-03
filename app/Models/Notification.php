<?php

namespace App\Models;

use App\Casts\CustomDateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'text',
        'type',
        'is_for_all_users'
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected $casts = [
        'is_for_all_users' => 'boolean',
        'created_at' => CustomDateCast::class,
    ];

    public function users()
    {
        $eloquent = NotificationUser::selectRaw("
            users.name AS user_name,
            users.last_name AS user_last_name,
            users.phone_number AS user_phone_number,
            users.id AS user_id,
            IFNULL(notification_users.is_seen, '0') AS `is_seen`,
            IFNULL(notification_users.notification_id, '{$this->id}') AS `notification_id`
        ");

        if (!$this->is_for_all_users)
        {
            $eloquent->join('users', function ($join){
                $join->on('notification_users.user_id', 'users.id');
                $join->where('notification_users.notification_id', $this->id);
            });
        }
        else
        {
            $eloquent->rightJoin('users', function ($join){
                $join->on('notification_users.user_id', 'users.id');
                $join->where('notification_users.notification_id', $this->id);
            });
        }

        return $eloquent;
    }
}
