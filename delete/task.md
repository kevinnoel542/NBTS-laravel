# Task Plan

This file breaks the requirements into actionable development tasks.

## Current Baseline

Already present:
- Laravel 12 project
- Filament admin panel
- Sanctum API authentication
- Basic users, blood centers, appointments, donations, and campaigns
- Basic donor mobile API endpoints
- Role-based Filament access check for active `admin` and `staff` users

Known gaps to fix:
- Add formal roles and permissions package
- Move donor-specific data into `donor_profiles`
- Add center staff assignment model
- Align status values across migrations, models, APIs, and Filament
- Add tests for admin access and API flows

## Immediate Tasks

1. Done - Install and configure Spatie Laravel Permission.
2. Done - Create roles: `super_admin`, `nbts_admin`, `center_manager`, `center_staff`, `donor`.
3. Done - Create permissions for donor, center, appointment, donation, inventory, campaign, report, and user management.
4. Done - Seed default roles, permissions, and the first super admin.
5. In progress - Replace simple `role` string checks with permission/policy checks.
6. Done - Create `donor_profiles` table and migrate donor fields from `users`.
7. Done - Create `center_staff` table for assigned blood center access.
8. In progress - Add Filament resource visibility based on role and assigned center.
9. Pending - Add tests for admin, staff, and donor access boundaries.

## API Tasks

1. Done - Review current mobile API response structure.
2. Done - Add donor card endpoint.
3. Done - Add donor ID field/generator.
4. Done - Add QR code payload endpoint.
5. Done - Add authenticated donor profile update endpoint using final field names.
6. Pending - Add API tests for registration, login, profile, centers, campaigns, and donor card.

## Appointment Tasks

1. Done - Add appointment cancellation flow.
2. Done - Add appointment confirmation flow for staff.
3. Pending - Add center-scoped appointment lists.
4. Done - Add walk-in donation path that does not require an appointment.
5. Pending - Add appointment notification triggers.

## Donation Tasks

1. Done - Add donor search by QR, donor ID, phone, and name.
2. Done - Add staff donation recording endpoint and Filament-backed recording service.
3. Done - Add blood group verification fields.
4. Done - Update donor stats after completed donation.
5. Pending - Add audit logs for donation and blood group changes.

## Inventory Tasks

1. Done - Create `blood_units` table.
2. Done - Create `blood_inventory` table.
3. Done - Create `inventory_adjustments` table.
4. Done - Create unit lifecycle actions.
5. Done - Update inventory when units become available, used, transferred, expired, discarded, or rejected.
6. Done - Add expiry handling service endpoint.
7. Pending - Add disposal confirmation workflow.

## Low Stock and Emergency Campaign Tasks

1. Done - Add stock thresholds per center and blood group.
2. Done - Add low stock alerts.
3. Done - Resolve alerts when stock recovers.
4. Done - Add emergency campaign creation from low-stock alerts.
5. Pending - Add eligible donor targeting.
6. Pending - Add automatic push/SMS/email dispatch.

## Reports and Analytics Tasks

1. Done - Add summary report service.
2. Done - Add donation report service.
3. Done - Add inventory report service.
4. Done - Add staff report APIs.
5. Done - Add inventory metrics to public analytics controller.
6. Pending - Add richer regional analytics and export formats.

## Eligibility Tasks

1. Done - Add eligibility status to donor profiles.
2. Done - Add eligibility records.
3. Done - Add temporary and permanent deferrals.
4. Done - Add eligibility evaluation service.
5. Done - Block completed donation recording when donor is ineligible.
6. Done - Add donor eligibility API.
7. Done - Add staff eligibility check, deferral, and lift-deferral APIs.
8. Done - Add Filament resources for deferrals and eligibility records.
9. Pending - Add detailed health-question rules beyond age, weight, deferrals, and recent donation date.

## Donor Card Tasks

1. Done - Add donor ID generator.
2. Done - Add QR payload in donor card response.
3. Done - Include eligibility status and donation stats in donor card response.
4. Pending - Add rendered QR image generation package or mobile-side QR rendering contract.

## Loyalty Tasks

1. Done - Add badges.
2. Done - Add rewards.
3. Done - Add donor badge and donor reward history.
4. Done - Add all-time leaderboard table.
5. Done - Award badges and rewards after completed donations.
6. Done - Add donor loyalty API.
7. Done - Add leaderboard API.
8. Done - Add Filament resources for badges, rewards, and leaderboard.
9. Pending - Add reward redemption workflow.

## Notification Tasks

1. Create notification templates.
2. Create notification history records.
3. Prepare push notification channel.
4. Prepare SMS channel.
5. Prepare email channel.
6. Trigger notifications for appointments, donations, eligibility, badges, rewards, low stock, and campaigns.

## Public Website Tasks

1. Confirm final public pages.
2. Keep donor login out of the website.
3. Improve landing page content.
4. Add contact page.
5. Connect public campaigns and blood center pages to final data model.

## Testing Tasks

1. Add feature tests for auth.
2. Add feature tests for role access.
3. Add donation workflow tests.
4. Add inventory workflow tests.
5. Add API resource response tests.
6. Fix local test database setup.
