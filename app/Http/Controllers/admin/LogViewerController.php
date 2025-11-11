<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class LogViewerController extends Controller
{
    /**
     * Display the registration log.
     */
    public function index(Request $request)
    {
        $logFile = storage_path('logs/registration.log');
        $logContent = '';
        $logLines = [];

        if (File::exists($logFile)) {
            $logContent = File::get($logFile);
            $logLines = explode("\n", $logContent);
        }

        // Optional: simple search filter
        $search = $request->input('search');
        if ($search) {
            $logLines = array_filter($logLines, fn($line) => stripos($line, $search) !== false);
        }

        return view('admin.registration-log', compact('logLines', 'search'));
    }

    /**
     * Clear the registration log.
     */
    public function clear()
    {
        $logFile = storage_path('logs/registration.log');

        if (File::exists($logFile)) {
            File::put($logFile, "=== Log cleared on " . now()->toDateTimeString() . " ===\n");
        }

        return redirect()->route('admin.registration.log')
            ->with('status', 'Registration log cleared successfully.');
    }
}