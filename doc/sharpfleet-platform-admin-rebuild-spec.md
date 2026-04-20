# SharpFleet Platform Admin Portal - Reconstruction Specification

## Purpose

This document captures the **complete current implementation** of the internal **SharpFleet Platform Admin** portal that exists inside the SharpLync web application.

It is written so the portal can be **recreated inside the SharpFleet website/app** without needing to reverse-engineer the current repo again.

This spec covers:

- every file in the current repo that materially participates in the portal
- route flow and access control
- controller actions and helper logic
- database connection requirements
- database tables and columns the portal expects
- UI screens, forms, actions, and view dependencies
- billing and Stripe integration points
- audit logging behavior
- implementation notes for rebuilding the portal inside SharpFleet

---

## What this portal is

The current portal is **not** the normal subscriber-facing SharpFleet admin.

It is an **internal platform management portal** used by SharpLync staff to manage SharpFleet subscribers across organisations.

Primary capabilities:

- list subscriber organisations
- search subscribers
- view organisation details
- inspect billing/subscription state
- edit subscriber metadata and billing overrides
- extend or clear organisation trial end
- inspect and edit subscriber users' trial overrides
- inspect subscriber vehicles
- inspect platform/subscriber audit logs
- initiate certain Stripe admin actions

In the current app, this portal lives under:

- `/admin/sharpfleet`

It uses the **SharpLync admin session**, not the normal SharpFleet subscriber login.

---

## High-level architecture

### Current hosting model

- Host application: **SharpLync** Laravel app
- Data source: **SharpFleet database** via Laravel connection name `sharpfleet`
- Auth source: **SharpLync Microsoft 365 admin login**
- UI layer: Blade views under `resources/views/admin/sharpfleet`
- Styling shell: `resources/views/admin/layouts/admin-layout.blade.php`

### Important distinction

There are two different SharpFleet admin concepts in this repo:

1. **Platform Admin** (the internal management portal described in this doc)
2. **Subscriber/Tenant Admin** (normal SharpFleet customer admin screens under `resources/views/sharpfleet/admin/...`)

This document is about **Platform Admin only**.

---

## Complete file inventory for the current portal

## Core routing and access files

### `config/database.php`
**Purpose:** Defines the Laravel DB connections used by the app, including the required `sharpfleet` connection.

**Portal relevance:** Every platform-admin data query depends on `DB::connection('sharpfleet')` or `Schema::connection('sharpfleet')` resolving correctly.

### `routes/admin.php`
**Purpose:** Declares the entire internal admin route tree.

**Portal relevance:** Contains the product portal route and all SharpFleet Platform Admin routes.

### `app/Http/Middleware/AdminAuth.php`
**Purpose:** Protects internal admin routes using the `admin_user` session.

**Portal relevance:** Required gate before any `/admin/sharpfleet*` page loads.

### `app/Http/Controllers/admin/Auth/MicrosoftController.php`
**Purpose:** Handles Microsoft 365 sign-in and writes `admin_user` into session.

**Portal relevance:** Current authentication source for the platform portal.

### `app/Http/Controllers/admin/PortalController.php`
**Purpose:** Renders the product selector page for internal admins.

**Portal relevance:** Provides the entry card that links into `admin.sharpfleet.platform`.

### `resources/views/admin/portal.blade.php`
**Purpose:** The internal admin landing page.

**Portal relevance:** Shows the “Open SharpFleet Platform Admin” button.

### `resources/views/admin/layouts/admin-layout.blade.php`
**Purpose:** Shared admin shell with top nav, sidebar, and route links.

**Portal relevance:** Supplies the persistent navigation entries for the SharpFleet platform admin.

---

## Core portal controller

### `app/Http/Controllers/admin/SharpFleet/PlatformController.php`
**Purpose:** Main controller for the internal SharpFleet platform admin.

**This is the single most important file for rebuilding the portal.**

It contains these actions:

- `index()`
- `organisation()`
- `editOrganisation()`
- `updateOrganisation()`
- `organisationUsers()`
- `editOrganisationUser()`
- `updateOrganisationUser()`
- `organisationVehicles()`
- `vehicle()`
- `auditLogs()`

Helper methods inside the same controller:

- `estimateMonthlyPrice()`
- `auditLogFilters()`
- `billingKeysForOrganisations()`
- `organisationTimezone()`
- `timezoneMapForOrganisations()`

