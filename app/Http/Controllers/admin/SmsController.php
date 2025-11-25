<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MobileMessageService;
use App\Models\Admin\SmsVerificationLog;
use App\Models\CRM\CustomerProfile;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    /**
     * Show the SMS sending page.
     * Can receive ?phone= and ?customer_id= from the Customer Profile page.
     */
    public function index(Request $request)
    {
        $prefillPhone = $request->query('phone');
        $customerId   = $request->query('customer_id');

        $customer = null;
        if ($customerId) {
            $customer = CustomerProfile::find($customerId);
        }

        return view('admin.sms.index', compact('prefillPhone', 'customer'));
    }

    /**
     * Send SMS (custom or auto-generated 6-digit code).
     */
    public function send(Request $request, MobileMessageService $smsService)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'nullable|string',
            'use_code' => 'nullable|boolean',
        ]);

        // In your setup, Auth::id() may be null (SSO only),
        // but we keep the field for future compatibility.
        $adminId   = Auth::id();
        $adminName = session('admin_user')['displayName'] ?? 'Admin';

        $customerId = $request->input('customer_id');

        // Generate code if toggled
        $verificationCode = null;
        $messageToSend = $request->message;

        if ($request->use_code) {
            $verificationCode = rand(100000, 999999);
            $messageToSend = "Your SharpLync verification code is {$verificationCode}";
        }

        try {
            // Send SMS via MobileMessage
            $response = $smsService->sendMessage(
                to: $request->phone,
                message: $messageToSend,
                customRef: 'admin_' . now()->timestamp
            );

            // Extract status + message_id
            $result    = $response['results'][0] ?? [];
            $status    = $result['status'] ?? 'unknown';
            $messageId = $result['message_id'] ?? null;

            // Store log
            SmsVerificationLog::create([
                'admin_id'            => $adminId,
                'admin_name'          => $adminName,
                'customer_profile_id' => $customerId,
                'phone'               => $request->phone,
                'message'             => $messageToSend,
                'verification_code'   => $verificationCode,
                'message_id'          => $messageId,
                'status'              => $status,
            ]);

            return back()->with('success', 'SMS sent successfully!')
                         ->with('response', $response);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show paginated SMS logs.
     */
    public function logs()
    {
        $logs = SmsVerificationLog::latest()->paginate(20);

        return view('admin.sms.logs', compact('logs'));
    }
}
