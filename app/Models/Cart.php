<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    protected $fillable = [
        'user_id'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function user (): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function products ()
    {
        return $this->belongsToMany(Product::class, 'cart_product', 'cart_id', 'product_id')
            ->withPivot('quantity');
    }
}
