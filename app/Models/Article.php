<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\CustomDateCast;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    protected $fillable = [
        'title',
        'image',
        'content'
    ];

    protected $casts = [
        'created_at' => CustomDateCast::class,
        'updated_at' => CustomDateCast::class
    ];
}
