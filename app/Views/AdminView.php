<?php

namespace App\Views;

use App\Actions\AdminActions;

class AdminView
{
    public static function get_response_by_exception (\Exception $exception)
    {
        switch ($exception->getCode())
        {
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
