<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * @return HasOne
     */
    public function cart (): HasOne
    {
        return $this->hasOne(Cart::class, 'cart_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function product (): HasOne
    {
        return $this->hasOne(Product::class, 'product_id', 'id');
    }
}
