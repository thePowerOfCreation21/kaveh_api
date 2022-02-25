<?php

namespace App\Services;

use Illuminate\Http\Request;

class PaginationService
{
    public static function paginate_with_request (Request $request, $model)
    {
        $values = self::get_values_from_request($request);

        return self::paginate(
            $model,
            $values['skip'],
            $values['limit']
        );
    }

    public static function get_values_from_request (Request $request)
    {
        $request->validate([
            'skip' => 'numeric|min:0',
            'limit' => 'numeric|min:0|max:100',
        ]);

        return [
            'skip' => !empty($request->input('skip')) ? $request->input('skip') : 0,
            'limit' => !empty($request->input('limit')) ? $request->input('limit') : 100,
        ];
    }

    public static function paginate ($model, int $skip = 0, $limit = 50)
    {
        return (object) [
            'count' => $model->count(),
            'data' => $model
                ->skip($skip)
                ->take($limit)
                ->get()
        ];
    }
}
