<?php

namespace App\Models;

use App\Casts\CustomDateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    use HasFactory;

    protected $table = 'notification_users';

    protected $fillable = [
        'user_id',
        'notification_id',
        'is_seen'
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'notification_created_at' => CustomDateCast::class,
    ];

    public $timestamps = false;
}
