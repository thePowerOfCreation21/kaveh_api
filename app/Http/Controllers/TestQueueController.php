<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\TestQueue;
use App\Actions\KeyValueConfigActions;

class TestQueueController extends Controller
{
    public function set_value (Request $request)
    {
        $value = $request->validate([
            'value' => 'required|string|max:100'
        ])['value'];

        TestQueue::dispatch($value);

        return response()->json([
            'message' => 'value should be set if the queue is working'
        ]);
    }

    public function get_value ()
    {
        return response()->json([
            'value' => KeyValueConfigActions::get('test_queue')
        ]);
    }
}
