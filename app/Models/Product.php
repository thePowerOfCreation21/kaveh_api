<?php

namespace App\Models;

use App\Casts\ProductStockCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'title',
        'image',
        'description',
        'before_discount_price',
        'price',
        'discount_percentage',
        'type',
        'stock'
    ];

    protected $casts = [
        'stock' => ProductStockCast::class,
        'price' => 'integer',
        'before_discount_price' => 'integer'
    ];

    public function getOriginalStock ()
    {
        return $this->getOriginal('stock');
    }
}
