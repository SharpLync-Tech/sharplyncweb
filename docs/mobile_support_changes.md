# Mobile Support Page + Offline PWA Improvements

This document summarizes the changes made to add a mobile Support page, offline-safe trip handling, and UI polish in SharpFleet.

## New Support Page

### What it does
- Adds a mobile Support page reachable from the More screen.
- Accepts a 500-character support request from the driver.
- Captures driver name and email (read-only), platform (Apple/Android/Other), usage mode (Browser/PWA), and recent device logs.
- Captures client timezone and company timezone for response context.
- Sends a support email to `info@sharplync.com.au`.
- Shows a success banner that auto-fades after 4 seconds and redirects to Home.
- When offline, queues the request locally, shows a queued banner, then redirects Home.
- When back online, queued requests are sent in the background and Home shows “Support request sent”.

### Files
- `resources/views/sharpfleet/mobile/support.blade.php`
  - Support form with character counter.
  - Hidden fields for platform/usage mode/logs.
  - Success/error handling and auto-fade.
  - JS to detect platform and PWA vs browser.
  - Serializes logs from in-memory/localStorage buffer for email payload.
  - Offline queue handling + redirect to Home after queueing.

- `app/Http/Controllers/SharpFleet/DriverMobileController.php`
  - `support()` action renders the Support page.
  - `supportSend()` action validates input and sends the email via `Mail::raw`.
  - Uses reply-to with the driver email when available.
  - Includes organisation name, company admin contact (name/email), and company timezone in the email.

- `routes/sharpfleet.php`
  - Adds GET `/app/sharpfleet/mobile/support`
  - Adds POST `/app/sharpfleet/mobile/support`

- `resources/views/sharpfleet/mobile/more.blade.php`
  - Adds a Support link to the More screen.

## Client-Side Logs (Warnings/Errors Only)

### What it does
- Captures only warnings/errors in memory + localStorage.
- Stores only the last 3 days or last 100 entries (whichever is less).
- Logs are structured objects (timestamp, level, message, context).
- No DB storage.
- Log payload is appended to support emails if available.

### Files
- `resources/views/sharpfleet/mobile/layouts/app.blade.php`
  - Adds a small logging module:
    - `window.sfLog(level, message, context)`
    - `window.sfGetLogs()`
  - Hooks `window.error` and `unhandledrejection`.
  - Prunes logs by age and entry count.
  - Adds a support queue sender:
    - `window.sfQueueSupportRequest(payload)`
    - `window.sfSyncSupportQueue()`
  - Sets a localStorage flag when queued requests are sent (used to show Home success).

## Offline Trip Flow Improvements

### What it does
- Ensures starting/ending trips works even on flaky connections.
- When online, the form submits via fetch with a timeout; on network errors it falls back to offline queue.
- Offline trips always show the offline “Trip in Progress” card, and hide “No Active Trip”.
- Offline sync message clears once syncing is done or there is nothing to sync.

### Files
- `resources/views/sharpfleet/mobile/dashboard.blade.php`
  - Adds `submitFormOnline()` helper for fetch-based POST with timeout.
  - Start Trip: blocks duplicate offline trips, online submit when possible, fallback to offline on network errors.
  - End Trip: if offline trip exists, always ends offline even if `navigator.onLine` is true.
  - Hides “No Active Trip” while an offline trip exists.
  - Adds auto-hide for offline sync banner on success/empty sync.

## Status Icon “Tick” Reliability + Pulse

### What it does
- Fixes missing tick updates when icons fallback in offline mode.
- Adds a subtle “pop” animation on completion.

### Files
- `resources/views/sharpfleet/mobile/layouts/app.blade.php`
  - Keeps attributes (including `id`) when swapping Ionicons for fallback SVGs.
- `resources/views/sharpfleet/mobile/sheets/start-trip.blade.php`
  - Adds a pulse class toggle when a section is saved.
- `public/css/sharpfleet/sharpfleet-mobile.css`
  - Adds `.sf-status-pulse` using existing `@keyframes sf-pop`.

## Notes
- Support request email destination: `info@sharplync.com.au`.
- Log payload is optional and capped (warnings/errors only, 3 days/100 entries).
- No database changes were made.
