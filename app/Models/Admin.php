<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Admin extends Model
{
    use HasFactory;

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
}
