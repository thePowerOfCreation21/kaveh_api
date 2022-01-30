<?php

namespace App\Actions;

use Illuminate\Http\Request;
use App\Models\CommonQuestion;

class CommonQuestionActions
{
    /**
     * store CommonQuestion from Request
     *
     * @param Request $request
     * @return CommonQuestion
     */
    public static function store_from_request (Request $request): CommonQuestion
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:1500',
        ]);

        return CommonQuestion::create($data);
    }
}
