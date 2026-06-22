# National Blood Transfusion Service - Mobile API v1

This documentation is for integration between the Flutter mobile application and the NBTS backend.

## Development Environment (Local Network)
During development, both the backend and mobile apps are connected via Ethernet/Wi-Fi on the same network.

### Backend Server Command
```bash
php artisan serve --host=0.0.0.0 --port=8003
```

### Base URL Configuration
The mobile app should use the backend machine's local IP address. 
**Example:** `http://192.168.0.196:8003/api/v1`

---

## Connectivity Verification
Before implementing auth, verify the connection using the ping endpoint.

### Ping API
- **URL**: `/ping`
- **Method**: `GET`
- **Expected Response**:
  ```json
  { "status": "API working" }
  ```

---

## Authentication
NBTS uses **Laravel Sanctum** token-based authentication.

### Demo Accounts
Seed the local database with `php artisan db:seed`, then use these credentials:

| Purpose | Email | Phone | Password |
| --- | --- | --- | --- |
| Admin panel / super admin API user | `admin@nbts.test` | `+255700000001` | `Password123!` |
| Center manager staff API user | `manager@nbts.test` | `+255700000002` | `Password123!` |
| Center staff API user | `staff@nbts.test` | `+255700000003` | `Password123!` |
| Donor mobile API user | `donor@nbts.test` | `+255700000101` | `Password123!` |

Admin panel URL: `/admin`

### Header Requirement
Include this header for all protected routes:
`Authorization: Bearer {token}`

### Auth Endpoints

#### Register Donor
- **URL**: `/api/v1/auth/register`
- **Method**: `POST`
- **Required fields**: `name`, `email`, `phone`, `password`, `password_confirmation`, `blood_group`, `gender`, `region`, `date_of_birth`
- **Accepted values**:
  - `blood_group`: `A+`, `A-`, `B+`, `B-`, `AB+`, `AB-`, `O+`, `O-`
  - `gender`: `Male`, `Female`, `Other`
  - `password`: minimum 8 characters and must match `password_confirmation`
- **Payload**:
  ```json
  {
    "name": "John Doe",
    "phone": "+255712345678",
    "email": "john@example.com",
    "blood_group": "B+",
    "gender": "Male",
    "region": "Dar es Salaam",
    "date_of_birth": "1995-12-01",
    "password": "secret_password",
    "password_confirmation": "secret_password"
  }
  ```
- **Success Response (201)**:
  ```json
  {
    "token": "bearer_token_string",
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "email": "john@example.com",
      "blood_group": "B+",
      "gender": "Male",
      "region": "Dar es Salaam",
      "date_of_birth": "1995-12-01",
      "last_donation": null
    }
  }
  ```

#### Login Donor
- **URL**: `/api/v1/auth/login`
- **Method**: `POST`
- **Required fields**: `identifier`, `password`
- **Payload**:
  ```json
  {
    "identifier": "+255712345678",
    "password": "secret_password"
  }
  ```
  *Note: `identifier` can be either the User's Phone or Email.*

#### Logout
- **URL**: `/api/v1/auth/logout`
- **Method**: `POST`
- **Auth**: Required
- **Headers**: `Authorization: Bearer {token}`
- **Success Response (200)**:
  ```json
  {
    "token": "bearer_token_string",
    "user": { ... }
  }
  ```

---

## Donor Profile

#### Get Profile
- **URL**: `/profile`
- **Method**: `GET`
- **Auth**: Required

#### Update Profile
- **URL**: `/profile`
- **Method**: `PUT`
- **Auth**: Required
- **Body**: `name, phone, blood_group, gender, date_of_birth, region, address` (all optional)

#### Donor Card
- **URL**: `/donor-card`
- **Method**: `GET`
- **Auth**: Required
- **Returns**: donor ID, QR payload, donor identity fields, eligibility status, and donation stats.

#### Eligibility
- **URL**: `/eligibility`
- **Method**: `GET`
- **Auth**: Required
- **Returns**: eligibility status, boolean eligibility result, reasons, and next eligible donation date.

#### Loyalty
- **URL**: `/loyalty`
- **Method**: `GET`
- **Auth**: Required
- **Returns**: donor donation stats, earned badges, and earned rewards.

#### Leaderboard
- **URL**: `/leaderboard`
- **Method**: `GET`
- **Auth**: Required
- **Returns**: all-time donor leaderboard.

---

## Blood Centers

#### List Centers
- **URL**: `/blood-centers`
- **Method**: `GET`
- **Auth**: Not required

#### Center Details
- **URL**: `/blood-centers/{id}`
- **Method**: `GET`
- **Auth**: Not required

---

## Appointments