---

## Supporting service files used directly by the portal

### `app/Services/SharpFleet/AuditLogService.php`
**Purpose:** Writes subscriber and platform-admin events to `sharpfleet_audit_logs`.

**Portal relevance:** Used by `PlatformController` for audit entries.

### `app/Services/SharpFleet/BillingDisplayService.php`
**Purpose:** Normalizes billing mode and billing state for an organisation.

**Portal relevance:** Used by the organisation details page.

### `app/Services/SharpFleet/StripeInvoiceService.php`
**Purpose:** Loads Stripe invoices for display.

**Portal relevance:** Used by the organisation details page.

### `app/Services/SharpFleet/StripeSubscriptionAdminService.php`
**Purpose:** Performs platform admin Stripe actions.

**Portal relevance:** Used by subscriber edit/update flow.

---

## View files used by the portal

### `resources/views/admin/sharpfleet/index.blade.php`
Subscriber list page.

### `resources/views/admin/sharpfleet/organisations/show.blade.php`
Organisation detail page with billing summary, Stripe invoices, and links to users/vehicles.

### `resources/views/admin/sharpfleet/organisations/edit.blade.php`
Organisation edit page with trial extension, billing override, and Stripe admin actions.

### `resources/views/admin/sharpfleet/organisations/users.blade.php`
Paginated organisation users list.

### `resources/views/admin/sharpfleet/users/edit.blade.php`
Edit form for user-level trial override.

### `resources/views/admin/sharpfleet/organisations/vehicles.blade.php`
Paginated organisation vehicles list.

### `resources/views/admin/sharpfleet/vehicles/show.blade.php`
Vehicle detail page that dynamically renders all schema columns if available.

### `resources/views/admin/sharpfleet/audit-logs/index.blade.php`
Audit log viewer with filters.

---

## Route map

All routes below are declared in `routes/admin.php` and protected by:

- middleware: `web`
- middleware: `admin.auth`
- prefix: `admin`

### Entry routes

| Method | URI | Route name | Controller/action | Notes |
|---|---|---|---|---|
| GET | `/admin/portal` | `admin.portal` | `PortalController@index` | Internal product selector page |
| GET | `/admin/sharpfleet/product` | `admin.sharpfleet.product` | `PortalController@sharpfleet` | Legacy tenant-admin SSO handoff |

### SharpFleet platform routes

| Method | URI | Route name | Controller/action | Purpose |
|---|---|---|---|---|
| GET | `/admin/sharpfleet` | `admin.sharpfleet.platform` | `PlatformController@index` | Subscriber list |
| GET | `/admin/sharpfleet/audit-logs` | `admin.sharpfleet.audit.index` | `PlatformController@auditLogs` | Audit log browser |
| GET | `/admin/sharpfleet/organisations/{organisationId}` | `admin.sharpfleet.organisations.show` | `PlatformController@organisation` | Organisation detail |
| GET | `/admin/sharpfleet/organisations/{organisationId}/edit` | `admin.sharpfleet.organisations.edit` | `PlatformController@editOrganisation` | Subscriber edit form |
| PATCH | `/admin/sharpfleet/organisations/{organisationId}` | `admin.sharpfleet.organisations.update` | `PlatformController@updateOrganisation` | Persist subscriber edit |
| GET | `/admin/sharpfleet/organisations/{organisationId}/users` | `admin.sharpfleet.organisations.users` | `PlatformController@organisationUsers` | Organisation users |
| GET | `/admin/sharpfleet/organisations/{organisationId}/users/{userId}/edit` | `admin.sharpfleet.organisations.users.edit` | `PlatformController@editOrganisationUser` | User trial edit form |
| PATCH | `/admin/sharpfleet/organisations/{organisationId}/users/{userId}` | `admin.sharpfleet.organisations.users.update` | `PlatformController@updateOrganisationUser` | Persist user edit |
| GET | `/admin/sharpfleet/organisations/{organisationId}/vehicles` | `admin.sharpfleet.organisations.vehicles` | `PlatformController@organisationVehicles` | Organisation vehicles |
| GET | `/admin/sharpfleet/vehicles/{vehicleId}` | `admin.sharpfleet.vehicles.show` | `PlatformController@vehicle` | Vehicle detail |

All entity IDs are constrained with `whereNumber(...)`.

