# SharpFleet Admin Routes (Desktop)

This map covers the desktop admin routes under `/app/sharpfleet/admin` and their handlers. Mobile routes under `/app/sharpfleet/mobile` are unchanged.

Example (reported issue): `/app/sharpfleet/admin/vehicles` now maps to `GET VehicleController@index` (see full map below).

## Previously missing (non-working) URLs

- `POST /app/sharpfleet/admin/account/upgrade-to-sole-trader` -> added route to `AccountController@upgradeToSoleTrader`
- `GET /app/sharpfleet/admin/reports` -> added redirect to `/app/sharpfleet/admin/reports/trips`

## Current admin route map (after updates)

### Auth and entry
- `GET /app/sharpfleet/admin` -> `DashboardController@index`
- `GET /app/sharpfleet/admin/dashboard` -> `DashboardController@index`
- `GET /app/sharpfleet/admin/login` -> `AuthController@showLogin`
- `GET|POST /app/sharpfleet/admin/logout` -> `AuthController@logout`

### Account
- `GET /app/sharpfleet/admin/account` -> `AccountController@show`
- `POST /app/sharpfleet/admin/account/subscribe` -> `AccountController@subscribe`
- `POST /app/sharpfleet/admin/account/cancel-trial` -> `AccountController@cancelTrial`
- `POST /app/sharpfleet/admin/account/cancel-subscription` -> `AccountController@cancelSubscription`
- `POST /app/sharpfleet/admin/account/upgrade-to-sole-trader` -> `AccountController@upgradeToSoleTrader`

### Company and settings
- `GET /app/sharpfleet/admin/company` -> `CompanyController@index`
- `GET /app/sharpfleet/admin/company/profile` -> `CompanyProfileController@edit`
- `POST /app/sharpfleet/admin/company/profile` -> `CompanyProfileController@update`
- `GET /app/sharpfleet/admin/settings` -> `CompanySettingsController@edit`
- `POST /app/sharpfleet/admin/settings` -> `CompanySettingsController@update`
- `GET /app/sharpfleet/admin/safety-checks` -> `CompanySafetyCheckController@index`
- `POST /app/sharpfleet/admin/safety-checks` -> `CompanySafetyCheckController@update`

### Branches
- `GET /app/sharpfleet/admin/branches` -> `BranchController@index`
- `GET /app/sharpfleet/admin/branches/create` -> `BranchController@create`
- `POST /app/sharpfleet/admin/branches` -> `BranchController@store`
- `GET /app/sharpfleet/admin/branches/{branchId}/edit` -> `BranchController@edit`
- `POST /app/sharpfleet/admin/branches/{branchId}` -> `BranchController@update`

### Users and invites
- `GET /app/sharpfleet/admin/users` -> `UserController@index`
- `GET /app/sharpfleet/admin/users/{userId}/edit` -> `UserController@edit`
- `POST /app/sharpfleet/admin/users/{userId}` -> `UserController@update`
- `POST /app/sharpfleet/admin/users/{userId}/delete` -> `UserController@destroy`
- `POST /app/sharpfleet/admin/users/{userId}/unarchive` -> `UserController@unarchive`
- `GET /app/sharpfleet/admin/users/invite` -> `AdminDriverInviteController@create`
- `POST /app/sharpfleet/admin/users/invite` -> `AdminDriverInviteController@store`
- `GET /app/sharpfleet/admin/users/add` -> `AdminDriverInviteController@createManual`
- `POST /app/sharpfleet/admin/users/add` -> `AdminDriverInviteController@storeManual`
- `GET /app/sharpfleet/admin/users/import` -> `AdminDriverInviteController@createImport`
- `POST /app/sharpfleet/admin/users/import` -> `AdminDriverInviteController@storeImport`
- `POST /app/sharpfleet/admin/users/send-invites` -> `AdminDriverInviteController@sendInvites`
- `POST /app/sharpfleet/admin/users/{userId}/resend-invite` -> `AdminDriverInviteController@resend`

### Customers
- `GET /app/sharpfleet/admin/customers` -> `CustomerController@index`
- `GET /app/sharpfleet/admin/customers/create` -> `CustomerController@create`
- `POST /app/sharpfleet/admin/customers` -> `CustomerController@store`
- `GET /app/sharpfleet/admin/customers/{customerId}/edit` -> `CustomerController@edit`
- `POST /app/sharpfleet/admin/customers/{customerId}` -> `CustomerController@update`
- `POST /app/sharpfleet/admin/customers/{customerId}/archive` -> `CustomerController@archive`

