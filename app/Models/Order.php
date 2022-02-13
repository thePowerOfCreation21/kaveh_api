<?php

namespace App\Models;

use App\Casts\CustomDateCast;
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
        'contents',
        'receipt_at'
    ];

    protected $casts = [
        'created_at' => CustomDateCast::class,
        'discount' => 'object',
        'contents' => 'object',
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
}
