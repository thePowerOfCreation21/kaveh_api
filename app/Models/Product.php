<?php

namespace App\Models;

use App\Casts\ProductStockCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'title',
        'image',
        'description',
        'price',
        'discount_percentage',
        'type',
        'stock'
    ];

    protected $casts = [
        'stock' => ProductStockCast::class
    ];

    public function getOriginalStock ()
    {
        return $this->getOriginal('stock');
    }
}
