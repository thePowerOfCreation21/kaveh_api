<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Rules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RulesController extends Controller
{
    /**
     * @return JsonResponse
     * @throws CustomException
     */
    public function get (): JsonResponse
    {
        return response()->json(
            (new Rules())->get()
        );
    }
}
