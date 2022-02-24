<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderContent extends Model
{
    use HasFactory;

    protected $table = 'order_contents';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'amount',
    ];

    protected $hidden = [
        'id',
        'order_id'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function order (): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function product (): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->withTrashed();
    }
}
