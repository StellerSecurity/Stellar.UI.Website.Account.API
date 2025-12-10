<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use StellarSecurity\DeviceApi\DeviceService;
use StellarSecurity\SubscriptionLaravel\Enums\SubscriptionType;
use StellarSecurity\SubscriptionLaravel\SubscriptionService;
use StellarSecurity\UserApiLaravel\UserService;

class VpnDashboardController
{

    public function __construct(
        private DeviceService $deviceService, private UserService $userService,
        private SubscriptionService $subscriptionService)
    {

    }

    public function dashboard(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json(null, 401);
        }

        // Fetch user from User API using the access token
        $userResponse = $this->userService->token($token);

        if ($userResponse->failed()) {
            return response()->json(null, 401);
        }

        $user = $userResponse->object();

        if (! $user || ! isset($user->token->tokenable_id)) {
            return response()->json(null, 401);
        }

        $userId = $user->token->tokenable_id;

        // Fetch all VPN subscriptions for this user
        $subscriptionsResponse = $this->subscriptionService
            ->findUserSubscriptions($userId, SubscriptionType::VPN->value);

        if ($subscriptionsResponse->failed()) {
            return response()->json([
                'response_code'    => 500,
                'response_message' => 'Could not load subscriptions.',
            ], 500);
        }

        $subscriptions = $subscriptionsResponse->object();

        if (empty($subscriptions) || ! isset($subscriptions[0]->id)) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'No subscription found.',
            ], 400);
        }

        $subscription = $subscriptions[0];

        // Fetch devices for this subscription
        $devicesResponse = $this->deviceService->devices($subscription->id);

        if ($devicesResponse->failed()) {
            return response()->json([
                'response_code'    => 500,
                'response_message' => 'Could not load devices.',
            ], 500);
        }

        $devices = $devicesResponse->object();

        return response()->json([
            'response_code' => 200,
            'subscription'  => $subscription,
            'devices'       => $devices,
        ], 200);
    }

    public function disconnect(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            return response()->json(null, 401);
        }

        // Resolve user from User API using the access token
        $userResponse = $this->userService->token($token);

        if ($userResponse->failed()) {
            return response()->json(null, 401);
        }

        $user = $userResponse->object();

        if (! $user || ! isset($user->token->tokenable_id)) {
            return response()->json(null, 401);
        }

        $userId = $user->token->tokenable_id;

        // Fetch all VPN subscriptions for this user
        $subscriptionsResponse = $this->subscriptionService
            ->findUserSubscriptions($userId, SubscriptionType::VPN->value);

        if ($subscriptionsResponse->failed()) {
            return response()->json([
                'response_code'    => 500,
                'response_message' => 'Could not load subscriptions.',
            ], 500);
        }

        $subscriptions = $subscriptionsResponse->object();

        if (empty($subscriptions) || ! isset($subscriptions[0]->id)) {
            return response()->json([
                'response_code'    => 400,
                'response_message' => 'No subscription found.',
            ], 400);
        }

        $subscription = $subscriptions[0];

        $deviceName = (string) $request->input('device_name');

        if (empty($deviceName)) {
            return response()->json([
                'response_code'    => 422,
                'response_message' => 'Device name is required.',
            ], 422);
        }

        // Ask device service to delete/unregister this device from the subscription
        $devicesResponse = $this->deviceService->delete($subscription->id, $deviceName);

        if ($devicesResponse->failed()) {
            return response()->json([
                'response_code'    => 500,
                'response_message' => 'Could not disconnect device.',
            ], 500);
        }

        $payload = $devicesResponse->object();

        if (isset($payload->response_code) && $payload->response_code !== 200) {
            return response()->json([
                'response_code'    => $payload->response_code,
                'response_message' => $payload->response_message ?? 'Could not disconnect device.',
            ], 400);
        }

        return response()->json([
            'response_code'    => 200,
            'response_message' => 'Device disconnected.',
        ], 200);
    }

}
