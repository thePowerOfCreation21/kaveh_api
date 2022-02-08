<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'last_name',
        'phone_number',
        'second_phone_number',
        'password',
        'area',
        'card_number',
        'should_change_password',
        'is_blocked',
        'reason_for_blocking'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'should_change_password',
        'is_blocked' => 'boolean'
    ];

    public function notifications ()
    {
        $user_id = $this->id;

        return NotificationUser::selectRaw("
            notifications.id AS notification_id,
            notifications.text AS notification_text,
            notifications.created_at AS notification_created_at,
            IFNULL(notification_users.user_id, '{$this->id}') AS `user_id`,
            IFNULL(notification_users.is_seen, '0') AS `is_seen`
        ")
            ->rightJoin('notifications', function($join){
                $join->on('notifications.id', 'notification_users.notification_id');
                $join->where('notification_users.user_id', $this->id);
            })
            ->where(function($query) use ($user_id){
                $query->where('notifications.is_for_all_users', true)
                    ->orWhere('notification_users.user_id', $user_id);
            });
    }

    public function format_user_data_array (array $user_data)
    {
        foreach ($this->fillable AS $field)
        {
            $user_data[$field] = $user_data[$field] ?? null;
        }

        return $user_data;
    }
}
