# GitHub Copilot instructions (SharpLync)

These instructions apply to work performed in this repository.

## Safety and environment
- Do **not** run environment-specific or local-only commands
  (e.g. `php -v`, `composer install`, `artisan`, `node`, `npm`, `yarn`)
  unless the user explicitly asks.
- Assume the user does **NOT** have a runnable local PHP/Laravel environment.
  Work is limited to static code changes only.
- Do **not** attempt to execute, test, or validate code at runtime.
- Prefer static inspection, file diffs, and reasoning over execution.
- If a command could modify data (DB writes, migrations, destructive actions), ask first.

## Database workflow (VERY IMPORTANT)
- Assume database work is performed via **phpMyAdmin**, not via migrations or CLI tools.
- Do **not** create or modify Laravel migrations unless explicitly requested.
- When database changes are required:
  - Provide **raw SQL** suitable for phpMyAdmin
  - Clearly label SQL as READ-ONLY or DESTRUCTIVE where applicable
- Never assume direct DB access from code execution.

## Project conventions
- Stack: Laravel / PHP / Blade.
- Keep changes minimal and focused on the requested feature.
- Prefer server-side enforcement for permission/feature toggles
  (do not rely only on UI checks).
- Do not introduce new architectural patterns unless explicitly asked.

## UX and wording
- Follow existing UI patterns and wording.
- Do not add new pages, flows, or features beyond the request.
- Avoid “helpful” UX changes that alter behaviour.

## Code quality
- Avoid broad refactors.
- Keep naming consistent with existing domain terms
  (SharpFleet, vehicles, trips, bookings).
- Ensure backwards compatibility when changing enums or values
  (e.g. legacy `trip_mode` values).
