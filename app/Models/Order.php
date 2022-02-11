<?php

namespace App\Models;

use App\Casts\CustomDateCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'amount',
        'discount',
        'products',
        'receipt_at'
    ];

    protected $casts = [
        'receipt_at' => CustomDateCast::class,
        'discount' => 'object',
        'products' => 'object'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
