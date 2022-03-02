<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class Admin extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'admins';

    protected $fillable = [
        'first_name',
        'last_name',
        'user_name',
        'password',
        'is_primary',
        'privileges',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'privileges' => 'object',
    ];

    public static $privileges_list = [
        'get_stats',
        'manage_settings',
        'get_orders',
        'manage_order_time_limit',
        'manage_products',
        'manage_discounts',
        'send_notifications',
        'get_users',
        'manage_users'
    ];

    public static function fix_privileges (object $temp_privileges, $privileges = null)
    {
        if (! is_object($privileges))
        {
            $privileges = (object) [];
            foreach (self::$privileges_list AS $privilege)
            {
                $privileges->$privilege = false;
            }
        }

        foreach ($temp_privileges AS $privilege => $value)
        {
            if (isset($privileges->$privilege))
            {
                $privileges->$privilege = (bool) $temp_privileges->$privilege;
            }
        }

        return $privileges;
    }
}
