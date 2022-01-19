<?php

namespace App\Views;

use App\Models\Admin;

class AdminView
{
    /**
     * converts exception to JsonResponse
     *
     * @param \Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public static function get_response_by_exception (\Exception $exception)
    {
        switch ($exception->getCode())
        {
            case 6:
            case 11:
            case 51:
            case 1:
            case 3:
                return response()->json([
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ], 400);

            default:
                return response()->json([
                    'code' => 50,
                    'message' => 'something went wrong'
                ], 400);
        }
    }
}
