<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformativeProduct extends Model
{
    use HasFactory;

    protected $table = 'informative_products';

    protected $fillable = [
        'title',
        'image',
        'category_id',
        'description'
    ];

    protected $hidden = [
        'is_in_index',
        'created_at',
        'updated_at'
    ];

    /**
     * @return BelongsTo
     */
    public function category (): BelongsTo
    {
        return $this->belongsTo(InformativeProductCategory::class, 'category_id', 'id');
    }
}
