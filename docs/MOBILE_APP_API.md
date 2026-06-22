# Mobile App API

The API routes are in `routes/api.php`.

All API routes are under:

`/api/v1`

The API uses Laravel Sanctum tokens for login.

## Test Route

`GET /api/v1/ping`

This checks if the API is working.

Response example:

```json
{
  "status": "API working"
}
```

## Authentication

### Register

`POST /api/v1/auth/register`

Creates a donor account.

It accepts:

- Name
- Email
- Phone
- Password
- Blood group
- Gender
- Region
- Date of birth

After registration:

- The user gets the donor role.
- A donor profile is created.
- A donor ID is generated.
- A Sanctum token is returned.

### Login

`POST /api/v1/auth/login`

Logs in a user.

The user can log in with email or phone.

The API returns a Sanctum token.

### Logout

`POST /api/v1/auth/logout`

Deletes the current access token.

The user must be logged in.

## Public Lookup Routes

These routes do not need login.

### Campaigns

`GET /api/v1/campaigns`

Returns active campaigns.

`GET /api/v1/campaigns/{id}`

Returns one campaign.

### Blood Centers

`GET /api/v1/blood-centers`

Returns active blood centers.

`GET /api/v1/blood-centers/{id}`

Returns one blood center.

## Protected Donor Routes

These routes need a Sanctum token.

### Current User

`GET /api/v1/user`

Returns the logged in user and donor profile.

### Profile

`GET /api/v1/profile`

Returns the logged in user's profile.

`PUT /api/v1/profile`

Updates profile details.

### Donor Card

`GET /api/v1/donor-card`

Returns the donor card data.

If the donor profile does not exist yet, the API creates one.

### Eligibility

`GET /api/v1/eligibility`

Returns whether the donor is allowed to donate.

The backend checks:

- Age
- Weight if available
- Active deferrals
- Next eligible donation date

### Loyalty

`GET /api/v1/loyalty`

Returns:

- Total donations
- Next eligible donation date
- Badges
- Rewards

`GET /api/v1/leaderboard`

Returns the top donors by donation count.

### Donation History

`GET /api/v1/donations`

Returns the logged in donor's donation history.

### Appointments

`GET /api/v1/appointments`

Returns all appointments for the logged in donor.

`GET /api/v1/appointments/upcoming`

Returns upcoming appointments.

`POST /api/v1/appointments`

Books a new appointment.

Required data:

- Blood center ID
- Scheduled date and time

The appointment starts as pending.

`GET /api/v1/appointments/{id}`

Returns one appointment owned by the logged in donor.

`POST /api/v1/appointments/{id}/cancel`

Cancels one appointment owned by the logged in donor.

Completed and already cancelled appointments cannot be cancelled.

### Notifications

`POST /api/v1/notifications/register-token`

Stores a device notification token.

It accepts:

- Token
- Device type

Device type defaults to android.

## Staff API Routes

Staff routes are under:

`/api/v1/staff`

They need login and permissions.

### Donor Search

`GET /api/v1/staff/donors/search`

Searches donors by:

- Phone
- Name
- Email
- Donor ID
- Any of the above

Permission needed:

- `donors.view`

`GET /api/v1/staff/donors/{donor}`

Returns one donor profile.

Permission needed:

- `donors.view`

### Staff Eligibility

`POST /api/v1/staff/donors/{donor}/eligibility-check`

Records a donor eligibility check.

Permission needed:

- `donors.view`

It can include:

- Weight
- Answers
- Notes

`POST /api/v1/staff/donors/{donor}/deferrals`

Creates a deferral for a donor.

Permission needed:

- `donors.manage`

`POST /api/v1/staff/deferrals/{deferral}/lift`

Lifts an active deferral and recalculates eligibility.

Permission needed:

- `donors.manage`

### Staff Appointment Management

`POST /api/v1/staff/appointments/{appointment}/confirm`

Confirms a pending appointment.

Permission needed:

- `appointments.manage`

`POST /api/v1/staff/appointments/{appointment}/cancel`

Cancels an appointment.

Permission needed:

- `appointments.manage`

### Staff Donation Recording

`POST /api/v1/staff/donations`

Records a donation.

Permission needed:

- `donations.record`

The donor must have the donor role.

If the donation type is appointment based, an appointment ID is required.

When a donation is completed, the system:

- Checks donor eligibility.
- Creates the donation.
- Marks the appointment completed if there is one.
- Updates donor profile.
- Awards badges and rewards.
- Creates a blood unit.
- Updates inventory.

`POST /api/v1/staff/donations/{donation}/verify-blood-group`

Marks the donation blood group as verified.

Permission needed:

- `donations.record`

### Staff Inventory

`GET /api/v1/staff/inventory`

Returns inventory rows by center and blood group.

Permission needed:

- `inventory.view`

`GET /api/v1/staff/blood-units`

Returns blood units.

Permission needed:

- `inventory.view`

`POST /api/v1/staff/blood-units/{unit}/transition`

Changes a blood unit status.

Permission needed:

- `inventory.manage`

If a unit moves into or out of available status, inventory is updated.

`POST /api/v1/staff/inventory/adjust`

Manually adjusts inventory.

Permission needed:

- `inventory.manage`

Inventory cannot go below zero.

`POST /api/v1/staff/inventory/expire-due`

Finds expired blood units and marks them expired.

Permission needed:

- `inventory.manage`

`GET /api/v1/staff/low-stock-alerts`

Returns active low stock alerts.

Permission needed:

- `inventory.view`

`POST /api/v1/staff/low-stock-alerts/{alert}/emergency-campaign`

Creates an emergency campaign from a low stock alert.

Permission needed:

- `campaigns.manage`

### Staff Reports

`GET /api/v1/staff/reports/summary`

Returns high level numbers.

Permission needed:

- `reports.view`

`GET /api/v1/staff/reports/donations`

Returns donation report data.

Permission needed:

- `reports.view`

`GET /api/v1/staff/reports/inventory`

Returns inventory report data.

Permission needed:

- `reports.view`

