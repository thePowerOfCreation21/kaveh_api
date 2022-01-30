<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\CommonQuestionActions;

class CommonQuestionController extends Controller
{
    public function store (Request $request)
    {
        CommonQuestionActions::store_from_request($request);

        return response()->json([
            'message' => 'common question stored successfully'
        ]);
    }
}
