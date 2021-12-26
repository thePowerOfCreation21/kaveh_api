<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUsContent;
use App\Actions\ContactUsContentActions;

class ContactUsContentController extends Controller
{
    public function update (Request $request)
    {
        $fields = $request->validate(
            ContactUsContent::$fields
        );

        ContactUsContentActions::update(
            (object) $fields
        );

        return response()->json([
            'message' => 'content of the contact us updated successfully'
        ]);
    }

    public function get ()
    {
        return response()->json(
            ContactUsContentActions::get()
        );
    }
}
