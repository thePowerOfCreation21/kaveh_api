<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformativeProduct extends Model
{
    use HasFactory;

    protected $table = 'informative_products';

    protected $fillable = [
        'title',
        'image',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
