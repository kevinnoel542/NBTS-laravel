# Project Overview

## What This Project Is

This project is a blood donation platform for NBTS.

It supports the full journey of blood donation:

1. A donor creates an account.
2. The donor finds blood centers and campaigns.
3. The donor books an appointment.
4. Staff confirm or cancel appointments.
5. Staff check if the donor is allowed to donate.
6. Staff record a completed donation.
7. The system creates a blood unit from the donation.
8. The system updates blood inventory.
9. The system creates low stock alerts when blood stock is too low.
10. The system can create emergency campaigns for low stock blood groups.
11. Donors receive badges and rewards based on donation count.
12. Staff can view reports and summaries.

## Who Uses The System

## Public Visitors

Public visitors can use the website without logging in.

They can:

- Read about NBTS.
- View blood centers.
- Search blood centers.
- View blood donation campaigns.
- See national impact statistics.
- Open the app download page.
- Read basic donation eligibility information.

## Donors

Donors mainly use the mobile app through the API.

They can:

- Register.
- Log in.
- View and update their profile.
- View their donor card.
- View eligibility status.
- View campaigns.
- View blood centers.
- Book appointments.
- Cancel their own appointments.
- View donation history.
- View badges, rewards, and leaderboard.
- Register a phone notification token.

## Staff

Staff use protected API routes and the admin dashboard.

They can:

- Search donors.
- View donor profiles.
- Run eligibility checks.
- Add donor deferrals.
- Lift donor deferrals.
- Confirm appointments.
- Cancel appointments.
- Record donations.
- Verify donor blood group.
- View blood inventory.
- View blood units.
- Change blood unit status.
- Adjust inventory.
- Expire old blood units.
- View low stock alerts.
- Create emergency campaigns.
- View reports.

## Admin Users

Admin users use the Filament admin panel at `/admin`.

They can manage:

- Users
- Roles
- Permissions
- Blood centers
- Center staff
- Campaigns
- Appointments
- Donations
- Donor profiles
- Eligibility records
- Deferrals
- Blood units
- Blood inventory
- Inventory adjustments
- Low stock alerts
- Badges
- Rewards
- Leaderboards

## Main Business Rules

The system has these important rules:

- A donor must be at least 18 years old.
- A donor should weigh at least 50 kg.
- A donor cannot donate again until their next eligible donation date.
- After a completed donation, the donor becomes eligible again after 90 days.
- A donor can be temporarily deferred or permanently deferred.
- Staff can lift a deferral.
- A completed donation creates a blood unit.
- A blood unit expires 35 days after collection.
- Inventory should not go below zero.
- If available blood units are lower than the minimum threshold, the system opens a low stock alert.
- If stock rises back above the threshold, open low stock alerts are resolved.
- Donors earn badges and rewards when their donation count reaches set thresholds.

## Important Folders

- `routes/web.php`: Public website routes.
- `routes/api.php`: API routes for mobile app and staff tools.
- `app/Http/Controllers/Web`: Controllers for public website pages.
- `app/Http/Controllers/Api`: Controllers for mobile app API.
- `app/Http/Controllers/Api/Staff`: Controllers for staff API actions.
- `app/Filament/Resources`: Admin dashboard pages.
- `app/Models`: Database models.
- `app/Services`: Main business logic.
- `database/migrations`: Database table structure.
- `database/seeders`: Starting roles, permissions, and demo data.
- `resources/views`: Blade pages for the public website.

