<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\KeyValueConfigActions;

class AboutUsController extends Controller
{
    public function update (Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:10000'
        ]);

        KeyValueConfigActions::set('about_us', $request->input('content'));

        return response()->json([
            'message' => 'content of the about us updated successfully'
        ]);
    }

    public function get ()
    {
        return response()->json([
            'content' => (string) KeyValueConfigActions::get('about_us')
        ]);
    }
}
