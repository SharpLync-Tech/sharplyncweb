<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\SharpFleet\CompanySettingsService;

class TripStartConfigController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // API key middleware should already have resolved the user
        $user = $request->user();

        if (!$user || !isset($user['organisation_id'])) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $settingsService = new CompanySettingsService(
            (int) $user['organisation_id']
        );

        $settings = $settingsService->all();

        return response()->json([
            'version' => 'v0.1',
            'timezone' => $settingsService->timezone(),

            'trip_mode' => [
                'enabled' => true,
                'allow_private' => $settingsService->allowPrivateTrips(),
                'default' => 'business',
            ],
            'private_vehicle' => [
                'enabled' => $settingsService->privateVehicleSlotsEnabled(),
            ],

            'odometer' => [
                'tracking_mode' => 'distance',
                'distance_unit' => $settingsService->distanceUnit(),
                'required' => $settingsService->odometerRequired(),
                'allow_override' => $settingsService->odometerAllowOverride(),
            ],

            'manual_times' => [
                'start_required' => $settingsService->requireManualStartEndTimes(),
                'end_required' => $settingsService->requireManualStartEndTimes(),
            ],

            'customer' => [
                'enabled' => $settings['customer']['enabled'] ?? false,
                'label' => $settingsService->clientLabel() ?: 'Customer',
                'required' => $settings['customer']['required'] ?? false,
                'allow_select' => $settings['customer']['allow_select'] ?? true,
                'allow_manual' => $settings['customer']['allow_manual'] ?? true,
                'max_list' => 500,
            ],

            'client_presence' => [
                'enabled' => $settings['client_presence']['enabled'] ?? false,
                'label' => $settings['client_presence']['label'] ?? 'Client',
                'required' => $settings['client_presence']['required'] ?? false,
                'enable_addresses' => $settings['client_presence']['enable_addresses'] ?? false,
            ],

            'purpose_of_travel' => [
                'enabled' => $settingsService->purposeOfTravelEnabled(),
                'required' => false,
                'max_length' => 255,
            ],

            'safety_check' => [
                'enabled' => $settingsService->safetyCheckEnabled(),
                'required' => true,
                'items' => $settingsService->safetyCheckItems() ?? [],
            ],
        ]);
    }
}