---

## Authentication and access control

## Current access model

### Step 1: Microsoft login
Handled by `app/Http/Controllers/admin/Auth/MicrosoftController.php`.

Flow:

1. `redirectToMicrosoft()` creates OAuth state and redirects to Microsoft
2. `handleCallback()` exchanges auth code for token
3. calls `https://graph.microsoft.com/v1.0/me`
4. validates the user principal name ends with `@sharplync.com.au`
5. regenerates session
6. stores Graph response in session key `admin_user`
7. redirects to `/admin/portal`

### Step 2: Admin middleware
Handled by `app/Http/Middleware/AdminAuth.php`.

Logic:

- if no `admin_user` session exists: redirect to `/admin/login`
- if `admin_user.userPrincipalName` is not a SharpLync address: invalidate session and return HTTP 403
- otherwise continue

### Step 3: Product portal
Rendered by `PortalController@index` + `resources/views/admin/portal.blade.php`.

This page contains a card with:

- title: `SharpFleet Platform Admin`
- button label: `Open SharpFleet Platform Admin`
- destination: `route('admin.sharpfleet.platform')`

## Rebuild implication

If this portal is moved inside the SharpFleet website, the current SharpLync Microsoft-auth model will likely be replaced with one of these:

- SharpFleet super-admin account model
- SharpFleet role/permission middleware
- shared SSO if both apps remain integrated

For a clean rebuild inside SharpFleet, the recommended replacement is:

- a dedicated middleware such as `sharpfleet.platform_admin`
- backed by a SharpFleet-side super-admin flag or dedicated role table

---

## Database connection requirements

## Connection name

The entire portal depends on a Laravel connection named:

- `sharpfleet`

## Current connection source

In the current app, this connection must resolve from:

- `config/database.php`
- Azure App Settings / environment variables

Expected env vars:

- `SHARPFLEET_DB_HOST`
- `SHARPFLEET_DB_PORT`
- `SHARPFLEET_DB_DATABASE`
- `SHARPFLEET_DB_USERNAME`
- `SHARPFLEET_DB_PASSWORD`
- `SHARPFLEET_DB_CHARSET`
- `SHARPFLEET_DB_COLLATION`
- optional `SHARPFLEET_MYSQL_ATTR_SSL_CA`

## Rebuild implication

If rebuilt inside the SharpFleet app itself, you have two options:

1. keep a dedicated `sharpfleet` connection name for compatibility
2. switch the portal to the default DB connection if SharpFleet already uses its own DB as default

If the SharpFleet website already uses the same database as its primary app DB, option 2 is simpler. If you want low-risk compatibility with the current logic, option 1 is easier.

---

## Database tables and columns the portal depends on

The controller uses Laravel Query Builder directly, not Eloquent models, so the table/column contract matters.

## 1) `organisations`

Columns directly referenced by the portal:

- `id`
- `name`
- `industry`
- `company_type`
- `trial_ends_at`
- `settings`

Columns dynamically inspected for billing display:

- any column matching names or patterns such as:
  - `trial_ends_at`
  - `plan`
  - `plan_id`
  - `status`
  - `subscription_status`
  - `subscription_id`
  - `subscription_ends_at`
  - `billing_email`
  - `billing_status`
  - `stripe_customer_id`
  - `stripe_subscription_id`
  - `stripe_price_id`
  - `created_at`
  - `updated_at`

## 2) `users`

Columns directly referenced:

- `id`
- `organisation_id`
- `email`
- `first_name`
- `last_name`
- `role`
- `is_driver`
- `trial_ends_at`
- `created_at`

## 3) `vehicles`

Columns directly referenced:

- `id`
- `organisation_id`
- `name`
- `registration_number`
- `make`
- `model`
- `is_active`

The vehicle detail page may render **all columns** if schema inspection succeeds.

## 4) `company_settings`

Columns referenced:

- `organisation_id`
- `settings_json`

Expected JSON key:

- `timezone`

## 5) `sharpfleet_audit_logs`

Columns referenced:

- `id`
- `organisation_id`
- `actor_type`
- `actor_id`
- `actor_email`
- `actor_name`
- `action`
- `ip`
- `user_agent`
- `method`
- `path`
- `status_code`
- `context_json`
- `created_at`

If this table does not exist, the audit log page still renders and shows a warning.

