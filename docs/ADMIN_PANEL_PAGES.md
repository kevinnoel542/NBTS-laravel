# Admin Panel Pages

The admin panel is built with Filament.

Admin URL: `/admin`

Admin resources are in `app/Filament/Resources`.

Each resource normally gives staff these pages:

- List page: view many records.
- Create page: add a new record.
- Edit page: change an existing record.

Access to the admin panel is controlled by the user model. A user must be active and must have one of these roles:

- `super_admin`
- `nbts_admin`
- `center_manager`
- `center_staff`

Each admin page is also restricted by permissions. If a staff user does not have the needed permission, the page is hidden from the admin menu and direct access is blocked by Filament.

## Permission-Based Dashboard

The admin dashboard uses one shared dashboard template for all staff and admin users.

The dashboard does not depend on role names. It depends on permissions.

This means a new role can be created later without creating a new dashboard page.

For example:

- A role with `inventory.view` sees inventory dashboard cards.
- A role with `campaigns.view` sees campaign dashboard cards.
- A role with `loyalty.manage` sees loyalty dashboard cards.
- A role without `users.view` does not see user dashboard cards.

Custom dashboard widgets are stored in:

`app/Filament/Widgets`

The widgets are registered in:

`app/Providers/Filament/AdminPanelProvider.php`

When creating a new role, assign the right permissions to the role. The dashboard will update based on those permissions.

## Dashboard

Page: Filament Dashboard

This is the first page after login.

It shows the account widget and NBTS dashboard widgets allowed by the user's permissions.

## Users

Resource: `UserResource`

This page manages system users.

It stores:

- Name
- Email
- Password
- Phone
- Blood group
- Gender
- Date of birth
- Region
- Address
- Roles
- Active status

Staff use it to create and manage donor, staff, and admin accounts.

## Roles

Resource: `RoleResource`

This page manages roles.

Roles decide what a user can do.

The project uses roles like:

- `super_admin`
- `nbts_admin`
- `center_manager`
- `center_staff`
- `donor`

## Permissions

Resource: `PermissionResource`

This page manages permission names.

Permissions are small actions such as:

- `users.view`
- `users.manage`
- `appointments.manage`
- `donations.record`
- `inventory.manage`
- `reports.view`

Roles receive permissions.

Users receive roles.

## Blood Centers

Resource: `BloodCenterResource`

This page manages blood donation centers.

It stores:

- Name
- Address
- City
- Phone
- Email
- Latitude
- Longitude
- Active status

Blood centers are used by appointments, donations, campaigns, staff assignments, blood units, inventory, and alerts.

## Center Staff

Resource: `CenterStaffResource`

This page connects staff users to blood centers.

It stores:

- Staff user
- Blood center
- Position
- Active status

Use this page to show which staff member belongs to which center.

## Campaigns

Resource: `CampaignResource`

This page manages donation campaigns.

It stores:

- Title
- Description
- Blood center
- Location
- Start date
- End date
- Poster image
- Status
- Campaign type
- Target blood group
- Low stock alert link

Campaign statuses include active, upcoming, completed, and cancelled.

Campaign types can include regular and emergency campaigns.

Emergency campaigns can be created from low stock alerts.

## Appointments

Resource: `AppointmentResource`

This page manages donor appointments.

It stores:

- Donor
- Blood center
- Scheduled date and time
- Status
- Confirmed time
- Cancelled time
- Staff handler
- Notes

Appointment statuses include:

- Pending
- Confirmed
- Completed
- Cancelled

## Donations

Resource: `DonationResource`

This page manages donation records.

It stores:

- Donor
- Blood center
- Related appointment
- Donation type
- Blood group
- Blood group verified status
- Volume in ml
- Donation date
- Status
- Notes

When a donation is completed through the service layer, the system updates the donor, awards loyalty items, and creates a blood unit.

## Donor Profiles

Resource: `DonorProfileResource`

This page manages extra donor information.

It stores:

- Donor user
- Donor ID
- Blood group status
- Blood group verified status
- Blood group verified date
- Blood group verifier
- Eligibility status
- Next eligible donation date
- Last eligibility check time
- Eligibility notes
- Total donations

The donor profile is used for donor cards, eligibility, loyalty, and donation history.

## Eligibility Records

Resource: `EligibilityRecordResource`

This page shows donor eligibility check records.

It stores:

- Donor
- Staff checker
- Status
- Age
- Weight in kg
- Answers
- Next eligible donation date
- Notes

This gives staff a history of donor screening results.

## Deferrals

Resource: `DeferralResource`

This page manages donor deferrals.

A deferral means the donor cannot donate for a reason.

It stores:

- Donor
- Staff creator
- Type: temporary or permanent
- Reason
- Notes
- Start date
- End date
- Active status
- Lifted time
- Staff who lifted it

Temporary deferrals can have an end date.

Permanent deferrals do not need an end date.

## Blood Units

Resource: `BloodUnitResource`

This page manages individual blood units.

It stores:

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

Blood unit statuses include:

- Collected
- Testing
- Available
- Reserved
- Issued
- Expired
- Discarded

Available units affect inventory totals.

## Blood Inventory

Resource: `BloodInventoryResource`

This page manages stock levels by center and blood group.

It stores:

- Blood center
- Blood group
- Available units
- Reserved units
- Minimum threshold

If available units are below the minimum threshold, the system can create a low stock alert.

## Inventory Adjustments

Resource: `InventoryAdjustmentResource`

This page records stock changes.

It stores:

- Blood center
- Blood unit
- Blood group
- Quantity change
- Reason
- Notes
- Staff adjuster

Inventory adjustments help explain why stock increased or decreased.

## Low Stock Alerts

Resource: `LowStockAlertResource`

This page manages alerts for low blood stock.

It stores:

- Blood center
- Blood group
- Available units
- Minimum threshold
- Status
- Resolved time

Alert statuses include:

- Open
- Notified
- Campaign created
- Resolved

## Badges

Resource: `BadgeResource`

This page manages donor badges.

It stores:

- Name
- Slug
- Description
- Icon
- Donation threshold
- Active status

Donors receive badges when their total donation count reaches the threshold.

## Rewards

Resource: `RewardResource`

This page manages donor rewards.

It stores:

- Name
- Slug
- Description
- Donation threshold
- Active status

Donors receive rewards when their donation count reaches the threshold.

## Leaderboards

Resource: `LeaderboardResource`

This page manages leaderboard rows.

It stores:

- Donor
- Period
- Donation count
- Rank

The loyalty service refreshes the leaderboard based on donor total donations.
