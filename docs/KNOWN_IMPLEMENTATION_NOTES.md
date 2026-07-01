# Known Implementation Notes

This file lists important things found while reading the code.

These are not explanations of how the project should work. They are notes about the current code.

## Blood Center Active Field

The `blood_centers` table uses `is_active`.

The API now returns active centers through the blood center service and resource.

Keep using `is_active` for center availability.

## Blood Center Phone Field

The `blood_centers` table has a `phone` field.

The public center views use:

`phone_number`

That means the page may show the fallback phone number instead of the real phone number.

Expected fix:

Use `phone` in the views, or add an accessor named `phone_number`.

## Campaign Status

The `campaigns` table uses these statuses:

- `upcoming`
- `ongoing`
- `completed`
- `cancelled`

The mobile API returns campaigns with status:

- `upcoming`
- `ongoing`

The API resource also exposes `is_active=true` when the status is `ongoing`.

Do not add a separate `active` status unless the migrations, admin forms, API filters, and Flutter app are updated together.

## Firebase Login

The Flutter app posts Google/Firebase login data to:

`POST /api/v1/auth/firebase`

Laravel now has this route.

Laravel needs this `.env` value:

`FIREBASE_PROJECT_ID=nbts-d567e`

Without it, Firebase token verification will fail.

## Mobile API Documentation

The mobile API contract is documented in:

`docs/MOBILE_APP_API.md`

The backend/mobile sync checklist is documented in:

`docs/MOBILE_BACKEND_SYNC_CHECKLIST.md`

## Admin And API Use The Same Data

The admin panel and API both manage the same database tables.

Changing a field name in one place means you must check:

- Model fillable fields.
- Migration columns.
- Filament resource forms and tables.
- API resources.
- Public Blade views.
- Controllers.
- Services.

## Tests Are Minimal

The current test files are default example tests.

There are no detailed tests yet for:

- Donor registration.
- Appointment booking.
- Eligibility checks.
- Donation recording.
- Inventory changes.
- Low stock alerts.
- Loyalty rewards.
- Admin permissions.

These areas are important because they contain the main business rules.
