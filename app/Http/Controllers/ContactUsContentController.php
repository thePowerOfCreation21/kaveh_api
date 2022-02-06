<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUsContent;

class ContactUsContentController extends Controller
{
    public function update (Request $request)
    {
        (new ContactUsContent())->update_by_request($request);

        return response()->json([
            'message' => 'content of the contact us updated successfully'
        ]);
    }

    public function get ()
    {
        return response()->json(
            (new ContactUsContent())->get()
        );
    }
}
