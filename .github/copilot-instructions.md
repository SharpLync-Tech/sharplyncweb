# GitHub Copilot instructions (SharpLync)

These instructions apply to work performed in this repository.

## Safety and environment
- Do **not** run environment-specific/local-only commands (e.g., `php -v`, `composer -V`, `node -v`) unless the user explicitly asks.
- Prefer repo-safe checks (static inspection, `git diff`, searching files) and only run commands the user requested.
- If a command could modify data (DB writes, migrations, destructive actions), ask first.

## Project conventions
- Stack: Laravel / PHP / Blade.
- Keep changes minimal and focused on the requested feature.
- Prefer server-side enforcement for permission/feature toggles (don’t rely only on UI).

## UX and wording
- Follow existing UI patterns and language.
- Don’t add extra pages/features beyond the request.

## Code quality
- Avoid broad refactors.
- Keep naming consistent with existing domain terms (SharpFleet, vehicles, trips).
- Ensure backwards compatibility when changing enums/values (e.g., legacy `trip_mode` values).
