<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Show the onboarding form.
     */
    public function create()
    {
        return view('customers.customer-onboard');
    }

    /**
     * Handle form submission and create customer + SSPIN + email.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'company_name' => 'nullable|string|max:150',
            'email'        => 'required|email|unique:customers',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'postcode'     => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // ✅ Create the customer record
            $customer = Customer::create($validated);

            // ✅ Generate random SharpLync Support PIN (SSPIN)
            $sspin = random_int(100000, 999999);

            // ✅ Save SSPIN linked to customer
            DB::table('support_pins')->insert([
                'customer_id' => $customer->id,
                'sspin'       => $sspin,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            // ✅ Send welcome email
            $this->sendWelcomeEmail($customer, $sspin);

            // ✅ Display success message
            return redirect()
                ->route('customers.create')
                ->with('success', "Your SharpLync Support PIN (SSPIN) is {$sspin}. Keep it safe — it helps us verify you during remote support.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating record: ' . $e->getMessage());
        }
    }

    /**
     * Send Welcome Email to the customer.
     */
    private function sendWelcomeEmail(Customer $customer, string $sspin): void
    {
        $to = $customer->email;
        $subject = 'Welcome to SharpLync - Your Support PIN';
        $message = <<<HTML
        <div style="font-family:Poppins,Arial,sans-serif; color:#0A2A4D; background:#F7F9FB; padding:30px; border-radius:10px;">
            <h2 style="color:#104946;">Welcome to SharpLync, {$customer->first_name}!</h2>
            <p>We’re thrilled to have you on board. To help keep your information secure, we’ve assigned you a unique <strong>SharpLync Support PIN (SSPIN)</strong>.</p>

            <div style="background:#E8F6F4; border-left:5px solid #2CBFAE; padding:15px; margin:20px 0; font-size:1.2rem;">
                <strong>Your SSPIN:</strong> <span style="color:#104946; font-weight:700;">{$sspin}</span>
            </div>

            <p>Please keep this PIN safe — our support team will ask for it before any remote assistance session.</p>

            <p>You can also download our Quick Support app anytime:</p>
            <p>
                <a href="https://sharplync.com.au/quick-support" style="background:#104946; color:#fff; padding:10px 20px; text-decoration:none; border-radius:6px;">Download SharpLync Quick Support</a>
            </p>

            <p>Thank you for choosing SharpLync — old school support, modern results.</p>
            <p>Warm regards,<br><strong>The SharpLync Team</strong></p>
        </div>
        HTML;

        Mail::html($message, function ($mail) use ($to, $subject) {
            $mail->to($to)->subject($subject);
        });
    }
}