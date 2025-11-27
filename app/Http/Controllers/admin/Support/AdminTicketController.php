<?php

namespace App\Http\Controllers\Admin\Support;

use App\Http\Controllers\Controller;
use App\Models\AdminSupport\AdminTicket;
use App\Models\AdminSupport\AdminTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTicketController extends Controller
{
    /**
     * List all support tickets for admin.
     */
    public function index(Request $request)
    {
        $tickets = AdminTicket::with(['customerUser', 'customerProfile'])
            ->withCount(['publicReplies as messages_count'])
            ->filter($request->all())
            ->paginate(20)
            ->withQueryString();

        return view('admin.support.tickets.index', compact('tickets'));
    }

    /**
     * Show a single ticket with conversation + notes.
     */
    public function show(AdminTicket $ticket)
    {
        $ticket->load(['customerUser', 'customerProfile']);

        $messages = $ticket->publicReplies()
            ->orderBy('created_at', 'asc')
            ->get();

        $internalNotes = $ticket->internalNotes()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.support.tickets.show', [
            'ticket'        => $ticket,
            'messages'      => $messages,
            'internalNotes' => $internalNotes,
        ]);
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(AdminTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,pending,resolved,closed'],
        ]);

        $ticket->status = $data['status'];

        if ($data['status'] === 'closed') {
            $ticket->closed_at = now();
        } elseif ($data['status'] === 'open') {
            $ticket->closed_at = null;
        }

        $ticket->save();

        return back()->with('success', 'Ticket status updated.');
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(AdminTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $ticket->priority = $data['priority'];
        $ticket->save();

        return back()->with('success', 'Ticket priority updated.');
    }

    /**
     * Quick resolve action from index view.
     */
    public function quickResolve(AdminTicket $ticket)
    {
        $ticket->status = 'resolved';
        $ticket->closed_at = now();
        $ticket->save();

        return back()->with('success', 'Ticket marked as resolved.');
    }

    /**
     * Admin reply to customer.
     */
    public function reply(AdminTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        AdminTicketReply::create([
            'ticket_id'   => $ticket->id,
            'customer_id' => $ticket->customer_id,
            'admin_id'    => Auth::id(),
            'message'     => $data['message'],
            'is_internal' => 0,
        ]);

        $ticket->last_reply_at = now();

        // If previously resolved/closed, set back to pending
        if (in_array($ticket->status, ['resolved', 'closed'])) {
            $ticket->status = 'pending';
            $ticket->closed_at = null;
        }

        $ticket->save();

        return back()->with('success', 'Reply sent to customer.');
    }

    /**
     * Store an internal note (admin only).
     */
    public function storeInternalNote(AdminTicket $ticket, Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        AdminTicketReply::create([
            'ticket_id'   => $ticket->id,
            'customer_id' => $ticket->customer_id,
            'admin_id'    => Auth::id(),
            'message'     => $data['message'],
            'is_internal' => 1,
        ]);

        return back()->with('success', 'Internal note saved.');
    }
}
