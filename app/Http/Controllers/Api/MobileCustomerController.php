<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileCustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !isset($user['organisation_id'])) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Customers are optional – table may not exist
        if (!Schema::connection('sharpfleet')->hasTable('customers')) {
            return response()->json(['customers' => []]);
        }

        $customers = DB::connection('sharpfleet')
            ->table('customers')
            ->where('organisation_id', (int) $user['organisation_id'])
            ->where('is_active', 1)
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name']);

        return response()->json([
            'customers' => $customers->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => (string) $c->name,
            ])->values(),
        ]);
    }
}
