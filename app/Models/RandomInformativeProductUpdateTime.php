<?php

namespace App\Models;

use App\Abstracts\KeyObjectConfig;

class RandomInformativeProductUpdateTime extends KeyObjectConfig
{
    protected $key = 'RandomInformativeProductUpdateTime';

    protected $fields = [
        'time' => 'string'
    ];

    protected $default_values = [
        'time' => null
    ];
}
