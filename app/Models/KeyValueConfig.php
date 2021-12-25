<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyValueConfig extends Model
{
    use HasFactory;

    protected $table = 'key_value_configs';

    protected $fillable = [
        'key',
        'value'
    ];
}
