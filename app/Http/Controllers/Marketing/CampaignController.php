<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marketing\Campaign;
use App\Models\Marketing\EmailSubscriber;
use App\Jobs\Marketing\SendCampaignEmailJob;
use App\Services\Marketing\MarketingAiClient;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    public function index()
    {
        $brandScope = $this->brandScope();

        $campaignQuery = Campaign::orderByDesc('id');

        if ($brandScope !== 'both') {
            $campaignQuery->where('brand', $brandScope);
        }

        $campaigns = $campaignQuery->get();

        $subscriberQuery = EmailSubscriber::where('status', 'subscribed');

        if ($brandScope !== 'both') {
            $subscriberQuery->where('brand', $brandScope);
        }

        $subscriberCount = $subscriberQuery->count();

        return view('marketing.admin.campaigns.index', [
            'campaigns' => $campaigns,
            'subscriberCount' => $subscriberCount,
            'brandScope' => $brandScope,
            'role' => $this->role(),
        ]);
    }

    public function logs()
    {
        $this->requireRole(['reviewer', 'sender', 'admin']);

        $brandScope = $this->brandScope();

        $logsQuery = \App\Models\Marketing\EmailSend::query()
            ->with(['campaign', 'subscriber'])
            ->orderByDesc('sent_at');

        if ($brandScope !== 'both') {
            $logsQuery->whereHas('campaign', function ($query) use ($brandScope) {
                $query->where('brand', $brandScope);
            });
        }

        $logs = $logsQuery->limit(500)->get();

        return view('marketing.admin.logs.index', [
            'logs' => $logs,
            'brandScope' => $brandScope,
        ]);
    }

    public function upload(Request $request)
    {
        $this->requireRole(['creator', 'reviewer', 'sender', 'admin']);

        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = 'marketing-' . now()->format('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

        $targetDir = public_path('uploads/marketing');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return response()->json([
            'url' => asset('uploads/marketing/' . $filename),
        ]);
    }

    public function generateAi(Request $request, MarketingAiClient $client)
    {
        $this->requireRole(['creator', 'reviewer', 'sender', 'admin']);

        $validated = $request->validate([
            'brand' => ['required', 'in:sl,sf'],
            'goal' => ['required', 'string', 'max:500'],
            'audience' => ['required', 'string', 'max:500'],
            'key_points' => ['nullable', 'string', 'max:2000'],
            'tone' => ['required', 'string', 'max:100'],
            'fluff' => ['required', 'in:none,light,rich'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:300'],
        ]);

        $this->assertBrandAllowed($validated['brand']);

        $result = $client->generateEmail($validated);

        if (isset($result['error'])) {
            Log::warning('[MARKETING AI] Generation failed', [
                'error' => $result['error'],
                'brand' => $validated['brand'],
                'tone' => $validated['tone'],
            ]);
        }

        return response()->json($result);
    }

    public function create()
    {
        $this->requireRole(['creator', 'reviewer', 'sender', 'admin']);

        return view('marketing.admin.campaigns.create', [
            'brandScope' => $this->brandScope(),
        ]);
    }

    public function edit($id)
    {
        $this->requireRole(['creator', 'reviewer', 'sender', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if (!in_array($campaign->status, ['draft', 'pending_review'], true)) {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Only draft campaigns can be edited.');
        }

        return view('marketing.admin.campaigns.edit', [
            'campaign' => $campaign,
            'brandScope' => $this->brandScope(),
        ]);
    }

    public function store(Request $request)
    {
        $this->requireRole(['creator', 'reviewer', 'sender', 'admin']);

        $validated = $request->validate([
            'brand' => ['required', 'in:sl,sf'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:190'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:300'],
            'body_html' => ['nullable', 'string'],
            'template_view' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'string', 'max:255'],
        ]);

        $brand = $validated['brand'];
        $this->assertBrandAllowed($brand);

        $templateView = $validated['template_view'] ?? null;
        if (!$templateView) {
            $templateView = $brand === 'sf'
                ? 'emails.marketing.templates.sf-basic'
                : 'emails.marketing.templates.sl-basic';
        }

        try {
            $campaign = Campaign::create([
                'brand' => $brand,
                'name' => $validated['name'],
                'subject' => $validated['subject'],
                'preheader' => $validated['preheader'] ?? null,
                'cta_text' => $validated['cta_text'] ?? null,
                'cta_url' => $validated['cta_url'] ?? null,
                'body_html' => $validated['body_html'] ?? '',
                'template_view' => $templateView,
                'hero_image' => $validated['hero_image'] ?? null,
                'status' => 'draft',
            ]);

            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('success', 'Campaign created (ID: ' . $campaign->id . ').');
        } catch (\Throwable $e) {
            Log::error('[MARKETING] Campaign create failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->route('marketing.admin.campaigns.create')
                ->withInput()
                ->with('error', 'Create failed: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->requireRole(['creator', 'reviewer', 'sender', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if (!in_array($campaign->status, ['draft', 'pending_review'], true)) {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Only draft campaigns can be updated.');
        }

        $validated = $request->validate([
            'brand' => ['required', 'in:sl,sf'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:190'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:300'],
            'body_html' => ['nullable', 'string'],
            'template_view' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'string', 'max:255'],
        ]);

        $brand = $validated['brand'];
        $this->assertBrandAllowed($brand);

        $templateView = $validated['template_view'] ?? null;
        if (!$templateView) {
            $templateView = $brand === 'sf'
                ? 'emails.marketing.templates.sf-basic'
                : 'emails.marketing.templates.sl-basic';
        }

        $campaign->brand = $brand;
        $campaign->name = $validated['name'];
        $campaign->subject = $validated['subject'];
        $campaign->preheader = $validated['preheader'] ?? null;
        $campaign->cta_text = $validated['cta_text'] ?? null;
        $campaign->cta_url = $validated['cta_url'] ?? null;
        $campaign->body_html = $validated['body_html'] ?? '';
        $campaign->template_view = $templateView;
        $campaign->hero_image = $validated['hero_image'] ?? null;

        if ($campaign->status === 'pending_review') {
            $campaign->status = 'draft';
        }

        $campaign->save();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign updated.');
    }

    public function submitForReview($id)
    {
        $this->requireRole(['creator', 'reviewer', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if ($campaign->status !== 'draft') {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Only draft campaigns can be submitted.');
        }

        $campaign->status = 'pending_review';
        $campaign->save();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign submitted for review.');
    }

    public function approve($id)
    {
        $this->requireRole(['reviewer', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if ($campaign->status !== 'pending_review') {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Only pending campaigns can be approved.');
        }

        $campaign->status = 'approved';
        $campaign->save();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign approved.');
    }

    public function schedule(Request $request, $id)
    {
        $this->requireRole(['reviewer', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
        ]);

        if (!in_array($campaign->status, ['approved', 'scheduled'], true)) {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Only approved campaigns can be scheduled.');
        }

        $campaign->status = 'scheduled';
        $campaign->scheduled_at = $validated['scheduled_at'];
        $campaign->save();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign scheduled.');
    }

    public function preview($id)
    {
        $this->requireRole(['creator', 'reviewer', 'sender', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        $template = $campaign->template_view;
        if (!$template) {
            $template = $campaign->brand === 'sf'
                ? 'emails.marketing.templates.sf-basic'
                : 'emails.marketing.templates.sl-basic';
        }

        $data = array_merge($campaign->body_json ?? [], [
            'campaign' => $campaign,
            'subscriber' => null,
            'brand' => $campaign->brand,
            'heroImage' => $campaign->hero_image,
            'unsubscribeUrl' => null,
            'subject' => $campaign->subject,
            'preheader' => $campaign->preheader,
            'ctaText' => $campaign->cta_text,
            'ctaUrl' => $campaign->cta_url,
            'bodyHtml' => $campaign->body_html,
        ]);

        return view($template, $data);
    }

    public function sendNowWeb($id)
    {
        $this->requireRole(['sender', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if ($campaign->status === 'sent') {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('success', 'Campaign already sent.');
        }

        if (!in_array($campaign->status, ['approved', 'scheduled'], true)) {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Only approved campaigns can be sent.');
        }

        $count = $this->dispatchCampaign($campaign);

        $campaign->status = 'sent';
        $campaign->sent_at = now();
        $campaign->scheduled_at = null;
        $campaign->save();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign dispatched to ' . $count . ' subscribers.');
    }

    public function resend($id)
    {
        $this->requireRole(['sender', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if ($campaign->status !== 'sent') {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Only sent campaigns can be resent.');
        }

        \App\Models\Marketing\EmailSend::where('campaign_id', $campaign->id)->delete();

        $count = $this->dispatchCampaign($campaign);

        $campaign->sent_at = now();
        $campaign->save();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign resent to ' . $count . ' subscribers.');
    }

    public function sendTest($id)
    {
        $this->requireRole(['reviewer', 'sender', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        $template = $campaign->template_view;
        if (!$template) {
            $template = $campaign->brand === 'sf'
                ? 'emails.marketing.templates.sf-basic'
                : 'emails.marketing.templates.sl-basic';
        }

        $payload = array_merge($campaign->body_json ?? [], [
            'campaign' => $campaign,
            'subscriber' => null,
            'brand' => $campaign->brand,
            'heroImage' => $campaign->hero_image,
            'unsubscribeUrl' => null,
            'preferencesUrl' => null,
            'subject' => $campaign->subject,
            'preheader' => $campaign->preheader,
            'ctaText' => $campaign->cta_text,
            'ctaUrl' => $campaign->cta_url,
            'bodyHtml' => $campaign->body_html,
        ]);

        $testEmail = 'jannie.brits@sharplync.com.au';
        $fromAddress = config('mail.from.address');
        $fromName = $campaign->brand === 'sf' ? 'SharpFleet' : 'SharpLync';

        \Mail::send(
            $template,
            $payload,
            function ($message) use ($campaign, $testEmail, $fromAddress, $fromName) {
                if ($fromAddress) {
                    $message->from($fromAddress, $fromName);
                }
                $message->to($testEmail)
                    ->subject('[TEST] ' . $campaign->subject);
            }
        );

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Test email sent to ' . $testEmail);
    }

    public function sendNow($id)
    {
        $this->requireRole(['sender', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if ($campaign->status === 'sent') {
            return response()->json(['message' => 'Campaign already sent.']);
        }

        if (!in_array($campaign->status, ['approved', 'scheduled'], true)) {
            return response()->json(['message' => 'Campaign not approved.'], 422);
        }

        $count = $this->dispatchCampaign($campaign);

        $campaign->status = 'sent';
        $campaign->sent_at = now();
        $campaign->scheduled_at = null;
        $campaign->save();

        return response()->json([
            'message' => 'Campaign dispatched to ' . $count . ' subscribers.',
        ]);
    }

    public function processScheduled()
    {
        $this->requireRole(['sender', 'admin']);

        $now = now();

        $campaigns = Campaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->get();

        $processed = 0;

        foreach ($campaigns as $campaign) {
            $this->dispatchCampaign($campaign);

            $campaign->status = 'sent';
            $campaign->sent_at = now();
            $campaign->save();

            $processed++;
        }

        return response()->json([
            'processed' => $processed,
        ]);
    }

    public function destroy($id)
    {
        $this->requireRole(['reviewer', 'admin']);

        $campaign = Campaign::findOrFail($id);
        $this->assertBrandAllowed($campaign->brand);

        if ($campaign->status === 'sent') {
            return redirect()
                ->route('marketing.admin.campaigns')
                ->with('error', 'Sent campaigns cannot be deleted.');
        }

        $campaign->delete();

        return redirect()
            ->route('marketing.admin.campaigns')
            ->with('success', 'Campaign deleted.');
    }

    private function dispatchCampaign(Campaign $campaign): int
    {
        $subscriberQuery = EmailSubscriber::where('status', 'subscribed')
            ->where('brand', $campaign->brand);

        $count = $subscriberQuery->count();

        $subscriberQuery->chunkById(200, function ($subscribers) use ($campaign) {
            foreach ($subscribers as $subscriber) {
                SendCampaignEmailJob::dispatch($campaign->id, $subscriber->id);
            }
        });

        return $count;
    }

    private function role(): string
    {
        return (string) (session('marketing_user.role') ?? 'creator');
    }

    private function brandScope(): string
    {
        return (string) (session('marketing_user.brand_scope') ?? 'both');
    }

    private function requireRole(array $allowed): void
    {
        if (!in_array($this->role(), $allowed, true)) {
            abort(403, 'Marketing role required.');
        }
    }

    private function assertBrandAllowed(string $brand): void
    {
        $scope = $this->brandScope();
        if ($scope !== 'both' && $brand !== $scope) {
            abort(403, 'Brand access only.');
        }
    }
}