### Vehicles
- `GET /app/sharpfleet/admin/vehicles` -> `VehicleController@index`
- `GET /app/sharpfleet/admin/vehicles/assigned` -> `VehicleController@assigned`
- `GET /app/sharpfleet/admin/vehicles/out-of-service` -> `VehicleController@outOfService`
- `GET /app/sharpfleet/admin/vehicles/create` -> `VehicleController@create`
- `GET /app/sharpfleet/admin/vehicles/create/confirm` -> `VehicleController@confirmCreate`
- `POST /app/sharpfleet/admin/vehicles/create/confirm` -> `VehicleController@confirmStore`
- `POST /app/sharpfleet/admin/vehicles/create/cancel` -> `VehicleController@cancelCreate`
- `POST /app/sharpfleet/admin/vehicles` -> `VehicleController@store`
- `GET /app/sharpfleet/admin/vehicles/{vehicle}/edit` -> `VehicleController@edit`
- `POST /app/sharpfleet/admin/vehicles/{vehicle}` -> `VehicleController@update`
- `GET /app/sharpfleet/admin/vehicles/{vehicle}/archive/confirm` -> `VehicleController@confirmArchive`
- `POST /app/sharpfleet/admin/vehicles/{vehicle}/archive/confirm` -> `VehicleController@confirmArchiveStore`
- `POST /app/sharpfleet/admin/vehicles/{vehicle}/archive/cancel` -> `VehicleController@cancelArchive`
- `POST /app/sharpfleet/admin/vehicles/{vehicle}/archive` -> `VehicleController@archive`

### Bookings and trips
- `GET /app/sharpfleet/admin/bookings` -> `AdminBookingController@index`
- `GET /app/sharpfleet/admin/bookings/feed` -> `AdminBookingController@feed`
- `POST /app/sharpfleet/admin/bookings` -> `AdminBookingController@store`
- `POST /app/sharpfleet/admin/bookings/{booking}` -> `AdminBookingController@update`
- `POST /app/sharpfleet/admin/bookings/{booking}/cancel` -> `AdminBookingController@cancel`
- `POST /app/sharpfleet/admin/bookings/{booking}/change-vehicle` -> `AdminBookingController@changeVehicle`
- `GET /app/sharpfleet/admin/bookings/available-vehicles` -> `AdminBookingController@availableVehicles`
- `GET /app/sharpfleet/admin/trips/active` -> `AdminBookingController@activeTrips`

### Faults and reminders
- `GET /app/sharpfleet/admin/faults` -> `AdminFaultController@index`
- `POST /app/sharpfleet/admin/faults/{fault}/status` -> `AdminFaultController@updateStatus`
- `GET /app/sharpfleet/admin/reminders` -> `ReminderController@index`

### Reports
- `GET /app/sharpfleet/admin/reports` -> redirect to `/app/sharpfleet/admin/reports/trips`
- `GET /app/sharpfleet/admin/reports/trips` -> `ReportController@trips`

### Setup wizard
- `GET /app/sharpfleet/admin/setup/company` -> `SetupWizardController@company`
- `POST /app/sharpfleet/admin/setup/company` -> `SetupWizardController@storeCompany`
- `GET /app/sharpfleet/admin/setup/settings/presence` -> `SetupWizardController@settingsPresence`
- `POST /app/sharpfleet/admin/setup/settings/presence` -> `SetupWizardController@storeSettingsPresence`
- `GET /app/sharpfleet/admin/setup/settings/customer` -> `SetupWizardController@settingsCustomer`
- `POST /app/sharpfleet/admin/setup/settings/customer` -> `SetupWizardController@storeSettingsCustomer`
- `GET /app/sharpfleet/admin/setup/settings/trip-rules` -> `SetupWizardController@settingsTripRules`
- `POST /app/sharpfleet/admin/setup/settings/trip-rules` -> `SetupWizardController@storeSettingsTripRules`
- `GET /app/sharpfleet/admin/setup/settings/vehicle-tracking` -> `SetupWizardController@settingsVehicleTracking`
- `POST /app/sharpfleet/admin/setup/settings/vehicle-tracking` -> `SetupWizardController@storeSettingsVehicleTracking`
- `GET /app/sharpfleet/admin/setup/settings/reminders` -> `SetupWizardController@settingsReminders`
- `POST /app/sharpfleet/admin/setup/settings/reminders` -> `SetupWizardController@storeSettingsReminders`
- `GET /app/sharpfleet/admin/setup/settings/client-addresses` -> `SetupWizardController@settingsClientAddresses`
- `POST /app/sharpfleet/admin/setup/settings/client-addresses` -> `SetupWizardController@storeSettingsClientAddresses`
- `GET /app/sharpfleet/admin/setup/settings/safety-check` -> `SetupWizardController@settingsSafetyCheck`
- `POST /app/sharpfleet/admin/setup/settings/safety-check` -> `SetupWizardController@storeSettingsSafetyCheck`
- `GET /app/sharpfleet/admin/setup/settings/incident-reporting` -> `SetupWizardController@settingsIncidentReporting`
- `POST /app/sharpfleet/admin/setup/settings/incident-reporting` -> `SetupWizardController@storeSettingsIncidentReporting`
- `GET /app/sharpfleet/admin/setup/finish` -> `SetupWizardController@finishView`
- `POST /app/sharpfleet/admin/setup/finish` -> `SetupWizardController@finish`
- `POST /app/sharpfleet/admin/setup/rerun` -> `SetupWizardController@rerun`

### Help and about
- `GET /app/sharpfleet/admin/help` -> `HelpController@admin`
- `GET /app/sharpfleet/admin/about` -> `view('sharpfleet.about')`
