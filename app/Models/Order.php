<?php

namespace App\Models;

use App\Casts\CustomDateCast;
use App\Casts\OrderIdCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'amount',
        'discount',
        'receipt_at'
    ];

    protected $appends = [
        'order_id'
    ];

    protected $casts = [
        'created_at' => CustomDateCast::class,
        'order_id' => OrderIdCast::class,
        'discount' => 'object',
    ];

    protected $hidden = [
        'updated_at'
    ];

    /**
     * @return BelongsTo
     */
    public function user (): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select([
            'id',
            'name',
            'last_name',
            'phone_number',
            'card_number',
            'is_blocked'
        ]);
    }

    public function contents ()
    {
        return $this->hasMany(OrderContent::class, 'order_id', 'id');
    }
}
