<?php

namespace App\Models;

use App\Abstracts\KeyObjectConfig;

class ContactUsContent extends KeyObjectConfig
{
    protected $key = 'contact_us_content';

    protected $fields = [
        'phone_number' => 'string|max:20',
        'address' => 'string|max:255',
        'email' => 'email|max:255'
    ];
}
