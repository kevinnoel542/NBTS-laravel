# Mobile App API

This file explains the Laravel API used by the NBTS Flutter app.

The mobile app code that was checked is in:

`nbts-mobile`

The main mobile API base URL is set in:

`nbts-mobile/lib/core/api/api_config.dart`

Current mobile default:

`http://192.168.0.156/api/v1`

If Laravel is served with `php artisan serve --host=0.0.0.0 --port=8003`, the mobile base URL should include the port:

`http://YOUR_COMPUTER_IP:8003/api/v1`

If Laravel is served by Apache or Nginx at the host root, the mobile base URL can stay without the port:

`http://YOUR_COMPUTER_IP/api/v1`

## Important Rule

All mobile routes start with:

`/api/v1`

Most donor routes need a Sanctum token.

The mobile app sends this header after login:

`Authorization: Bearer TOKEN_HERE`

The API should always return JSON.

## Response Shapes The Mobile App Accepts

For lists, the mobile app accepts:

```json
[
  {}
]
```

or:

```json
{
  "data": [
    {}
  ]
}
```

For one record, the mobile app accepts:

```json
{
  "data": {}
}
```

or:

```json
{}
```

For auth, the mobile app accepts a token from any of these fields:

- `token`
- `access_token`
- `auth_token`
- `plain_text_token`
- `plainTextToken`

The current Laravel auth response uses:

```json
{
  "token": "sanctum-token-here",
  "user": {}
}
```

## Test Route

### `GET /api/v1/ping`

Checks if the API is reachable.

Example response:

```json
{
  "status": "API working"
}
```

## Authentication

### `POST /api/v1/auth/register`

Creates a donor account.

Flutter sends:

```json
{
  "name": "Donor Name",
  "email": "donor@example.com",
  "phone": "+255700000000",
  "password": "secret",
  "password_confirmation": "secret",
  "blood_group": "O+",
  "gender": "male",
  "region": "Dar es Salaam",
  "date_of_birth": "2000-01-01"
}
```

Laravel should:

- Create the user.
- Give the user the donor role.
- Create the donor profile.
- Generate a donor ID.
- Return a Sanctum token and user data.

### `POST /api/v1/auth/login`

Logs in with email or phone.

Flutter sends:

```json
{
  "identifier": "donor@example.com",
  "password": "secret"
}
```

Laravel returns a Sanctum token and user data.

### `POST /api/v1/auth/firebase`

Used by Google login and other Firebase social login flows.

This endpoint is needed because Firebase login on the phone is not the same as Laravel login. The phone gets a Firebase ID token first, then sends it to Laravel. Laravel verifies it and returns a normal Sanctum token.

Flutter sends:

```json
{
  "provider": "google.com",
  "firebase_id_token": "firebase-id-token-here",
  "id_token": "firebase-id-token-here",
  "email": "donor@example.com",
  "name": "Donor Name",
  "photo_url": "https://example.com/photo.jpg",
  "firebase_uid": "firebase-user-id"
}
```

Laravel should:

- Verify the Firebase ID token.
- Check the Firebase project ID.
- Check token signature, expiry, issuer, audience, and subject.
- Find a user by `firebase_uid` or email.
- Create a donor user if no user exists.
- Save `firebase_uid` and `firebase_provider`.
- Create a donor profile if missing.
- Return a Sanctum token and user data.

Needed `.env` value:

```env
FIREBASE_PROJECT_ID=nbts-d567e
```

For push notifications, these values are also used:

```env
FIREBASE_NOTIFICATIONS_ENABLED=true
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-service-account.json
```

### `POST /api/v1/auth/logout`

Deletes the current token.

This route needs login.

## Current User And Profile

### `GET /api/v1/user`

Returns the logged in user.

The mobile app reads these important fields:

- `id`
- `name`
- `email`
- `phone`
- `blood_group`
- `gender`
- `region`
- `date_of_birth`
- `donor_id`
- `preferred_center`
- `loyalty_tier`
- `loyalty_points`
- `total_donations`
- `total_volume_ml`
- `next_eligible_date`

### `GET /api/v1/profile`

Returns the logged in user's profile.

### `PUT /api/v1/profile`

Updates profile details.

The mobile app can send only the fields being changed.

Important fields:

- `phone`
- `blood_group`
- `gender`
- `region`
- `date_of_birth`
- `address`
- `preferred_center_id`
- `emergency_contact_name`
- `emergency_contact_phone`
- `push_notifications_enabled`
- `sms_reminders_enabled`
- `share_anonymized_data`
- `language`

## Public Lookup Routes

These routes do not need login.

### `GET /api/v1/campaigns`

Returns campaigns for the app.

Current Laravel returns campaigns with status:

- `upcoming`
- `ongoing`

The mobile app reads:

- `id`
- `title`
- `summary` or `description`
- `category` or `type`
- `blood_type` or `blood_group`
- `starts_at` or `start_date`
- `ends_at` or `end_date`
- `urgent`
- `blood_center`

### `GET /api/v1/campaigns/{id}`

Returns one campaign.

### `GET /api/v1/articles`

Returns education and health articles.

The mobile app reads:

- `id`
- `title`
- `category`
- `summary`
- `body`
- `image_url`
- `status`
- `published_at`

