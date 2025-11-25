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
    public function logs(Request $request)
{
    $q        = $request->query('q');
    $type     = $request->query('type');      // verification, general, all
    $status   = $request->query('status');    // success, failed, all
    $from     = $request->query('from');      // yyyy-mm-dd
    $to       = $request->query('to');        // yyyy-mm-dd

    $logs = SmsVerificationLog::query()

        // 🔍 Search: phone, message, code, names, admin
        ->when($q, function ($query) use ($q) {
            $query->where(function ($q2) use ($q) {
                $q2->where('phone', 'LIKE', "%$q%")
                   ->orWhere('message', 'LIKE', "%$q%")
                   ->orWhere('verification_code', 'LIKE', "%$q%")
                   ->orWhere('admin_name', 'LIKE', "%$q%")
                   ->orWhere('recipient_name', 'LIKE', "%$q%");
            });
        })

        // 🔵 Filter by Type
        ->when($type === 'verification', function ($query) {
            $query->whereNotNull('customer_profile_id');
        })
        ->when($type === 'general', function ($query) {
            $query->whereNull('customer_profile_id');
        })

        // 🔴 Filter by Status
        ->when($status === 'success', fn($q) => $q->where('status', 'success'))
        ->when($status === 'failed',  fn($q) => $q->where('status', '!=', 'success'))

        // 📅 Filter: Date Range
        ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
        ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to))

        ->latest()
        ->paginate(20)
        ->appends($request->query());

    return view('admin.sms.logs', compact('logs', 'q', 'type', 'status', 'from', 'to'));
}



    public function general()
    {
        return view('admin.sms.general');
    }

    public function sendGeneral(Request $request, MobileMessageService $smsService)
{
    $request->validate([
        'phone'   => 'required|string',
        'message' => 'required|string',
        'name'    => 'nullable|string',
    ]);

    $adminName = session('admin_user')['displayName'] ?? 'Admin';

    try {
        // Send SMS
        $response = $smsService->sendMessage(
            to: $request->phone,
            message: $request->message,
            customRef: 'general_' . now()->timestamp
        );

        // Parse response
        $result = $response['results'][0] ?? [];
        $status = $result['status'] ?? 'unknown';
        $messageId = $result['message_id'] ?? null;

        // Log it
        SmsVerificationLog::create([
            'admin_id'            => null, // SSO admins don't use DB
            'admin_name'          => $adminName,
            'customer_profile_id' => null,
            'recipient_name'      => $request->name,
            'phone'               => $request->phone,
            'message'             => $request->message,
            'verification_code'   => null,
            'message_id'          => $messageId,
            'status'              => $status,
        ]);

        return back()->with('success', 'SMS sent successfully!')
                     ->with('response', $response);

    } catch (\Throwable $e) {
        return back()->with('error', $e->getMessage());
    }
}

public function searchRecipients(Request $request)
{
    $q = $request->query('q');

    if (!$q || strlen($q) < 2) {
        return response()->json([]);
    }

    $results = [];

    /** 1. Search Customer Profiles */
    $profiles = \DB::table('customer_profiles')
        ->where('mobile_number', 'LIKE', "%$q%")
        ->orWhere('business_name', 'LIKE', "%$q%")
        ->orWhere('authority_contact', 'LIKE', "%$q%")
        ->limit(10)
        ->get();

    foreach ($profiles as $p) {
        $results[] = [
            'label' => "{$p->contact_name} – {$p->mobile_number} (Profile)",
            'name'  => $p->contact_name,
            'phone' => $p->mobile_number,
            'type'  => 'profile',
            'id'    => $p->id,
        ];
    }

    /** 2. Search Customer Contacts */
    $contacts = \DB::table('customer_contacts')
        ->where('phone', 'LIKE', "%$q%")
        ->orWhere('contact_name', 'LIKE', "%$q%")
        ->limit(10)
        ->get();

    foreach ($contacts as $c) {
        $results[] = [
            'label' => "{$c->contact_name} – {$c->phone} (Contact)",
            'name'  => $c->contact_name,
            'phone' => $c->phone,
            'type'  => 'contact',
            'id'    => $c->id,
        ];
    }

    /** 3. Search CRM Users */
    $users = \DB::table('users')
        ->where('phone', 'LIKE', "%$q%")
        ->orWhere('first_name', 'LIKE', "%$q%")
        ->orWhere('last_name', 'LIKE', "%$q%")
        ->limit(10)
        ->get();

    foreach ($users as $u) {
        $results[] = [
            'label' => "{$u->first_name} {$u->last_name} – {$u->phone} (User)",
            'name'  => "{$u->first_name} {$u->last_name}",
            'phone' => $u->phone,
            'type'  => 'user',
            'id'    => $u->id,
        ];
    }

    return response()->json($results);
}


}