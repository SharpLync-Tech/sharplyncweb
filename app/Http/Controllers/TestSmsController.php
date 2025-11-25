<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MobileMessageService;

class TestSmsController extends Controller
{
    public function form()
    {
        return view('test-sms.form');
    }

    public function send(Request $request, MobileMessageService $sms)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:300',
        ]);

        try {
            $response = $sms->sendMessage(
                $request->phone,
                $request->message,
                customRef: 'test_' . now()->timestamp
            );

            return back()->with('success', 'SMS sent!')->with('response', $response);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
