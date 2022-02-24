<?php

namespace App\Http\Controllers;

use App\Actions\StatsAction;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function get (): JsonResponse
    {
        return response()->json(
            (new StatsAction())->get()
        );
    }
}
