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

    public function get (Request $request)
    {
        return response()->json(
            CommonQuestionActions::get_by_request($request)
        );
    }
}
