<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id'
    ];

    public $timestamps = false;

    public function user ()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }
}