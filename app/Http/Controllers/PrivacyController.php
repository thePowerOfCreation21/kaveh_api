<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Privacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    /**
     * @return JsonResponse
     * @throws CustomException
     */
    public function get (): JsonResponse
    {
        return response()->json(
            (new Privacy())->get()
        );
    }
}
