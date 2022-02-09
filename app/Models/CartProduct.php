<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartProduct extends Model
{
    use HasFactory;

    protected $table = 'cart_product';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function cart (): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function product (): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
