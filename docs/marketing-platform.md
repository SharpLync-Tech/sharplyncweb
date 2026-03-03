# SharpLync Marketing Platform Overview

Date: 2026-03-03

This document summarizes the marketing campaign system built for SharpLync/SharpFleet, including core features, routes, templates, and key files.

## What We Built
- Marketing admin area with role-gated access.
- Campaign creation, edit, preview, submit for review, approve, schedule, send, resend, and test-send.
- Quill editor for body content (drag/drop images + upload).
- Azure OpenAI integration to generate subject, preheader, and body.
- Brand-specific email templates for SharpLync (SL) and SharpFleet (SF).
- SharpPulse page (public) that publishes sent campaigns.
- Subscriber management (add/edit), preferences page, unsubscribe flow.
- Scheduling via `php artisan schedule:run` (Azure WebJob).
- Reply-to handling for SL/SF.

## Public Routes
Routes are in `routes/marketing.php`.
- Subscribe: `POST /marketing/subscribe`
- Confirm: `GET /marketing/confirm/{token}`
- Unsubscribe: `GET /marketing/unsubscribe/{token}`
- Manage preferences: `GET /marketing/preferences/{token}`
- Update preferences: `POST /marketing/preferences/{token}`
- SharpPulse feed: `GET /marketing/sharppulse`

## Admin Routes (Marketing)
Routes are in `routes/marketing.php` and protected by `marketing.access` middleware.
- Campaigns list: `GET /marketing/admin/campaigns`
- Create: `GET /marketing/admin/campaigns/create`
- Edit: `GET /marketing/admin/campaigns/{id}/edit`
- Preview: `GET /marketing/admin/campaigns/{id}/preview`
- Submit/approve/schedule/send/resend/test
- Logs: `GET /marketing/admin/logs`
- Subscribers: `GET /marketing/admin/subscribers`

## Core Controllers
- `app/Http/Controllers/Marketing/CampaignController.php`
  - Full campaign lifecycle + AI generation endpoint.
  - Test email is hard-coded to `jannie.brits@sharplync.com.au`.
  - Reply-to set per brand.
- `app/Http/Controllers/Marketing/SubscriptionController.php`
  - Subscribe/confirm/unsubscribe/preferences.
  - Sends admin notifications on subscribe/unsubscribe/changes.
- `app/Http/Controllers/Marketing/SharpPulseController.php`
  - Publishes sent campaigns to SharpPulse.
  - Cleans greetings/sign-off text for public display.
- `app/Http/Controllers/Marketing/SubscriberController.php`
  - Add/edit subscribers in admin.

## Email Templates
- Master layout: `resources/views/emails/marketing/layouts/master.blade.php`
- SharpLync template: `resources/views/emails/marketing/templates/sl-basic.blade.php`
- SharpFleet template: `resources/views/emails/marketing/templates/sf-basic.blade.php`

Key behavior:
- Brand-based logo and footer copy.
- CTA is injected before “Regards” if present.
- Footer includes preferences link, website, and legal line.
- Reply-to:
  - SL → `info@sharplync.com.au`
  - SF → `info@sharpfleet.com.au`

## SharpPulse Page
- View: `resources/views/marketing/sharppulse.blade.php`
- Displays most recent sent campaign first.
- Subject as heading, body_html as content.
- Strips greeting/sign-off (e.g., “Hi …”, “Dear …”, “Regards”).
- Includes social share links.

## Quill Editor + Uploads
- JS: `public/js/marketing/marketing-quill.js`
- Upload endpoint: `POST /marketing/admin/uploads`
- Files saved to `public/uploads/marketing`.

## AI Generation (Azure OpenAI)
- Service: `app/Services/Marketing/MarketingAiClient.php`
- Endpoint: `POST /marketing/admin/ai/generate`
- Generates subject, preheader, and HTML body.
- Supports tone + fluff options.

## Scheduling
- Schedule command: `php artisan schedule:run`
- App timezone: `Australia/Brisbane` (`config/app.php`)
- Scheduler processes:
  - `marketing:process-scheduled`

## Notifications
On confirmation/unsubscribe/preferences updates, admin notifications are sent:
- SL → `info@sharplync.com.au`
- SF → `info@sharpfleet.com.au`

## Formatting Helper
- `app/Services/Marketing/MarketingHtmlFormatter.php`
  - Normalizes HTML for email rendering and preview.
  - Removes empty paragraphs, sets consistent paragraph margins,
    and adjusts signature spacing.

## Key Views (Admin)
- Campaign list: `resources/views/marketing/admin/campaigns/index.blade.php`
- Create: `resources/views/marketing/admin/campaigns/create.blade.php`
- Edit: `resources/views/marketing/admin/campaigns/edit.blade.php`
- Logs: `resources/views/marketing/admin/logs/index.blade.php`
- Subscribers: `resources/views/marketing/admin/subscribers/index.blade.php`

## Database Changes (Manual)
These were added/updated manually via phpMyAdmin:
- `email_campaigns`: `preheader`, `cta_text`, `cta_url`, updated status values
- `email_subscribers`: `first_name`
- `marketing_users`: role + brand scope

## Navigation Update
SharpPulse link added in main site nav:
- `resources/views/layouts/base.blade.php`
  - Added “SharpPulse” between Home and Services (desktop + mobile).

