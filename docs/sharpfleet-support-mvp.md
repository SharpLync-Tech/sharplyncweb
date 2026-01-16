# SharpFleet Simple Support Portal (MVP)

## Goal
A lightweight, SharpFleet-only support portal that lets users log issues, receive email confirmations, and follow a basic reply thread. Support staff can reply, update status, and resolve/close tickets. This is intentionally simpler than SharpLync.

## Why Separate From SharpLync
A separate SharpFleet support system is easier for this scope because it avoids cross-system user mapping and session/guard complexity. SharpFleet already has its own auth/session (`sharpfleet.user`), so a SharpFleet-only system can stay small and cohesive.

## Feature Scope (MVP)
- Customer portal
  - Create ticket (subject + message)
  - List my tickets
  - View ticket thread
  - Reply to ticket
  - View ticket status (open/pending/resolved/closed)
- Support/staff portal
  - List all tickets
  - View ticket thread
  - Reply to ticket
  - Update status (open/pending/resolved/closed)
- Email notifications
  - New ticket -> support email
  - Staff reply -> customer email
  - Customer reply -> support email
- Auth
  - Uses SharpFleet session (`sharpfleet.user`)
  - Staff access restricted to SharpFleet admin roles

## Optional (Defer Until Later)
- Attachments
- Priority levels
- Internal notes
- SLA or reporting

## Data Model (SharpFleet DB)
### Table: `sf_support_tickets`
- id (pk)
- user_id (SharpFleet user id)
- subject
- status (open, pending, resolved, closed)
- created_at
- updated_at
- closed_at (nullable)

### Table: `sf_support_replies`
- id (pk)
- ticket_id (fk)
- user_id (SharpFleet user id)
- is_staff (bool)
- message
- created_at

## Routes (Proposed)
### Customer
- GET  `/app/sharpfleet/support`             -> list
- GET  `/app/sharpfleet/support/create`      -> new ticket form
- POST `/app/sharpfleet/support`             -> create ticket
- GET  `/app/sharpfleet/support/{ticket}`    -> show ticket
- POST `/app/sharpfleet/support/{ticket}/reply` -> add reply

### Staff
- GET  `/app/sharpfleet/admin/support`          -> list
- GET  `/app/sharpfleet/admin/support/{ticket}` -> show ticket
- POST `/app/sharpfleet/admin/support/{ticket}/reply` -> add reply
- POST `/app/sharpfleet/admin/support/{ticket}/status` -> update status

## Controllers (Proposed)
- `App\Http\Controllers\SharpFleet\Support\TicketController`
- `App\Http\Controllers\SharpFleet\Support\ReplyController`
- `App\Http\Controllers\SharpFleet\Admin\SupportTicketController`

## Views (Proposed)
- `resources/views/sharpfleet/support/index.blade.php`
- `resources/views/sharpfleet/support/create.blade.php`
- `resources/views/sharpfleet/support/show.blade.php`
- `resources/views/sharpfleet/admin/support/index.blade.php`
- `resources/views/sharpfleet/admin/support/show.blade.php`

## Email (Proposed)
- `SupportTicketCreated` (to support)
- `SupportTicketReplyCustomer` (to customer)
- `SupportTicketReplySupport` (to support)

## Implementation Effort (Rough)
- Simple MVP: low effort (few days)
- Full parity with SharpLync: not intended for this path

## Open Questions
- Should staff be SharpFleet admins only, or a dedicated support role?
- Attachments: skip for MVP or include from day one?
- Do customers need priority or just status?

## Recommendation
Proceed with the MVP scope above. It is simpler and quicker than integrating with SharpLync for this lightweight support experience.
