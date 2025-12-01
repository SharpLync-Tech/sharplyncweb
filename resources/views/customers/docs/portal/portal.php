SharpLync Customer Portal — Developer README

Version: 1.0
Updated: December 2025
Maintainer: Max (ChatGPT)
Project Owner: Jannie Brits (SharpLync Pty Ltd)

📌 Overview

The SharpLync Customer Portal is a modular Laravel Blade system designed for:

Customer profile display

Security management (2FA, password, SSPIN)

Support access

Account summary

Modern, clean, modular UI

Fully separated partials to avoid bloated Blade files

Dedicated modal and JS controllers

This README outlines file structure, responsibilities, update procedures, and integration rules.

🧱 1. Folder Structure
resources/
└── views/
    └── customers/
        ├── layouts/
        │   └── customer-layout.blade.php
        │
        ├── portal.blade.php              <-- MAIN ROUTE VIEW (loads all partials)
        │
        ├── portal/
        │   ├── profile-card.blade.php    <-- Left column - profile & SSPIN preview
        │   ├── security-card.blade.php   <-- 2FA + password settings buttons
        │   ├── support-card.blade.php    <-- Support shortcuts
        │   ├── account-card.blade.php    <-- Account summary
        │
        │   └── modals/
        │       ├── security-modal.blade.php       <-- Full 2FA modal
        │       └── password-sspin-modal.blade.php <-- Password + SSPIN modal

public/
└── js/
    ├── security.js          <-- Existing 2FA logic
    └── portal-ui.js         <-- Handles both modals + SSPIN preview

public/
└── css/
    ├── customer.css         <-- Main portal styling
    └── password-sspin.css   <-- Styling isolated for new modal

🎯 2. Main Portal Loader: portal.blade.php

This file should NEVER contain large UI blocks anymore.
It only includes:

Profile card

Security card

Support card

Account card

Both modals

This ensures the file stays <200 lines forever.

If anything visual needs updating, it goes into the partial, not portal.blade.php.

🪪 3. Profile Card

File:
resources/views/customers/portal/profile-card.blade.php

Responsibilities:

Avatar

Customer name

Email

Customer since

SSPIN preview section

“Manage” button → opens SSPIN modal

“Edit Profile” button

Update Rules:

Only SSPIN preview section should ever be modified for SSPIN-related changes.

Do NOT place modal code here.

🔐 4. Security Card

File:
resources/views/customers/portal/security-card.blade.php

Responsibilities:

2FA Settings button

Password & SSPIN Settings button

Update Rules:

These two buttons must always exist.

If new security features are added (SMS verification later), this card is where a new button will go.

🛠 5. Modals
A) security-modal.blade.php

Handles:

Email-based 2FA setup

Authenticator app setup

Disable 2FA

OTP inputs

QR code flow

This file should NEVER be modified unless upgrading 2FA logic.

B) password-sspin-modal.blade.php

Handles:

Password change

SSPIN show/generate/save

SSPIN section is hidden by default

Updated modern SharpLync input design

Styled with password-sspin.css

Update Rules:

Password area updates → this file

SSPIN logic/flow → this file

Styling → password-sspin.css

JS → portal-ui.js

🧩 6. JavaScript Controller

File:
public/js/portal-ui.js

This JS controls:

✔ Security modal open/close

✔ Password & SSPIN modal open/close
✔ Dashboard “Manage” button (opens SSPIN modal)
✔ SSPIN preview synchronization (if needed in future)

Update Rules:

Open/close logic for modals → this file

SSPIN show/hide or generation logic → also this file

2FA logic → belongs in security.js

🎨 7. Styling
customer.css

Large main CSS containing:

Layout

Cards

Buttons

Typography

Portal grid

password-sspin.css

Isolated stylesheet containing:

Modal close button

Modal input styles

SSPIN + password card

Responsive tweaks

Ensures no cross-contamination with customer.css

Update Rules:

Do NOT put password or SSPIN styles into customer.css anymore.

password-sspin.css is allowed to override customer.css only inside #cp-password-modal.

🔌 8. Adding New Features in the Future
To add a new dashboard card:

Create a new Blade partial:

resources/views/customers/portal/new-feature-card.blade.php


Then include it in:

portal.blade.php

To add a new modal:

Create a file in:

resources/views/customers/portal/modals/


Add logic to portal-ui.js.

To add new backend actions:

Add controller functions, then call them from AJAX (future enhancement).

🧨 9. Common Update Points (Very Important)

If you ever need to update:

✔ SSPIN preview

→ update profile-card.blade.php

✔ SSPIN modal

→ update password-sspin-modal.blade.php

✔ SSPIN JavaScript

→ update portal-ui.js

✔ Password input design

→ update password-sspin.css

✔ 2FA logic

→ update security.js
→ update security-modal.blade.php (if UI change)

✔ Layout / spacing / alignment

→ update customer.css

✔ Panel or card content

→ update the specific partial inside /portal/

🔍 10. Versioning Rules

Whenever you update a file:

Add version comment at the top:

{{-- Version: 1.1 (Updated SSPIN preview style) --}}


Always update this README if structural changes happen.

Keep a clean changelog inside /docs/changelog.md if needed.

🏁 11. Goal of This Architecture

Avoid giant 600-line portal files

Easy maintenance

Perfect organization

Safe future updates

Cleaner code

Zero risk of breaking 2FA

Easy collaboration