<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsMessage extends Model
{
    use HasFactory;

    protected $table = 'contact_us_messages';

    protected $fillable = [
        'full_name',
        'email',
        'message'
    ];

    protected $hidden = [
        'updated_at'
    ];
}
