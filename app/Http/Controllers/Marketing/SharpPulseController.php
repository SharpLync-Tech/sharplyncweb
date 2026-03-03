<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing\Campaign;

class SharpPulseController extends Controller
{
    public function index()
    {
        $emails = Campaign::where('status', 'sent')
            ->whereNotNull('sent_at')
            ->orderByDesc('sent_at')
            ->get();

        return view('marketing.sharppulse', [
            'emails' => $emails,
        ]);
    }
}
