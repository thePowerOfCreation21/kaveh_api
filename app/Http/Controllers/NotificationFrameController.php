<?php

namespace App\Http\Controllers;

use App\Actions\NotificationFrameAction;
use Illuminate\Http\Request;

class NotificationFrameController extends Controller
{
    public function store (Request $request)
    {
        (new NotificationFrameAction())->store_by_request($request);

        return response()->json([
            'message' => 'stored successfully'
        ]);
    }

    public function get (Request $request)
    {
        return response()->json(
            (new NotificationFrameAction())->get_by_request($request)
        );
    }
}