#### List My Appointments
- **URL**: `/appointments`
- **Method**: `GET`
- **Auth**: Required

#### List My Upcoming Appointments
- **URL**: `/appointments/upcoming`
- **Method**: `GET`
- **Auth**: Required

#### Book Appointment
- **URL**: `/appointments`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "blood_center_id": 1,
      "scheduled_at": "2026-04-20 10:00:00",
      "notes": "First time donor"
  }
  ```

#### Cancel My Appointment
- **URL**: `/appointments/{id}/cancel`
- **Method**: `POST`
- **Auth**: Required

---

## Donations

#### My Donation History
- **URL**: `/donations`
- **Method**: `GET`
- **Auth**: Required

---

## Staff Operations

Staff endpoints require Sanctum auth and matching staff/admin permissions.

#### Search Donors
- **URL**: `/staff/donors/search`
- **Method**: `GET`
- **Auth**: Required
- **Query**: `query`, optional `type` of `any`, `donor_id`, `qr`, `phone`, `name`, or `email`

#### Confirm Appointment
- **URL**: `/staff/appointments/{appointment}/confirm`
- **Method**: `POST`
- **Auth**: Required

#### Cancel Appointment
- **URL**: `/staff/appointments/{appointment}/cancel`
- **Method**: `POST`
- **Auth**: Required

#### Record Donation
- **URL**: `/staff/donations`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "user_id": 1,
      "blood_center_id": 1,
      "appointment_id": 1,
      "donation_type": "appointment",
      "blood_group": "B+",
      "blood_group_verified": true,
      "volume_ml": 450,
      "donation_date": "2026-06-12",
      "status": "completed",
      "notes": "Successful donation"
  }
  ```
  For walk-ins, use `"donation_type": "walk_in"` and omit `appointment_id`.

#### Verify Blood Group
- **URL**: `/staff/donations/{donation}/verify-blood-group`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "blood_group": "O+"
  }
  ```

#### Staff Eligibility Check
- **URL**: `/staff/donors/{donor}/eligibility-check`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "weight_kg": 65,
      "answers": {
          "feeling_well": true
      },
      "notes": "Cleared for donation"
  }
  ```

#### Defer Donor
- **URL**: `/staff/donors/{donor}/deferrals`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "type": "temporary",
      "reason": "Low hemoglobin",
      "starts_at": "2026-06-13",
      "ends_at": "2026-07-13",
      "notes": "Review after one month"
  }
  ```

#### Lift Deferral
- **URL**: `/staff/deferrals/{deferral}/lift`
- **Method**: `POST`
- **Auth**: Required

---

## Inventory and Reports

Staff inventory endpoints require Sanctum auth and inventory permissions.

#### List Inventory
- **URL**: `/staff/inventory`
- **Method**: `GET`
- **Auth**: Required

#### List Blood Units
- **URL**: `/staff/blood-units`
- **Method**: `GET`
- **Auth**: Required

#### Transition Blood Unit
- **URL**: `/staff/blood-units/{unit}/transition`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "status": "used",
      "notes": "Issued to hospital"
  }
  ```

#### Manual Inventory Adjustment
- **URL**: `/staff/inventory/adjust`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "blood_center_id": 1,
      "blood_group": "O+",
      "quantity_delta": -1,
      "reason": "manual_correction",
      "notes": "Stock count correction"
  }
  ```

#### Expire Due Units
- **URL**: `/staff/inventory/expire-due`
- **Method**: `POST`
- **Auth**: Required

#### Low Stock Alerts
- **URL**: `/staff/low-stock-alerts`
- **Method**: `GET`
- **Auth**: Required

#### Create Emergency Campaign
- **URL**: `/staff/low-stock-alerts/{alert}/emergency-campaign`
- **Method**: `POST`
- **Auth**: Required

#### Summary Report
- **URL**: `/staff/reports/summary`
- **Method**: `GET`
- **Auth**: Required

#### Donation Report
- **URL**: `/staff/reports/donations`
- **Method**: `GET`
- **Auth**: Required

#### Inventory Report
- **URL**: `/staff/reports/inventory`
- **Method**: `GET`
- **Auth**: Required

---

## Campaigns

#### Active Campaigns
- **URL**: `/campaigns`
- **Method**: `GET`
- **Auth**: Not required

#### Campaign Details
- **URL**: `/campaigns/{id}`
- **Method**: `GET`
- **Auth**: Not required

---

## Device Notifications

#### Register Push Token
- **URL**: `/notifications/register-token`
- **Method**: `POST`
- **Auth**: Required
- **Payload**:
  ```json
  {
      "token": "fcm_device_token_here",
      "device_type": "android"
  }
  ```
- **Response**: `200 OK`
