<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUsMessage;

class ContactUsMessageController extends Controller
{
    public function store (Request $request)
    {
        ContactUsMessage::create(
            $request->validate([
                'full_name' => 'required|string|max:120',
                'email' => 'required|email|max:120',
                'message' => 'required|string|max:120'
            ])
        );

        return response()->json([
            'message' => 'message sent successfully'
        ]);
    }
}
