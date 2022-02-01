<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationFrame extends Model
{
    use HasFactory;

    protected $table = 'notification_frames';

    protected $fillable = ['text'];

    public $timestamps = false;
}
