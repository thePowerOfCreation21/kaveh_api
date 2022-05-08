<?php

namespace App\Models;

use App\Services\KeyObjectConfig;

class Rules extends KeyObjectConfig
{
    protected $key = 'rules';

    protected $fields = [
        'content' => 'string|max:2500'
    ];

    protected $default_values = [
        'content' => null
    ];
}
