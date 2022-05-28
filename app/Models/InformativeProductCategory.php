<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InformativeProductCategory extends Model
{
    use HasFactory;

    protected $table = 'informative_product_categories';

    protected $fillable = [
        'title',
        'image'
    ];

    public $timestamps = false;

    /**
     * @return HasMany
     */
    public function products (): HasMany
    {
        return $this->hasMany(InformativeProduct::class, 'category_id', 'id');
    }
}
