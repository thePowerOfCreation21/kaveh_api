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
}