---

## JSON contract inside `organisations.settings`

The portal relies heavily on the `settings` JSON column in `organisations`.

## Billing-related keys read by the portal

Top-level keys:

- `subscription_status`
- `subscription_started_at`
- `subscription_cancel_requested_at`
- `stripe_customer_id`
- `stripe_subscription_id`
- `stripe_price_id`

Nested override object:

- `billing_override.mode`
- `billing_override.access_until_utc`
- `billing_override.vehicle_cap_override`
- `billing_override.price_override_monthly`
- `billing_override.invoice_reference`
- `billing_override.notes`
- `billing_override.updated_at_utc`
- `billing_override.stripe_cancel`
- `billing_override.stripe_uncancel`

## Allowed `billing_override.mode` values

- `manual_invoice`
- `comped`
- empty / absent

The edit UI also allows `stripe` as a form value, but the override logic only meaningfully stores manual/comped override data. The normal/default Stripe state is inferred from settings and Stripe-related keys.

---

## Screen-by-screen logic

## 1) Subscriber index page

### File(s)

- Route: `admin.sharpfleet.platform`
- Controller: `PlatformController@index`
- View: `resources/views/admin/sharpfleet/index.blade.php`

### Behavior

- reads query param `q`
- attempts platform admin audit log for action `sharpfleet.platform.index`
- queries `organisations`
- adds subquery counts for:
  - users per organisation
  - vehicles per organisation
- if `q` is present, filters by:
  - `organisations.name LIKE %q%`
  - `organisations.industry LIKE %q%`
- sorts by `organisations.id DESC`
- paginates 25 rows
- loads organisation timezone map from `company_settings`

### UI output

Table columns:

- Organisation
- Industry
- Trial ends
- Users count
- Vehicles count
- Actions

Actions:

- `Manage` -> organisation detail
- `Edit` -> organisation edit

### Rebuild notes

This page is the subscriber directory / landing dashboard. If rebuilding, preserve:

- search by name and industry
- count subqueries
- pagination
- route names or equivalent path aliases

---

## 2) Organisation detail page

### File(s)

- Route: `admin.sharpfleet.organisations.show`
- Controller: `PlatformController@organisation`
- View: `resources/views/admin/sharpfleet/organisations/show.blade.php`
- Service(s):
  - `BillingDisplayService`
  - `StripeInvoiceService`
  - `AuditLogService`

### Data loaded

From `organisations`:

- the organisation row itself

Counts:

- total users
- total vehicles
- active vehicles (`is_active = 1`)

Timezone:

- from `company_settings.settings_json.timezone`

Decoded from `organisations.settings`:

- subscription status
- Stripe customer id
- Stripe subscription id
- Stripe price id
- subscription start/cancel timestamps

Billing helpers:

- monthly estimate via `estimateMonthlyPrice()`
- effective billing summary via `BillingDisplayService`
- recent billing audit logs from `sharpfleet_audit_logs`
- Stripe invoices if `stripe_customer_id` exists

### Billing estimate logic

Defined in `PlatformController::estimateMonthlyPrice()`.

Pricing tiers:

- first 10 vehicles = `$3.50` each
- vehicles above 10 = `$2.50` each
- if vehicles > 20, `requiresContact = true`

It returns:

- `monthlyPrice`
- `breakdown`
- `requiresContact`

### Effective billing mode logic

Defined in `BillingDisplayService::getOrganisationBillingSummary()`.

Precedence:

1. active manual/complimentary override
2. active Stripe subscription (`subscription_status === 'active'`)
3. otherwise trial

Returned `effective_mode` values:

- `manual_invoice`
- `complimentary`
- `stripe`
- `trial`

### UI sections

1. Organisation summary
   - org ID
   - industry
   - company type
   - trial end
   - timezone

2. Subscription & Billing
   - active vehicles
   - estimated monthly cost
   - billing identifiers from settings JSON
   - recent billing audit activity
   - Stripe invoices

3. Subscriber Data shortcuts
   - users count + link
   - vehicles count + link
   - actions placeholder card

### Session flash banners supported

The page expects these session keys after successful update actions:

- `stripe_checkout_url`
- `stripe_uncancel_result`
- `stripe_cancel_result`

---

## 3) Organisation edit page

### File(s)

