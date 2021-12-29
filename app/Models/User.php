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
        'card_number'
    ];

    protected $hidden = [
        'password'
    ];
}
