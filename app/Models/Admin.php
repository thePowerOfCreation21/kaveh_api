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
        'privileges'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'privileges' => 'array'
    ];

    public $timestamps = false;

    public static $privileges_list = ['manage_users', 'manage_admins', 'manage_orders'];
}
