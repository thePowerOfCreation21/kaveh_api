<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\ArticleActions;

class ArticleController extends Controller
{
    public function store (Request $request)
    {
        ArticleActions::store($request);

        return response()->json([
            'message' => 'article added successfully'
        ]);
    }

    public function get (Request $request)
    {
        $request->validate([
            'skip' => 'numeric',
            'limit' => 'numeric|max:50'
        ]);

        return response()->json(
            ArticleActions::get(
                (! empty($request->input('skip'))) ? $request->input('skip') : 0,
                (! empty($request->input('limit'))) ? $request->input('limit') : 50
            )
        );
    }
}
