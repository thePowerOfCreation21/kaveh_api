<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class Admin extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'admins';

    protected $fillable = [
        'user_name',
        'password',
        'privilege'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'privilege' => 'array'
    ];

    public $timestamps = false;

    public static $privilege_list = ['manage_users', 'manage_admins', 'manage_orders'];
}
