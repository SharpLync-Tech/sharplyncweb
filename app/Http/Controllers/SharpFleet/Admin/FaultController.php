<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\FaultService;

class FaultController extends Controller
{
    protected FaultService $faultService;

    public function __construct(FaultService $faultService)
    {
        $this->faultService = $faultService;
    }

    public function index()
    {
        // List faults
    }

    public function updateStatus($fault)
    {
        // $this->faultService->updateFaultStatus()
    }
}
