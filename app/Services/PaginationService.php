<?php

namespace App\Services;

use Illuminate\Http\Request;

class PaginationService
{
    public static function paginate_with_request (Request $request, $model)
    {
        $request->validate([
            'skip' => 'numeric|min:0',
            'limit' => 'numeric|min:0|max:50',
        ]);

        return self::paginate(
            $model,
            !empty($request->input('skip')) ? $request->input('skip') : 0,
            !empty($request->input('limit')) ? $request->input('limit') : 50,
        );
    }

    public static function paginate ($model, int $skip = 0, $limit = 50)
    {
        return (object) [
            'count' => $model->count(),
            'data' => $model
                ->orderBy('id', 'DESC')
                ->skip($skip)
                ->take($limit)
                ->get()
        ];
    }
}
