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

    public function get_by_id (string $id)
    {
        return response()->json(
            CommonQuestionActions::get_by_id($id)
        );
    }

    public function edit_by_id (Request $request, string $id)
    {
        CommonQuestionActions::edit_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'CommonQuestion updated successfully'
        ]);
    }

    public function delete_by_id (string $id)
    {
        CommonQuestionActions::delete_by_id($id);

        return response()->json([
            'message' => 'common question deleted successfully'
        ]);
    }
}
