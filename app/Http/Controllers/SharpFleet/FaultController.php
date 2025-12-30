<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\FaultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaultController extends Controller
{
    protected FaultService $faultService;

    public function __construct(FaultService $faultService)
    {
        $this->faultService = $faultService;
    }

    public function storeFromTrip(Request $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'trip_id' => ['required', 'integer'],
            'severity' => ['required', 'string', 'in:minor,major,critical'],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $this->faultService->createFaultFromTrip($user, $validated);

        return back()->with('success', 'Incident reported successfully.');
    }

    public function storeStandalone(Request $request): RedirectResponse
    {
        $user = session('sharpfleet.user');

        if (!$user) {
            abort(401, 'Not authenticated');
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
            'severity' => ['required', 'string', 'in:minor,major,critical'],
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $this->faultService->createFaultStandalone($user, $validated);

        return back()->with('success', 'Incident reported successfully.');
    }
}