- Route: `admin.sharpfleet.organisations.edit`
- POST target: `admin.sharpfleet.organisations.update`
- Controller methods:
  - `editOrganisation()`
  - `updateOrganisation()`
- View: `resources/views/admin/sharpfleet/organisations/edit.blade.php`

### What the form edits

Basic subscriber fields:

- `name`
- `industry`
- `company_type`

Trial override / extension:

- `trial_ends_at`
- `extend_trial_days`

Billing override:

- `billing_mode`
- `billing_access_until`
- `billing_vehicle_cap_override`
- `billing_price_override_monthly`
- `billing_invoice_reference`
- `billing_notes`

Stripe admin action:

- `stripe_admin_action`

### Validation rules in `updateOrganisation()`

- `name` => required, string, max 150
- `industry` => nullable, string, max 150
- `company_type` => nullable, string, max 50
- `trial_ends_at` => nullable, string, max 30
- `extend_trial_days` => nullable, integer, min 1, max 3650
- `billing_mode` => nullable, string, in `stripe,manual_invoice,comped`
- `billing_access_until` => nullable, string, max 30
- `billing_vehicle_cap_override` => nullable, integer, min 1, max 100000
- `billing_price_override_monthly` => nullable, numeric, min 0, max 100000
- `billing_invoice_reference` => nullable, string, max 100
- `billing_notes` => nullable, string, max 1000
- `stripe_admin_action` => nullable, string, in `uncancel,create_checkout`

### Timezone handling

Admin UI inputs are treated as:

- Brisbane time (`Australia/Brisbane`)

Saved values are converted to:

- UTC timestamps in database / settings JSON

### Trial update logic

Rules:

- if `extend_trial_days > 0`: extend from existing future trial end if available, otherwise now
- else if `trial_ends_at` present and blank: clear the org-level trial end
- else parse `trial_ends_at` from Brisbane local time and store as UTC

### Billing mode rules

Special validation:

- if `billing_mode` is `manual_invoice` or `comped`, then `billing_access_until` is required

### Stripe action rules

Special validation:

- `stripe_admin_action` may only be used when billing mode is blank/default or `stripe`

Behavior:

#### If switching to `manual_invoice` or `comped`

- if an existing Stripe subscription ID exists:
  - call `StripeSubscriptionAdminService::cancelSubscription(..., true)`
  - this schedules cancellation at period end
  - update settings locally to `subscription_status = cancelled`
  - set `subscription_cancel_requested_at`

#### If `stripe_admin_action = uncancel`

- require existing stored Stripe subscription id
- call `uncancelSubscription()`
- set local settings `subscription_status = active`
- clear `subscription_cancel_requested_at`

#### If `stripe_admin_action = create_checkout`

- block if settings already say subscription is active
- require at least 1 active vehicle
- call `createCheckoutUrl(organisationId, activeVehiclesCount, baseUrl)`
- return generated Stripe Checkout URL through session flash

### Settings JSON mutation logic

The method updates `organisations.settings` by:

1. reading current JSON
2. preserving prior values
3. constructing a `billing_override` payload
4. deleting the override if effectively empty
5. writing updated JSON back only if changed

### Audit logging

Action written:

- `sharpfleet.organisation.update`

Context includes:

- updated org fields
- trial before/after
- billing override before/after
- stripe admin action details

### Redirect behavior

On success:

- redirect to organisation detail page
- flash `success = Subscriber updated.`
- optionally flash Stripe action result data

---

## 4) Organisation users page

### File(s)

- Route: `admin.sharpfleet.organisations.users`
- Controller: `PlatformController@organisationUsers`
- View: `resources/views/admin/sharpfleet/organisations/users.blade.php`

### Behavior

- logs action `sharpfleet.organisation.users.view`
- loads organisation
- loads paginated users for org
- sort order:
  - role
  - email
- pagination size: 50

### UI columns

- Name
- Email
- Role
- Driver (Yes/No)
- Trial ends
- Action -> Edit

---

## 5) User edit page

### File(s)

- Route: `admin.sharpfleet.organisations.users.edit`
- PATCH target: `admin.sharpfleet.organisations.users.update`
- Controller methods:
  - `editOrganisationUser()`
  - `updateOrganisationUser()`
- View: `resources/views/admin/sharpfleet/users/edit.blade.php`

### Behavior

This page only edits **user-level trial override**.

It does not edit:

