<?php

namespace App\Models;

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
        'is_for_all_users' => 'boolean'
    ];
}
