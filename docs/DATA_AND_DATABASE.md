# Data And Database

This file explains the main records in the database.

The table structure is defined in `database/migrations`.

The model files are in `app/Models`.

## Users

Model: `User`

Table: `users`

A user can be a donor, staff member, or admin.

Important fields include:

- Name
- Email
- Password
- Phone
- Blood group
- Gender
- Date of birth
- Region
- Last donation date
- Address
- Role
- Active status

A user can have:

- Many appointments
- Many donations
- One donor profile
- Many staff center assignments
- Many eligibility records
- Many deferrals
- Many donor badges
- Many donor rewards
- Many roles and permissions

## Donor Profiles

Model: `DonorProfile`

Table: `donor_profiles`

This stores donor-specific information.

Important fields include:

- User
- Donor ID
- Blood group status
- Blood group verified status
- Blood group verified date
- Blood group verifier
- Next eligible donation date
- Eligibility status
- Last eligibility check date
- Eligibility notes
- Total donations

The donor ID format is like:

`DNR-2026-123456`

## Blood Centers

Model: `BloodCenter`

Table: `blood_centers`

A blood center is a donation location.

Important fields include:

- Name
- Address
- City
- Phone
- Email
- Latitude
- Longitude
- Active status

A blood center can have:

- Many appointments
- Many donations
- Many campaigns
- Many staff assignments
- Many blood units
- Many inventory rows
- Many low stock alerts

## Center Staff

Model: `CenterStaff`

Table: `center_staff`

This connects staff users to blood centers.

Important fields include:

- User
- Blood center
- Position
- Active status

## Campaigns

Model: `Campaign`

Table: `campaigns`

A campaign is a blood donation drive or appeal.

Important fields include:

- Title
- Description
- Start date
- End date
- Blood center
- Location
- Image path
- Status
- Campaign type
- Target blood group
- Low stock alert

A campaign can belong to a low stock alert when it is created as an emergency campaign.

## Appointments

Model: `Appointment`

Table: `appointments`

An appointment is a planned visit by a donor.

Important fields include:

- User
- Blood center
- Scheduled date and time
- Status
- Confirmed time
- Cancelled time
- Staff handler
- Notes

An appointment can have one donation.

## Donations

Model: `Donation`

Table: `donations`

A donation is a recorded blood donation event.

Important fields include:

- User
- Blood center
- Staff recorder
- Appointment
- Donation type
- Blood group
- Blood group verified status
- Volume in ml
- Donation date
- Status
- Notes

A donation can create one blood unit.

## Blood Units

Model: `BloodUnit`

Table: `blood_units`

A blood unit is the physical unit collected from a donation.

Important fields include:

- Unit number
- Donation
- Donor
- Blood center
- Blood group
- Collection date
- Expiry date
- Status
- Current location
- Staff handler

The unit number format is like:

`BU-20260622-123456`

A blood unit expires 35 days after collection.

## Blood Inventory

Model: `BloodInventory`

Table: `blood_inventory`

Inventory stores blood stock totals by blood center and blood group.

Important fields include:

- Blood center
- Blood group
- Available units
- Reserved units
- Minimum threshold

The minimum threshold is used to detect low stock.

## Inventory Adjustments

Model: `InventoryAdjustment`

Table: `inventory_adjustments`

This records every manual or automatic change to inventory.

Important fields include:

- Blood center
- Blood unit
- Staff adjuster
- Blood group
- Quantity change
- Reason
- Notes

## Low Stock Alerts

Model: `LowStockAlert`

Table: `low_stock_alerts`

This records low stock problems.

Important fields include:

- Blood center
- Blood group
- Available units
- Minimum threshold
- Status
- Resolved time

The system opens an alert when available units are below the minimum threshold.

The system resolves open alerts when stock reaches the threshold again.

## Eligibility Records

Model: `EligibilityRecord`

Table: `eligibility_records`

This stores donor screening results.

Important fields include:

- User
- Staff checker
- Status
- Age
- Weight in kg
- Answers
- Next eligible donation date
- Notes

## Deferrals

Model: `Deferral`

Table: `deferrals`

A deferral blocks a donor from donating.

Important fields include:

- User
- Staff creator
- Type
- Reason
- Notes
- Start date
- End date
- Active status
- Lifted time
- Staff lifter

Deferral types:

- Temporary
- Permanent

## Badges

Model: `Badge`

Table: `badges`

A badge is an achievement for donors.

Important fields include:

- Name
- Slug
- Description
- Icon
- Donation threshold
- Active status

## Donor Badges

Model: `DonorBadge`

Table: `donor_badges`

This stores badges awarded to donors.

Important fields include:

- User
- Badge
- Awarded time

## Rewards

Model: `Reward`

Table: `rewards`

A reward is something a donor earns after reaching a donation threshold.

Important fields include:

- Name
- Slug
- Description
- Donation threshold
- Active status

## Donor Rewards

Model: `DonorReward`

Table: `donor_rewards`

This stores rewards awarded to donors.

Important fields include:

- User
- Reward
- Status
- Awarded time
- Redeemed time

## Leaderboards

Model: `Leaderboard`

Table: `leaderboards`

This stores donor ranking.

Important fields include:

- User
- Period
- Donation count
- Rank

The current code uses the `all_time` period.

## FCM Tokens

Model: `FCMToken`

Table: `f_c_m_tokens`

This stores mobile device notification tokens.

Important fields include:

- User
- Token
- Device type

## Auth And System Tables

The project also has standard Laravel tables:

- `personal_access_tokens` for Sanctum API tokens.
- `jobs`, `job_batches`, and `failed_jobs` for queues.
- `cache` and `cache_locks`.
- `sessions`.
- `password_reset_tokens`.

It also has Spatie permission tables:

- `permissions`
- `roles`
- `model_has_permissions`
- `model_has_roles`
- `role_has_permissions`

