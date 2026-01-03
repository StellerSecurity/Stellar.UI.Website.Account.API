<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use StellarSecurity\DeviceApi\DeviceService;
use StellarSecurity\SubscriptionLaravel\SubscriptionService;
use StellarSecurity\UserApiLaravel\UserService;

class DashboardController extends Controller
{

    public function __construct(private SubscriptionService $subscriptionService,
                                private DeviceService $deviceService,
                                private UserService $userService)
    {

    }

    public function index(Request $request) {

        $token = $request->bearerToken();

        $data = [
            'stellar_id' => [
                'id'          => $userId ?? 123,              // udfyld rigtigt
                'displayName' => 'Blerim Cazimi',
                'email'       => 'blerim@example.com',
            ],

            'products' => [

                // STELLAR VPN
                [
                    'key'        => 'vpn',
                    'name'       => 'Stellar VPN',
                    'plan'       => 'Unlimited',
                    'category'   => 'Stellar VPN',
                    'status'     => 'active',                 // active | inactive | cancelled
                    'status_badge' => 'Active',
                    'stats' => [
                        'label'        => 'devices',
                        'in_use'       => 3,
                        'limit'        => 5,
                        'display_text' => '3 / 5 devices',
                    ],
                    'description' => 'Hide your location and browse privately across all your devices.',
                    'actions' => [
                        [
                            'type'   => 'primary',
                            'label'  => 'Open VPN devices',
                            'action' => 'open_vpn_devices',   // frontenden mapper selv til route
                        ],
                        [
                            'type'   => 'secondary',
                            'label'  => 'Manage subscription',
                            'action' => 'manage_vpn_subscription',
                        ],
                    ],
                ],

                // STELLAR ANTIVIRUS
                [
                    'key'        => 'antivirus',
                    'name'       => 'Stellar Antivirus',
                    'plan'       => '3 devices',
                    'category'   => 'Stellar Antivirus',
                    'status'     => 'active',
                    'status_badge' => 'Active',
                    'stats' => [
                        'label'        => 'devices',
                        'in_use'       => 2,
                        'limit'        => 3,
                        'display_text' => '2 / 3 devices',
                    ],
                    'description' => 'Protect your Mac and Windows devices against malware and unwanted software.',
                    'actions' => [
                        [
                            'type'   => 'primary',
                            'label'  => 'Open Antivirus dashboard',
                            'action' => 'open_antivirus_dashboard',
                        ],
                        [
                            'type'   => 'secondary',
                            'label'  => 'Manage subscription',
                            'action' => 'manage_antivirus_subscription',
                        ],
                    ],
                ],

                // STELLAR PRIVATE NOTES
                [
                    'key'        => 'notes',
                    'name'       => 'Private Notes',
                    'plan'       => 'Premium',
                    'category'   => 'Stellar Private Notes',
                    'status'     => 'active',
                    'status_badge' => 'Active',
                    'stats' => [
                        'label'        => 'devices',
                        'synced_on'    => 2,
                        'display_text' => 'Synced on 2 devices',
                    ],
                    'description' => 'Private, end-to-end encrypted notes and documents synced across your devices.',
                    'actions' => [
                        [
                            'type'   => 'primary',
                            'label'  => 'Open Private Notes',
                            'action' => 'open_private_notes',
                        ],
                        [
                            'type'   => 'secondary',
                            'label'  => 'Manage subscription',
                            'action' => 'manage_notes_subscription',
                        ],
                    ],
                ],

                // STELLAR PROTECT
                [
                    'key'        => 'protect',
                    'name'       => 'Device Protect',
                    'plan'       => null,
                    'category'   => 'Stellar Protect',
                    'status'     => 'enabled',                 // enabled vs disabled
                    'status_badge' => 'Enabled',
                    'stats' => [
                        'label'        => 'protected devices',
                        'protected'    => 3,
                        'display_text' => '3 protected devices',
                    ],
                    'description' => 'Remote wipe, tamper detection and USB kill-switch for your Stellar devices.',
                    'actions' => [
                        [
                            'type'   => 'primary',
                            'label'  => 'Manage Protect rules',
                            'action' => 'manage_protect_rules',
                        ],
                        [
                            'type'   => 'secondary',
                            'label'  => 'View devices',
                            'action' => 'view_protect_devices',
                        ],
                    ],
                ],

                // STELLAR SECRET
                [
                    'key'        => 'secret',
                    'name'       => 'Stellar Secret',
                    'plan'       => null,
                    'category'   => 'Stellar Secret',
                    'status'     => 'active',
                    'status_badge' => 'Active',
                    'stats' => [
                        'label'        => 'uses_stellar_id',
                        'uses_stellar_id' => true,
                        'display_text' => 'Uses Stellar ID',
                    ],
                    'description' => 'Create private links that disappear after they are opened. Perfect for passwords and sensitive details.',
                    'actions' => [
                        [
                            'type'   => 'primary',
                            'label'  => 'Open Stellar Secret',
                            'action' => 'open_stellar_secret',
                        ],
                    ],
                ],
            ],

            // Tekst til “More Stellar tools coming soon”-boksen kan også komme fra API
            'future_tools' => [
                'title'       => 'More Stellar tools coming soon',
                'description' => 'Future products like Stellar Mail or Stellar Drive will appear here automatically when they are added to your account.',
            ],
        ];

        return response()->json($data);

    }

}
