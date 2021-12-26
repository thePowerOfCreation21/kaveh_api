<?php

namespace App\Models;

class ContactUsContent
{
    public static $fields = [
        'phone_number' => 'string|max:20',
        'social_network_links' => 'string|max:255',
        'address' => 'string|max:255',
        'email' => 'email|max:255'
    ];
}
