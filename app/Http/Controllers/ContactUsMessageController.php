<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUsMessage;
use App\Actions\ContactUsMessageActions;

class ContactUsMessageController extends Controller
{
    public function store (Request $request)
    {
        ContactUsMessageActions::store_with_request($request);

        return response()->json([
            'message' => 'message sent successfully'
        ]);
    }

    public function get_all (Request $request)
    {
        return ContactUsMessageActions::get_all_with_request($request);
    }

    public function delete_by_id (string $id)
    {
        ContactUsMessage::where('id', $id)->delete();

        return response()->json([
            'message' => 'message deleted successfully'
        ]);
    }
}
