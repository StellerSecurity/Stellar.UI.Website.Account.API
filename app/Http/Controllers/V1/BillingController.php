<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use StellarSecurity\DeviceApi\DeviceService;
use StellarSecurity\SubscriptionLaravel\SubscriptionService;
use StellarSecurity\UserApiLaravel\UserService;
use Illuminate\Http\JsonResponse;

class BillingController
{

    public function __construct(
        private DeviceService $deviceService, private UserService $userService,
        private SubscriptionService $subscriptionService)
    {

    }

    public function index(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json([
                'response_code'    => 401,
                'response_message' => 'Missing access token.',
            ], 401);
        }

        // Resolve user from User API using the access token
        $userResponse = $this->userService->token($token);

        if ($userResponse->failed()) {
            return response()->json([
                'response_code'    => 401,
                'response_message' => 'Invalid or expired access token.',
            ], 401);
        }

        $userPayload = $userResponse->object();

        if (! $userPayload || ! isset($userPayload->token->tokenable_id)) {
            return response()->json([
                'response_code'    => 401,
                'response_message' => 'Unauthorized.',
            ], 401);
        }

        $userId = $userPayload->token->tokenable_id;

        // Fetch all subscriptions for this user (all products)
        $subscriptionsResponse = $this->subscriptionService->findUserSubscriptions($userId);

        if ($subscriptionsResponse->failed()) {
            return response()->json([
                'response_code'    => 500,
                'response_message' => 'Could not load subscriptions.',
            ], 500);
        }

        $subscriptions = $subscriptionsResponse->object() ?? [];

        // Placeholder for future invoice integration
        $invoices = [];

        return response()->json([
            'response_code'  => 200,
            'subscriptions'  => $subscriptions,
            'invoices'       => $invoices,
        ], 200);
    }
}
