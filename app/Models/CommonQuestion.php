<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonQuestion extends Model
{
    use HasFactory;

    protected $table = 'common_questions';

    protected $fillable = [
        'question',
        'answer'
    ];
}
