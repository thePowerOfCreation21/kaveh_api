<?php

namespace App\Models;

use App\Services\KeyObjectConfig;

class ContactUsContent extends KeyObjectConfig
{
    protected $key = 'contact_us_content';

    protected $fields = [
        'phone_number' => 'string|max:20',
        'address' => 'string|max:255',
        'instagram' => 'string|max:255',
        'telegram' => 'string|max:255',
        'email' => 'email|max:255'
    ];
}
