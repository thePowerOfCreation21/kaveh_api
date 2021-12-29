<?php

namespace App\Actions;

use App\Models\ContactUsMessage;

class ContactUsMessageActions
{
    public static function get_all (int $skip = 0, int $limit = 50)
    {
        return (object) [
            'count' => ContactUsMessage::count(),
            'data' => ContactUsMessage::orderBy('id', 'DESC')->skip($skip)->take($limit)->get()
        ];
    }
}
