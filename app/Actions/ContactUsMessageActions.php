<?php

namespace App\Actions;

use App\Models\ContactUsMessage;
use Illuminate\Http\Request;

class ContactUsMessageActions
{
    public static function store_with_request (Request $request)
    {
        return ContactUsMessage::create(
            $request->validate([
                'full_name' => 'required|string|max:120',
                'email' => 'required|email|max:120',
                'message' => 'required|string|max:120'
            ])
        );
    }

    public static function get_all (int $skip = 0, int $limit = 50)
    {
        return (object) [
            'count' => ContactUsMessage::count(),
            'data' => ContactUsMessage::orderBy('id', 'DESC')->skip($skip)->take($limit)->get()
        ];
    }
}
