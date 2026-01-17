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
        $fleetUser = $this->getFleetUser($request);
        $organisationId = (int) $fleetUser['organisation_id'];

        $branchService = new BranchService();
        $branchesEnabled = $branchService->branchesEnabled() && $branchService->vehiclesHaveBranchSupport();
        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveBranchScope(
            $fleetUser,
            $organisationId,
            $branchService
        );

        $branches = $branchesEnabled ? $branchService->getBranches($organisationId) : collect();
        if ($branchScopeEnabled) {
            $branches = $branches->filter(
                fn ($b) => in_array((int) ($b->id ?? 0), $accessibleBranchIds, true)
            )->values();
        }

        $defaultBranchId = null;
        if ($branchesEnabled) {
            if ($branchScopeEnabled) {
                $defaultBranchId = count($accessibleBranchIds) > 0 ? (int) $accessibleBranchIds[0] : null;
            } else {
                $defaultBranchId = $branchService->ensureDefaultBranch($organisationId);
            }
        }

        return view('sharpfleet.admin.vehicles-ai-test', [
            'branchesEnabled' => $branchesEnabled,
            'branches' => $branches,
            'defaultBranchId' => $defaultBranchId,
        ]);
    }

    public function makes(Request $request, VehicleAiClient $client): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:40'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        $location = strtoupper($this->resolveLocationFromBranch(
            $request,
            (int) ($validated['branch_id'] ?? 0)
        ));
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
            'branch_id' => ['nullable', 'integer'],
        ]);

        $location = strtoupper($this->resolveLocationFromBranch(
            $request,
            (int) ($validated['branch_id'] ?? 0)
        ));
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
            'branch_id' => ['nullable', 'integer'],
        ]);

        $location = strtoupper($this->resolveLocationFromBranch(
            $request,
            (int) ($validated['branch_id'] ?? 0)
        ));
        $query = trim((string) ($validated['query'] ?? ''));
        $make = trim($validated['make']);
        $model = trim($validated['model']);

        $items = $client->suggestTrims($make, $model, $query, $location);

        return response()->json([
            'items' => $items,
        ]);
    }

    public function type(Request $request, VehicleAiClient $client): JsonResponse
    {
        $validated = $request->validate([
            'make' => ['required', 'string', 'max:40'],
            'model' => ['required', 'string', 'max:40'],
            'variant' => ['nullable', 'string', 'max:60'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        $location = $this->resolveLocationFromBranch(
            $request,
            (int) ($validated['branch_id'] ?? 0)
        );

        $type = $client->suggestVehicleType(
            trim($validated['make']),
            trim($validated['model']),
            trim((string) ($validated['variant'] ?? '')),
            $location
        );

        return response()->json([
            'type' => $type,
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

    private function getFleetUser(Request $request): array
    {
        $fleetUser = $request->session()->get('sharpfleet.user');
        if (!$fleetUser || empty($fleetUser['organisation_id'])) {
            abort(403, 'No SharpFleet organisation context.');
        }

        return $fleetUser;
    }

    private function resolveBranchScope(array $fleetUser, int $organisationId, BranchService $branchService): array
    {
        $bypassBranchRestrictions = Roles::bypassesBranchRestrictions($fleetUser);
        $branchAccessEnabled = $branchService->branchesEnabled()
            && $branchService->vehiclesHaveBranchSupport()
            && $branchService->userBranchAccessEnabled();

        $branchScopeEnabled = $branchAccessEnabled && !$bypassBranchRestrictions;
        $accessibleBranchIds = $branchScopeEnabled
            ? $branchService->getAccessibleBranchIdsForUser($organisationId, (int) ($fleetUser['id'] ?? 0))
            : [];

        if ($branchScopeEnabled && count($accessibleBranchIds) === 0) {
            abort(403, 'No branch access.');
        }

        $accessibleBranchIds = array_values(array_unique(array_map('intval', $accessibleBranchIds)));

        return [$branchScopeEnabled, $accessibleBranchIds];
    }

    private function resolveBranchId(
        array $fleetUser,
        int $organisationId,
        BranchService $branchService,
        int $branchId
    ): ?int {
        if (!$branchService->branchesEnabled() || !$branchService->vehiclesHaveBranchSupport()) {
            return null;
        }

        [$branchScopeEnabled, $accessibleBranchIds] = $this->resolveBranchScope(
            $fleetUser,
            $organisationId,
            $branchService
        );

        if ($branchScopeEnabled) {
            if ($branchId > 0) {
                if (!in_array($branchId, $accessibleBranchIds, true)) {
                    abort(403, 'No branch access.');
                }
                return $branchId;
            }

            return count($accessibleBranchIds) > 0 ? (int) $accessibleBranchIds[0] : null;
        }

        if ($branchId > 0) {
            return $branchId;
        }

        return (int) ($branchService->ensureDefaultBranch($organisationId) ?? 0);
    }

    private function resolveLocationFromBranch(Request $request, int $branchId): string
    {
        $fleetUser = $this->getFleetUser($request);
        $organisationId = (int) $fleetUser['organisation_id'];
        $settingsService = new CompanySettingsService($organisationId);

        $timezone = $settingsService->timezone();
        $branchService = new BranchService();
        $resolvedBranchId = $this->resolveBranchId($fleetUser, $organisationId, $branchService, $branchId);

        if ($resolvedBranchId) {
            $branch = $branchService->getBranch($organisationId, $resolvedBranchId);
            $branchTimezone = $branch && isset($branch->timezone) ? trim((string) $branch->timezone) : '';
            if ($branchTimezone !== '') {
                $timezone = $branchTimezone;
            }
        }

        return $this->countryFromTimezone($timezone);
    }
}