- name
- email
- role
- permissions

Those are shown read-only in the UI.

### Validation rules

- `trial_ends_at` => nullable, string, max 30
- `extend_trial_days` => nullable, integer, min 1, max 3650

### Trial logic

Same pattern as organisation-level trial logic:

- extend from future user trial end if present, else now
- blank input clears user-level override
- datetime-local input is Brisbane time and stored as UTC

### Audit logging

Action written:

- `sharpfleet.organisation.user.update`

Context includes old and new trial values.

---

## 6) Organisation vehicles page

### File(s)

- Route: `admin.sharpfleet.organisations.vehicles`
- Controller: `PlatformController@organisationVehicles`
- View: `resources/views/admin/sharpfleet/organisations/vehicles.blade.php`

### Behavior

- logs action `sharpfleet.organisation.vehicles.view`
- loads organisation
- loads paginated vehicles for org
- sort order:
  - active first (`is_active` desc)
  - then `name`
- pagination size: 50

### UI columns

- Name
- Registration
- Make / Model
- Active badge
- Action -> Details

---

## 7) Vehicle detail page

### File(s)

- Route: `admin.sharpfleet.vehicles.show`
- Controller: `PlatformController@vehicle`
- View: `resources/views/admin/sharpfleet/vehicles/show.blade.php`

### Behavior

- load vehicle by ID
- if vehicle has `organisation_id`, load owning organisation
- log `sharpfleet.vehicle.view`
- inspect schema using `Schema::connection('sharpfleet')->getColumnListing('vehicles')`

### Display logic

If schema listing fails:

- show fallback fields only:
  - id
  - organisation_id
  - name
  - registration_number

If schema listing succeeds:

- render a row for every column in `vehicles`
- convert `_at` or `date`-like string values from UTC to Brisbane time when possible
- otherwise show raw scalar or JSON-encoded value

### Rebuild notes

This page is intentionally generic. It acts as a schema-driven inspection tool rather than a curated vehicle profile.

---

## 8) Audit log page

### File(s)

- Route: `admin.sharpfleet.audit.index`
- Controller: `PlatformController@auditLogs`
- View: `resources/views/admin/sharpfleet/audit-logs/index.blade.php`
- Service: `AuditLogService`

### Behavior

- attempts to log `sharpfleet.audit_logs.index`
- checks whether `sharpfleet_audit_logs` table exists
- if missing, page still renders with warning and empty result set
- if table exists, builds filtered query

### Supported filters

Query-string inputs:

- `organisation_id`
- `actor_type`
- `actor_email`
- `action`
- `date_from`
- `date_to`
- `q`

### Search behavior

If `q` present, it matches any of:

- `action`
- `actor_email`
- `actor_name`
- `path`
- `context_json`

### UI columns

- When (Brisbane)
- Org
- Actor
- Action
- Request
- Status
- Context (expandable details block)

### Human-readable action labels

The view maps some raw actions to friendlier labels, including:

- `sharpfleet.organisation.update` -> Platform Admin Updated Subscriber
- `sharpfleet.organisation.edit.view` -> Platform Admin Viewed Subscriber Edit
- `sharpfleet.organisation.view` -> Platform Admin Viewed Subscriber
- `sharpfleet.organisation.user.update` -> Platform Admin Updated User Trial
- `sharpfleet.organisation.user.edit.view` -> Platform Admin Viewed User Edit
- several `Billing:*` actions

---

## Helper/service logic in detail

## `AuditLogService`

### Main responsibility

Write audit entries into the SharpFleet DB.

### Important methods

- `logSubscriber()`
- `logPlatformAdmin()`
- `logSystem()`
- `logSubscriberRequest()`

### Important implementation detail

`write()` refuses to insert logs when:

- `organisation_id <= 0`

This means platform-admin events with no organisation ID are skipped.

### Consequence

These calls from `PlatformController` do **not reliably persist** because they pass null organisation:

- `sharpfleet.platform.index`
- `sharpfleet.audit_logs.index`

That behavior should be consciously decided during rebuild.

**Recommendation for rebuild:**

Either:

1. allow platform-level audit events with nullable organisation IDs
2. introduce a special organisation ID like `NULL` rather than rejecting them
3. split platform audit logs into a separate table

## `BillingDisplayService`

### Responsibility

Normalize the organisation billing state for UI consumption.

