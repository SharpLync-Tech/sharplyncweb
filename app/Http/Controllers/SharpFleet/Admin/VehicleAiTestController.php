<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\BranchService;
use App\Services\SharpFleet\CompanySettingsService;
use App\Services\SharpFleet\VehicleAiClient;
use App\Support\SharpFleet\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleAiTestController extends Controller
{
    public function index(Request $request): View
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        $organisationId = (int) $fleetUser['organisation_id'];
        $settingsService = new CompanySettingsService($organisationId);
        $timezone = $settingsService->timezone();

        $branchService = new BranchService();
        if ($branchService->branchesEnabled() && $branchService->vehiclesHaveBranchSupport()) {
            $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($fleetUser);
            $branchScopeEnabled = $branchService->userBranchAccessEnabled() && !$bypassBranchRestrictions;
            $branchId = null;

            if ($branchScopeEnabled) {
                $accessibleBranchIds = $branchService->getAccessibleBranchIdsForUser(
                    $organisationId,
                    (int) ($fleetUser['id'] ?? 0)
                );
                if (count($accessibleBranchIds) === 0) {
                    abort(403, 'No branch access.');
                }
                $branchId = (int) $accessibleBranchIds[0];
            } else {
                $branchId = (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
            }

            if ($branchId > 0) {
                $branch = $branchService->getBranch($organisationId, $branchId);
                $branchTimezone = $branch && isset($branch->timezone) ? trim((string) $branch->timezone) : '';
                if ($branchTimezone !== '') {
                    $timezone = $branchTimezone;
                }
            }
        }

        $aiCountry = $this->countryFromTimezone($timezone);

        return view('sharpfleet.admin.vehicles-ai-test', [
            'aiCountry' => $aiCountry,
        ]);
    }

    public function makes(Request $request, VehicleAiClient $client): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:40'],
            'location' => ['required', 'string', 'max:40'],
        ]);

        $location = strtoupper($validated['location']);
        $items = $client->suggestMakes(trim($validated['query']), $location);

        return response()->json([
            'items' => $items,
        ]);
    }

    public function models(Request $request, VehicleAiClient $client): JsonResponse
    {
        $validated = $request->validate([
            'make' => ['required', 'string', 'max:40'],
            'query' => ['nullable', 'string', 'max:40'],
            'location' => ['required', 'string', 'max:40'],
        ]);

        $location = strtoupper($validated['location']);
        $query = trim((string) ($validated['query'] ?? ''));
        $make = trim($validated['make']);

        $items = $client->suggestModels($make, $query, $location);

        return response()->json([
            'items' => $items,
        ]);
    }

    public function trims(Request $request, VehicleAiClient $client): JsonResponse
    {
        $validated = $request->validate([
            'make' => ['required', 'string', 'max:40'],
            'model' => ['required', 'string', 'max:40'],
            'query' => ['nullable', 'string', 'max:40'],
            'location' => ['required', 'string', 'max:40'],
        ]);

        $location = strtoupper($validated['location']);
        $query = trim((string) ($validated['query'] ?? ''));
        $make = trim($validated['make']);
        $model = trim($validated['model']);

        $items = $client->suggestTrims($make, $model, $query, $location);

        return response()->json([
            'items' => $items,
        ]);
    }

    public function countries(Request $request, VehicleAiClient $client): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:40'],
        ]);

        $items = $client->suggestCountries(trim($validated['query']));

        return response()->json([
            'items' => $items,
        ]);
    }

    private function countryFromTimezone(string $timezone): string
    {
        $tz = strtolower(trim($timezone));
        if ($tz === '') {
            return 'Australia';
        }

        if (str_starts_with($tz, 'australia/')) {
            return 'Australia';
        }
        if (str_starts_with($tz, 'pacific/auckland') || str_contains($tz, 'new_zealand')) {
            return 'New Zealand';
        }
        if (str_starts_with($tz, 'europe/') || str_contains($tz, 'london') || str_contains($tz, 'uk')) {
            return 'United Kingdom';
        }
        if (str_starts_with($tz, 'africa/') || str_contains($tz, 'johannesburg')) {
            return 'South Africa';
        }
        if (str_starts_with($tz, 'america/') || str_starts_with($tz, 'us/')) {
            return 'United States';
        }

        return 'Australia';
    }
}
