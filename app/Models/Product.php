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
        'price',
        'discount_percentage',
        'type',
    ];

    protected $casts = [
        'stock' => ProductStockCast::class,
        'price' => 'integer'
    ];

    public function getOriginalStock ()
    {
        return $this->getOriginal('stock');
    }
}
