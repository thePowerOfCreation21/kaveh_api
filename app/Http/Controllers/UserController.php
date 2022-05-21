<?php

namespace App\Http\Controllers;

use App\Actions\UserAction;
use App\Exceptions\CustomException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function add (Request $request): JsonResponse
    {
        (new UserAction())->store_by_request($request);

        return response()->json([
            'message' => 'user added successfully'
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function update_by_id (Request $request, string $id): JsonResponse
    {
        (new UserAction())->update_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'user updated successfully'
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function block_user_by_id (Request $request, string $id): JsonResponse
    {
        (new UserAction())->block_by_request_and_id($request, $id);

        return response()->json([
            'message' => 'user blocked successfully'
        ]);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function unblock_user_by_id (string $id): JsonResponse
    {
        (new UserAction())->unblock_by_id($id);

        return response()->json([
            'message' => 'user unblocked successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function get (Request $request): JsonResponse
    {
        return response()->json(
            (new UserAction())->get_by_request($request)
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_notifications_by_id (Request $request, string $id): JsonResponse
    {
        return response()->json(
            (new UserAction())->get_user_notifications_by_request_and_id($request, $id)
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function get_by_id (string $id): JsonResponse
    {
        return response()->json(
            (new UserAction())->get_by_id($id)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function login (Request $request): JsonResponse
    {
        try{
            $token = (new UserAction())->login_with_OTP_by_request($request);
        }
        catch (Exception $exception)
        {
            $token = (new UserAction())->login_by_request($request);
        }

        return response()->json([
            'token' => $token->plainTextToken
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function login_with_OTP (Request $request): JsonResponse
    {
        return response()->json([
            'token' => (new UserAction())->login_with_OTP_by_request($request)->plainTextToken
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function forgot_password (Request $request): JsonResponse
    {
        (new UserAction())->send_one_time_password_by_request($request);

        return response()->json([
            'message' => 'one time password was sent to your phone number, try logging in with OTP'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get_user_from_request (Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function change_password (Request $request): JsonResponse
    {
        (new UserAction())->change_password_by_request_and_model($request, $request->user());

        return response()->json([
            'message' => 'password changed successfully'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     */
    public function update_by_request (Request $request): JsonResponse
    {
        (new UserAction())->update_user_by_request($request);

        return response()->json([
            'message' => 'updated successfully'
        ]);
    }
}
