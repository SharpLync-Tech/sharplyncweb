# SharpFleet Email Inventory

Last updated: 2026-01-17

This document lists SharpFleet-specific emails, the templates they use, where they are sent from (controller/service/command), and the UI pages that trigger those sends.

## Email templates used by SharpFleet

1) Account activation
- Mailable: App\Mail\SharpFleet\AccountActivation
- Template: resources/views/emails/sharpfleet/account-activation.blade.php
- Sender: app/Http/Controllers/SharpFleet/Admin/RegisterController.php (register)
- Trigger: Admin registration form submit

2) Welcome email
- Mailable: App\Mail\SharpFleet\WelcomeEmail
- Template: resources/views/emails/sharpfleet/welcome.blade.php
- Sender: app/Http/Controllers/SharpFleet/Admin/RegisterController.php (completeRegistration)
- Trigger: Account activation completion

3) New subscriber notification (internal)
- Mailable: App\Mail\SharpFleet\NewSubscriberNotification
- Template: resources/views/emails/sharpfleet/new-subscriber.blade.php
- Sender: app/Http/Controllers/SharpFleet/Admin/RegisterController.php (completeRegistration)
- Trigger: Account activation completion (internal notice to info@sharplync.com.au)

4) Password reset
- Mailable: App\Mail\SharpFleet\PasswordReset
- Template: resources/views/emails/sharpfleet/password-reset.blade.php
- Sender: app/Http/Controllers/SharpFleet/Auth/ForgotPasswordController.php (sendResetLinkEmail)
- Trigger: Forgot password form submit

5) Driver invitation
- Mailable: App\Mail\SharpFleet\DriverInvitation
- Template: resources/views/emails/sharpfleet/driver-invitation.blade.php
- Sender: app/Http/Controllers/SharpFleet/Admin/DriverInviteController.php (invite/resend/send-invites)
- Trigger: Invite driver actions in admin users pages

6) Booking changed (created/updated/cancelled)
- Mailable: App\Mail\SharpFleet\BookingChanged
- Template: resources/views/emails/sharpfleet/booking-changed.blade.php
- Sender: app/Services/SharpFleet/BookingService.php (emailBookingChanged)
- Trigger: Booking create/update/cancel in admin/driver/mobile bookings UI

7) Booking reminder (1 hour before start)
- Mailable: App\Mail\SharpFleet\BookingReminder
- Template: resources/views/emails/sharpfleet/booking-reminder.blade.php
- Sender: app/Console/Commands/SharpFleetSendBookingReminders.php
- Trigger: Scheduled command sharpfleet:send-booking-reminders

8) Rego reminder digest
- Mailable: App\Mail\SharpFleet\RegoReminderDigest
- Template: resources/views/emails/sharpfleet/rego-reminder.blade.php
- Sender: app/Console/Commands/SharpFleetSendReminders.php
- Trigger: Scheduled command sharpfleet:send-reminders

9) Service reminder digest
- Mailable: App\Mail\SharpFleet\ServiceReminderDigest
- Template: resources/views/emails/sharpfleet/service-reminder.blade.php
- Sender: app/Console/Commands/SharpFleetSendReminders.php
- Trigger: Scheduled command sharpfleet:send-reminders

10) Mobile support request
- Mailable: none (raw email)
- Template: none (Mail::raw)
- Sender: app/Http/Controllers/SharpFleet/DriverMobileController.php (support)
- Trigger: PWA Support form submit

## Pages that send email (UI triggers)

- Registration form: resources/views/sharpfleet/admin/register.blade.php
- Account activation completion: resources/views/sharpfleet/admin/activate-account.blade.php
- Forgot password: resources/views/sharpfleet/passwords/email.blade.php
- Reset password: resources/views/sharpfleet/passwords/reset.blade.php
- Admin invite driver: resources/views/sharpfleet/admin/users/invite.blade.php
- Admin users list (send/resend invites): resources/views/sharpfleet/admin/users/index.blade.php
- Admin bookings calendar (create/edit/cancel): resources/views/sharpfleet/admin/bookings/index.blade.php
- Driver bookings page (create/edit/cancel): resources/views/sharpfleet/driver/bookings/upcoming.blade.php
- Mobile bookings (create/cancel): resources/views/sharpfleet/mobile/bookings.blade.php
- Mobile support form: resources/views/sharpfleet/mobile/support.blade.php

## Other SharpFleet email senders (non-UI triggers)

- Booking reminder scheduler: app/Console/Commands/SharpFleetSendBookingReminders.php
- Reminder digest scheduler: app/Console/Commands/SharpFleetSendReminders.php

## Possible missing emails (not found as SharpFleet mailables)

- If any other SharpFleet mailables are added, they should live under app/Mail/SharpFleet and templates under resources/views/emails/sharpfleet.
- Support ticket emails under resources/views/emails/support are part of the customer support system and are not currently wired to SharpFleet-specific controllers.
