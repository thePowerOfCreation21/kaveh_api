<?php

namespace App\Actions;

use App\Models\ContactUsMessage;
use Illuminate\Http\Request;
use App\Services\PaginationService;

class ContactUsMessageActions
{
    /**
     * store new message
     *
     * @param Request $request
     * @return mixed
     */
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

    /**
     * get all messages
     * (uses PaginationService to paginate)
     *
     * @param Request $request
     * @return object
     */
    public static function get_all_with_request (Request $request)
    {
        return PaginationService::paginate_with_request(
            $request,
            ContactUsMessage::orderBy('id', 'DESC')
        );
    }
}
