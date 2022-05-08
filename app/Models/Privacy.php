<?php

namespace App\Models;

use App\Services\KeyObjectConfig;

class Privacy extends KeyObjectConfig
{
    protected $key = 'privacy';

    protected $fields = [
        'content' => 'string|max:2500'
    ];

    protected $default_values =  [
        'content' => null
    ];
}
