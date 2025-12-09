<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use StellarSecurity\UserApiLaravel\UserService;

class LoginController extends Controller
{
    private string $token = "Stellar.UI.Website.Account.API";

    public function __construct(public UserService $userService)
    {
    }

    /**
     * Authenticate user via UserService
     */
    public function auth(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if ($username === null || $password === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'Username or password missing',
            ], 400);
        }

        $response = $this->userService->auth([
            'username' => $username,
            'password' => $password,
            'token'    => $this->token,
        ]);

        if ($response->failed()) {
            return response()->json([
                'response_code'    => 502,
                'response_message' => 'User service unavailable',
            ], 502);
        }

        return response()->json($response->object());
    }

    public function create(Request $request): JsonResponse
    {
        $data          = $request->all();
        $data['token'] = $this->token;

        $response = $this->userService->create($data);

        if ($response->failed()) {
            return response()->json([
                'response_message' => 'User service unavailable',
            ], 502);
        }

        return response()->json($response->object());
    }

    public function sendresetpasswordlink(Request $request): JsonResponse
    {
        $email = $request->input('email');

        if ($email === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'No email was provided',
            ], 400);
        }

        $confirmation_code = Str::password(6, false, true, false, false);

        $response = $this->userService->sendresetpasswordlink($email, $confirmation_code);

        if ($response->failed()) {
            return response()->json([
                'response_code'    => 502,
                'response_message' => 'User service unavailable',
            ], 502);
        }

        $resetpassword = $response->object();

        if (isset($resetpassword->response_code) && $resetpassword->response_code !== 200) {
            return response()->json([
                'response_code'    => $resetpassword->response_code,
                'response_message' => $resetpassword->response_message ?? 'Reset failed',
            ], 400);
        }

        return response()->json([
            'response_code'    => 200,
            'response_message' => 'OK. Reset password link sent to your email.',
        ]);
    }

    public function resetpasswordupdate(Request $request): JsonResponse
    {
        $email             = $request->input('email');
        $confirmation_code = $request->input('confirmation_code');
        $new_password      = $request->input('new_password');

        if ($email === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'No email was provided',
            ], 400);
        }

        if ($new_password === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'New password was not provided',
            ], 400);
        }

        if ($confirmation_code === null) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'No confirmation code was provided',
            ], 400);
        }

        $response = $this->userService
            ->verifyresetpasswordconfirmationcode($email, $confirmation_code, $new_password);

        if ($response->failed()) {
            return response()->json([
                'response_code'    => 502,
                'response_message' => 'User service unavailable',
            ], 502);
        }

        return response()->json($response->object());
    }
}
