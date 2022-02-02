<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\NotificationAction;

class NotificationController extends Controller
{
    public function send (Request $request)
    {
        (new NotificationAction())->send_by_request($request);

        return response()->json([
            'message' => 'sent successfully'
        ]);
    }
}
