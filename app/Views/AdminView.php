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

    /**
     * get admin by id
     *
     * @param string $id
     * @return Admin
     * @throws \Exception
     */
    public static function get_by_id (string $id): Admin
    {
        $admin = Admin::where('id', $id)->first();
        if (empty($admin))
        {
            throw new \Exception('admin not found', 51);
        }
        return $admin;
    }
}
