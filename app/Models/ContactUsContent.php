<?php

namespace App\Models;

class ContactUsContent
{
    public static $fields = [
        'phone_number' => 'string|max:20',
        'social_network_links' => 'array|max:10',
        'social_network_links.*' => 'string',
        'address' => 'string|max:255',
        'email' => 'email|max:255'
    ];

    public static $ignore_this_fields = [
        'social_network_links.*'
    ];

    public static $default_values = [
        'social_network_links' => []
    ];
}
