<?php

namespace App\Models;

use App\Abstracts\KeyObjectConfig;

class ContactUsContent extends KeyObjectConfig
{
    protected $key = 'contact_us_content';

    protected $fields = [
        'phone_number' => 'string|max:20',
        'social_network_links' => 'array|max:10',
        'social_network_links.*' => 'string',
        'address' => 'string|max:255',
        'email' => 'email|max:255'
    ];

    public $ignore_this_fields = [
        'social_network_links.*'
    ];

    public $default_values = [
        'social_network_links' => []
    ];
}