### `GET /api/v1/articles/{id}`

Returns one article.

### `GET /api/v1/blood-centers`

Returns active blood centers.

The mobile app reads:

- `id`
- `name`
- `address`
- `distance_km`
- `hours` or `opening_hours`
- `phone`
- `wait_time`
- `capacity_label`
- `services`
- `is_open`

### `GET /api/v1/blood-centers/{id}`

Returns one blood center.

### `GET /api/v1/blood-centers/{bloodCenter}/available-slots`

Returns appointment slots for one center.

Query:

`?date=2026-07-01`

Response data:

- `time`
- `scheduled_at`
- `available`

## Donor Card

### `GET /api/v1/donor-card`

Returns the donor card and QR payload.

If the donor profile does not exist, Laravel creates it.

The mobile app reads:

- `donor_id`
- `qr_payload`
- `qr_expires_at`
- `donor.name`
- `donor.phone`
- `donor.blood_group`
- `donor.blood_group_verified`
- `donor.region`
- `donor.preferred_center`
- `stats.total_donations`
- `stats.last_donation`
- `stats.next_eligible_donation_date`
- `stats.eligibility_status`
- `stats.loyalty_points`
- `stats.loyalty_tier`

The QR code is rendered by Flutter from the `qr_payload` string that Laravel sends.

## Eligibility

### `GET /api/v1/eligibility`

Returns whether the donor can donate.

The mobile app reads:

- `status`
- `eligible`
- `message`
- `reasons`
- `next_eligible_donation_date`

Laravel checks:

- Age.
- Weight if available.
- Active deferrals.
- Next eligible donation date.

## Loyalty

### `GET /api/v1/loyalty`

Returns the donor loyalty information.

Current response includes:

- `stats.total_donations`
- `stats.next_eligible_donation_date`
- `badges`
- `rewards`

### `GET /api/v1/leaderboard`

Returns top donors.

## Donation History

### `GET /api/v1/donations`

Returns donation history for the logged in donor.

The mobile app reads:

- `id`
- `donation_date` or `donated_at`
- `blood_center` or `center`
- `blood_group` or `blood_type`
- `volume_ml`
- `status`
- `donation_type`

### `GET /api/v1/donations/summary`

Returns summary numbers for the donor donation history.

## Appointments

### `GET /api/v1/appointments`

Returns all appointments for the logged in donor.

The mobile app reads:

- `id`
- `scheduled_at`
- `blood_center_id`
- `center_id`
- `center_name`
- `status`
- `notes`

### `GET /api/v1/appointments/upcoming`

Returns the next upcoming appointment.

If there is no upcoming appointment, Laravel returns:

```json
{
  "data": null
}
```

### `POST /api/v1/appointments`

Books a new appointment.

Flutter sends:

```json
{
  "center_id": 1,
  "blood_center_id": 1,
  "scheduled_at": "2026-07-01T09:30:00.000",
  "notes": "Morning appointment"
}
```

Laravel uses `blood_center_id`.

The app sends both `center_id` and `blood_center_id` so both naming styles are easy to support.

### `GET /api/v1/appointments/{id}`

Returns one appointment owned by the logged in donor.

### `PUT /api/v1/appointments/{id}`

Reschedules one appointment.

Flutter sends:

```json
{
  "blood_center_id": 1,
  "scheduled_at": "2026-07-02T11:00:00.000",
  "notes": "Changed time"
}
```

Completed and cancelled appointments cannot be rescheduled.

### `POST /api/v1/appointments/{id}/cancel`

Cancels one appointment owned by the logged in donor.

Completed and already cancelled appointments cannot be cancelled.

## Notifications

These routes need login.

### `GET /api/v1/notifications`

Returns the in-app notification list.

The mobile app reads:

- `id`
- `title`
- `body` or `message`
- `type`
- `read`
- `read_at`
- `sent_at`
- `created_at`

Laravel also returns `meta.unread_count`.

### `GET /api/v1/notifications/unread-count`

Returns the number of unread notifications.

Response:

```json
{
  "data": {
    "unread_count": 3
  }
}
```

### `POST /api/v1/notifications/mark-all-read`

Marks all current user notifications as read.

### `POST /api/v1/notifications/{notification}/read`

Marks one notification as read.

Laravel only allows the owner of the notification to mark it as read.

### `POST /api/v1/notifications/register-token`

Stores the phone FCM token.

Flutter sends:

```json
{
  "token": "phone-fcm-token-here",
  "device_type": "android"
}
```

Allowed `device_type` values:

- `android`
- `ios`

## Staff API Routes

Staff routes are under:

`/api/v1/staff`

They need login and staff permissions.

These routes are for staff tools, scanning, recording donations, inventory, and reports. They are not normal donor app screens unless the mobile app later adds a staff mode.

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

`POST /api/v1/staff/donors/scan`

Scans the donor card QR payload and returns donor details.

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

When a completed donation is recorded, Laravel:

- Checks donor eligibility.
- Creates the donation.
- Marks the appointment completed if there is one.
- Updates donor profile totals.
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

`GET /api/v1/staff/inventory-adjustments`

Returns inventory adjustment records.

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

`POST /api/v1/staff/inventory/adjust`

Manually adjusts inventory.

Permission needed:

- `inventory.manage`

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

