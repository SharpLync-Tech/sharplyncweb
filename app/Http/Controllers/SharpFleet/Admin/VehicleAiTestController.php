<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\VehicleAiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleAiTestController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('sharpfleet.admin.vehicles-ai-test');
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
}
