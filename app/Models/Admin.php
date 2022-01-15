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
        'privileges' => 'array',
    ];

    public $timestamps = false;

    public static $privileges_list = [
        'manage_guest_side',
        'manage_users',
        'manage_products',
        'manage_orders',
        'manage_discounts'
    ];
}