### Important behavior

- reads `organisations.settings`
- inspects `billing_override`
- computes whether override is still active based on current UTC time
- falls back to Stripe active state, else trial

### Important quirk

This service reads timezone from:

- `organisations.timezone`

But `PlatformController` itself reads timezone from:

- `company_settings.settings_json.timezone`

These should ideally be unified during rebuild.

## `StripeInvoiceService`

### Responsibility

Load simplified invoice data from Stripe.

### Env dependency

Uses:

- `STRIPE_SECRET_TEST`

### Output fields returned

- `id`
- `number`
- `status`
- `currency`
- `total`
- `amount_due`
- `amount_paid`
- `created`
- `period_start`
- `period_end`
- `hosted_invoice_url`
- `invoice_pdf`

## `StripeSubscriptionAdminService`

### Responsibility

Perform platform-initiated subscription actions in Stripe.

### Methods

- `cancelSubscription()`
- `uncancelSubscription()`
- `createCheckoutUrl()`

### Env dependencies

Uses:

- `STRIPE_SECRET_TEST`
- `STRIPE_PRICE_TEST`

### Important implementation notes

- cancellation defaults to `cancel_at_period_end = true`
- uncancel explicitly clears `cancel_at`
- checkout success/cancel URLs are hardcoded to SharpFleet tenant admin account page:
  - `/app/sharpfleet/admin/account?checkout=success`
  - `/app/sharpfleet/admin/account?checkout=cancelled`

### Rebuild implication

If this portal moves inside SharpFleet, checkout success/cancel return URLs may become simpler because the flow will already be inside the SharpFleet app.

---

## Shared UI shell behavior

## `resources/views/admin/layouts/admin-layout.blade.php`

This layout supplies:

- navbar
- user display/avatar from `session('admin_user')`
- logout link
- sidebar navigation
- mobile offcanvas navigation

SharpFleet-related nav entries:

- `SharpFleet Platform Admin`
- `Subscribers`
- `Audit Logs`

If rebuilding inside SharpFleet, you probably do **not** want to copy this layout 1:1. Instead, port only:

- page structure
- route links
- action grouping

and replace the surrounding shell with SharpFleet’s own admin layout.

---

## Current UX / content model by screen

## Subscriber list UX

- searchable
- compact count-based summary
- primary user journey starts here

## Organisation detail UX

- summary + billing in two-column layout
- links deeper into users and vehicles
- exposes Stripe and billing state to support operations

## Edit subscriber UX

- intentionally support-focused
- combines metadata, trial support, billing overrides, and Stripe control in one form

## Users UX

- read-only list plus edit of trial override only

## Vehicles UX

- read-only list plus generic detail inspector

## Audit UX

- support operator search/filter tool
- designed for investigation rather than dashboards

---

## Rebuild plan inside SharpFleet website

The current portal can be rebuilt inside SharpFleet in a cleaner form by separating **platform-admin concerns** from **subscriber concerns**.

## Recommended target architecture

### 1. Put the portal in its own route group

Suggested example:

- `/platform`
- `/platform/subscribers`
- `/platform/subscribers/{id}`

Or if you want to stay close to current URLs:

- `/admin/platform`
- `/admin/platform/subscribers`

### 2. Replace SharpLync admin auth

Instead of Microsoft-only SharpLync session, use one of:

- `role = platform_admin`
- dedicated `platform_admins` table
- permission middleware such as `can:manage-subscribers`

### 3. Keep the same controller breakdown initially

For fastest rebuild, preserve the current action split:

- index
- show organisation
- edit organisation
- update organisation
- list users
- edit user trial
- list vehicles
- show vehicle
- audit logs

### 4. Keep database contracts initially

To minimize risk, keep these contracts as-is first:

- `organisations.settings` JSON shape
- `company_settings.settings_json.timezone`
- `sharpfleet_audit_logs`

You can normalize later after the rebuild is stable.

### 5. Extract billing logic into a dedicated domain service

The current code spreads billing state across:

- `organisations.settings`
- `trial_ends_at`
- vehicle counts
- audit log inspection
- Stripe runtime API calls

For the SharpFleet rebuild, it would be cleaner to formalize this into:

- `PlatformSubscriberBillingService`
- `PlatformSubscriberAccessService`

But only after preserving behavior parity.

---

