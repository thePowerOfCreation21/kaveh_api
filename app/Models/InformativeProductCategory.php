<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformativeProductCategory extends Model
{
    use HasFactory;

    protected $table = 'informative_product_categories';

    protected $fillable = [
        'title'
    ];

    public $timestamps = false;
}
