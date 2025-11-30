<?php

namespace App\Http\Controllers\SupportAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportAdmin\SupportTicket;
use App\Models\SupportAdmin\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::with(['customerUser', 'customerProfile'])
            ->withCount(['publicReplies as messages_count'])
            ->filter($request->all())
            ->paginate(15)
            ->withQueryString();

        $statusOptions = $this->statusOptions();
        $priorityOptions = $this->priorityOptions();

        return view('support-admin.tickets.index', compact('tickets', 'statusOptions', 'priorityOptions'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['customerUser', 'customerProfile']);

        $messages = $ticket->publicReplies()
            ->orderBy('created_at', 'asc')
            ->get();

        $internalNotes = $ticket->internalNotes()
            ->orderBy('created_at', 'asc')
            ->get();

        $statusOptions = $this->statusOptions();
        $priorityOptions = $this->priorityOptions();

        return view('support-admin.tickets.show', compact(
            'ticket',
            'messages',
            'internalNotes',
            'statusOptions',
            'priorityOptions'
        ));
    }

    public function updateStatus(SupportTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:50'],
        ]);

        $ticket->status = $data['status'];

        if ($data['status'] === 'closed') {
            $ticket->closed_at = now();
        } else {
            $ticket->closed_at = null;
        }

        $ticket->save();

        return back()->with('success', 'Status updated.');
    }

    public function updatePriority(SupportTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'priority' => ['required', 'string', 'max:50'],
        ]);

        $ticket->priority = $data['priority'];
        $ticket->save();

        return back()->with('success', 'Priority updated.');
    }

    public function quickResolve(SupportTicket $ticket)
    {
        $ticket->status = 'resolved';
        $ticket->closed_at = now();
        $ticket->save();

        return back()->with('success', 'Ticket marked as resolved.');
    }

    public function reply(SupportTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_type' => 'admin',
            'user_id'   => Auth::id(),
            'message'   => $data['message'],
            'is_internal' => 0,
        ]);


        $ticket->last_reply_at = now();

        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            $ticket->status = 'pending';
            $ticket->closed_at = null;
        }

        $ticket->save();

        return back()->with('success', 'Reply added.');
    }

    public function storeInternalNote(SupportTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_type' => 'admin',
            'user_id'   => Auth::id(),
            'message'   => $data['message'],
            'is_internal' => 1,
        ]);


        return back()->with('success', 'Internal note added.');
    }

    protected function statusOptions(): array
    {
        return [
            'open'                  => 'Open',
            'pending'               => 'Pending',
            'waiting_on_customer'   => 'Waiting on customer',
            'waiting_on_third_party'=> 'Waiting on third party',
            'waiting_on_stock'      => 'Waiting on stock',
            'resolved'              => 'Resolved',
            'closed'                => 'Closed',
        ];
    }

    protected function priorityOptions(): array
    {
        return [
            'low'    => 'Low',
            'medium' => 'Medium',
            'high'   => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function download(\App\Models\Support\TicketReply $reply)
    {
        // Must be logged in as admin
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            abort(403);
        }

        if (!$reply->attachment_path || !\Storage::exists($reply->attachment_path)) {
            abort(404, 'File not found');
        }

        return \Storage::download(
            $reply->attachment_path,
            $reply->attachment_original_name,
            ['Content-Type' => $reply->attachment_mime]
        );
    }

}
