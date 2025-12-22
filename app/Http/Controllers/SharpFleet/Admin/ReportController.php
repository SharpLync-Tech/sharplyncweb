<?php

namespace App\Http\Controllers\SharpFleet\Admin;

use App\Http\Controllers\Controller;
use App\Services\SharpFleet\ReportService;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function trips()
    {
        // $this->reportService->tripReport()
    }

    public function vehicles()
    {
        // $this->reportService->vehicleReport()
    }
}
