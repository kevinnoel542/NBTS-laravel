# Known Implementation Notes

This file lists important things found while reading the code.

These are not explanations of how the project should work. They are notes about the current code.

## Blood Center Active Field

The `blood_centers` table has an `is_active` field.

The `BloodCenter` model also uses `is_active`.

But the public centers controller uses:

`where('status', 'active')`

That means the centers page may fail or return wrong data unless the database also has a `status` column.

Expected fix:

Use `where('is_active', true)` for active blood centers.

## Blood Center Phone Field

The `blood_centers` table has a `phone` field.

The public center views use:

`phone_number`

That means the page may show the fallback phone number instead of the real phone number.

Expected fix:

Use `phone` in the views, or add an accessor named `phone_number`.

## Campaign Active Status

The `campaigns` table originally defines these statuses:

- `upcoming`
- `ongoing`
- `completed`
- `cancelled`

Some controllers use:

`where('status', 'active')`

Those queries may not return campaigns unless the database has records with `active` status or later migrations changed the allowed values.

Expected fix:

Use the same status names everywhere. For example, use `ongoing` for active campaigns, or update the schema and forms to support `active`.

## Campaign List Difference

The public website campaigns page lists all campaigns.

The mobile API campaign list only returns campaigns with status `active`.

Because of the status mismatch above, the mobile API may return an empty campaign list.

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

