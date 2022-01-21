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
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50'
        ]);

        return response()->json(
            ContactUsMessageActions::get_all(
                (! empty($request->input('skip'))) ? $request->input('skip') : 0,
                (! empty($request->input('limit'))) ? $request->input('limit') : 50
            )
        );
    }

    public function delete_by_id (string $id)
    {
        ContactUsMessage::where('id', $id)->delete();

        return response()->json([
            'message' => 'message deleted successfully'
        ]);
    }
}
