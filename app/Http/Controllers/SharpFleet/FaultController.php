<?php

namespace App\Http\Controllers\SharpFleet;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\FaultService;

class FaultController extends Controller
{
    protected FaultService $faultService;

    public function __construct(FaultService $faultService)
    {
        $this->faultService = $faultService;
    }

    public function storeFromTrip()
    {
        // $this->faultService->createFaultFromTrip()
    }

    public function storeStandalone()
    {
        // $this->faultService->createFaultStandalone()
    }
}