## Suggested rebuild file structure inside SharpFleet

A practical parity-first version could look like:

```text
app/
  Http/
    Controllers/
      Platform/
        SubscriberController.php
        SubscriberUserController.php
        SubscriberVehicleController.php
        AuditLogController.php
    Middleware/
      EnsurePlatformAdmin.php
  Services/
    Platform/
      SubscriberBillingService.php
      StripeInvoiceService.php
      StripeSubscriptionAdminService.php
      AuditLogService.php
resources/
  views/
    platform/
      subscribers/
        index.blade.php
        show.blade.php
        edit.blade.php
        users.blade.php
        user-edit.blade.php
        vehicles.blade.php
        vehicle-show.blade.php
      audit-logs/
        index.blade.php
routes/
  platform.php
```

You could also keep a single controller initially if speed matters more than long-term neatness.

---

## Exact behavior to preserve during rebuild

If you want the rebuilt portal to feel identical, preserve these behaviors exactly:

1. Subscriber search by organisation name/industry
2. User and vehicle counts on the index page
3. Trial dates displayed in Brisbane time
4. User-level trial override separate from organisation-level trial override
5. Billing override precedence over Stripe/trial
6. Stripe cancellation at period end, not immediate by default
7. Stripe checkout quantity derived from active vehicle count
8. Generic vehicle inspection page driven by schema columns
9. Audit log filtering by org/actor/action/date/search
10. Graceful audit page behavior when `sharpfleet_audit_logs` table is absent

---

## Known quirks / caveats in the current implementation

These are important if recreating the portal faithfully.

### 1. Some platform-level audit events are skipped
Because `AuditLogService::write()` requires `organisation_id > 0`, global platform actions without organisation context do not persist.

### 2. Billing timezone source is inconsistent
- `BillingDisplayService` reads `organisations.timezone`
- `PlatformController` reads `company_settings.settings_json.timezone`

### 3. Stripe env names are test-specific
The code uses:

- `STRIPE_SECRET_TEST`
- `STRIPE_PRICE_TEST`

If you move this into production-grade SharpFleet code, consider switching to environment-specific or neutral names.

### 4. The detail page suggests actions are read-only, but editing exists
The organisation detail page has an `Actions` card that says “Read-only (MVP)” even though the portal supports editing subscriber and user trial data.

### 5. Billing identifiers come from JSON, not normalized columns
A lot of billing state is read from `organisations.settings`, not relational tables. That makes parity easy, but long-term reporting harder.

---

## Minimum viable recreation checklist

Use this as the actual rebuild checklist.

### Access/auth
- [ ] add platform-admin-only middleware in SharpFleet
- [ ] create platform route group
- [ ] add nav entry to SharpFleet admin shell

### Data access
- [ ] ensure SharpFleet app can read `organisations`
- [ ] ensure `users`, `vehicles`, `company_settings`, `sharpfleet_audit_logs` are accessible
- [ ] preserve settings JSON contract

### Screens
- [ ] subscriber list
- [ ] subscriber detail
- [ ] subscriber edit
- [ ] users list
- [ ] user trial edit
- [ ] vehicles list
- [ ] vehicle detail
- [ ] audit log browser

### Billing/Stripe
- [ ] replicate monthly estimate tiers
- [ ] replicate effective billing mode precedence
- [ ] load Stripe invoices
- [ ] support cancel-at-period-end
- [ ] support uncancel
- [ ] support checkout link creation

### Audit logging
- [ ] write platform admin actions
- [ ] define whether org-less logs should be stored
- [ ] preserve search/filter UX

---

## Short conclusion

The current SharpFleet Platform Admin is a **support/operator portal embedded in SharpLync**, backed directly by the SharpFleet DB and protected by SharpLync’s Microsoft-based admin session.

The true core of the portal is:

- `routes/admin.php`
- `app/Http/Controllers/admin/SharpFleet/PlatformController.php`
- `app/Services/SharpFleet/AuditLogService.php`
- `app/Services/SharpFleet/BillingDisplayService.php`
- `app/Services/SharpFleet/StripeInvoiceService.php`
- `app/Services/SharpFleet/StripeSubscriptionAdminService.php`
- `resources/views/admin/sharpfleet/*`

If you recreate those behaviors inside SharpFleet and replace only the outer authentication/layout shell, you can reproduce the current portal with high fidelity.
