# Mobile Backend Sync Checklist

This file is for keeping Laravel and the Flutter app aligned.

Use it before changing API fields, routes, login, notifications, donor cards, appointments, or campaigns.

## Mobile Project Checked

Mobile folder:

`nbts-mobile`

Important mobile files checked:

- `lib/core/api/api_config.dart`
- `lib/core/api/api_client.dart`
- `lib/core/data/repositories/auth_repository.dart`
- `lib/core/data/repositories/profile_repository.dart`
- `lib/core/data/repositories/appointments_repository.dart`
- `lib/core/data/repositories/campaigns_repository.dart`
- `lib/core/data/repositories/centers_repository.dart`
- `lib/core/data/repositories/donations_repository.dart`
- `lib/core/data/repositories/donor_card_repository.dart`
- `lib/core/data/repositories/eligibility_repository.dart`
- `lib/core/data/repositories/notifications_repository.dart`
- `lib/core/data/models/*.dart`
- `doc/achievement.md`

## What Laravel Must Keep Working

These donor mobile routes must stay available:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/firebase`
- `POST /api/v1/auth/logout`
- `GET /api/v1/user`
- `GET /api/v1/profile`
- `PUT /api/v1/profile`
- `GET /api/v1/campaigns`
- `GET /api/v1/articles`
- `GET /api/v1/blood-centers`
- `GET /api/v1/blood-centers/{bloodCenter}/available-slots`
- `GET /api/v1/donor-card`
- `GET /api/v1/eligibility`
- `GET /api/v1/loyalty`
- `GET /api/v1/leaderboard`
- `GET /api/v1/donations`
- `GET /api/v1/donations/summary`
- `GET /api/v1/appointments`
- `GET /api/v1/appointments/upcoming`
- `POST /api/v1/appointments`
- `PUT /api/v1/appointments/{id}`
- `POST /api/v1/appointments/{id}/cancel`
- `GET /api/v1/notifications`
- `GET /api/v1/notifications/unread-count`
- `POST /api/v1/notifications/register-token`
- `POST /api/v1/notifications/mark-all-read`
- `POST /api/v1/notifications/{notification}/read`

## Firebase Login Status

Mobile expects:

`POST /api/v1/auth/firebase`

Laravel now has this route.

Laravel stores:

- `users.firebase_uid`
- `users.firebase_provider`

Laravel needs this in `.env`:

```env
FIREBASE_PROJECT_ID=nbts-d567e
```

If this value is missing, Firebase login will fail even if Google login succeeds on the phone.

## Push Notification Status

Mobile registers the phone token using:

`POST /api/v1/notifications/register-token`

Laravel stores the token in:

`f_c_m_tokens`

Laravel stores in-app messages in:

`user_notifications`

Laravel sends Firebase push messages through:

`App\Services\NotificationService`

For real Firebase push sending, `.env` needs:

```env
FIREBASE_NOTIFICATIONS_ENABLED=true
FIREBASE_PROJECT_ID=nbts-d567e
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-service-account.json
```

## Field Names To Keep Stable

The Flutter app is flexible, but these names should not be removed from Laravel responses.

User/profile:

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

Campaign:

- `id`
- `title`
- `summary`
- `description`
- `category`
- `type`
- `blood_group`
- `blood_type`
- `starts_at`
- `start_date`
- `ends_at`
- `end_date`
- `urgent`

Blood center:

- `id`
- `name`
- `address`
- `phone`
- `phone_number`
- `opening_hours`
- `hours`
- `wait_time`
- `capacity_label`
- `services`
- `is_open`

Appointment:

- `id`
- `scheduled_at`
- `blood_center_id`
- `center_id`
- `center_name`
- `status`
- `notes`

Donation:

- `id`
- `donation_date`
- `donated_at`
- `blood_group`
- `blood_type`
- `volume_ml`
- `status`
- `donation_type`

Donor card:

- `donor_id`
- `qr_payload`
- `qr_expires_at`
- `donor`
- `stats`

Notification:

- `id`
- `title`
- `body`
- `message`
- `type`
- `read`
- `read_at`
- `sent_at`
- `created_at`

## Before Changing A Mobile API

Check these Laravel places:

- Route in `routes/api.php`
- Controller in `app/Http/Controllers/Api`
- Resource in `app/Http/Resources`
- Model fillable fields in `app/Models`
- Migration columns in `database/migrations`
- Seeder data in `database/seeders`
- Docs in `docs/MOBILE_APP_API.md`

Check these Flutter places:

- Repository in `nbts-mobile/lib/core/data/repositories`
- Model in `nbts-mobile/lib/core/data/models`
- Screen that displays the data
- `nbts-mobile/doc/achievement.md`

## Testing Commands

Run inside Laravel:

```bash
php artisan route:list --path=api/v1
php artisan migrate:status
php artisan test
php artisan optimize:clear
```

Test the API server:

```bash
php artisan serve --host=0.0.0.0 --port=8003
```

Then the phone should use:

`http://YOUR_COMPUTER_IP:8003/api/v1`

## Common Problems

### Phone Says Cannot Reach Server

Check:

- The computer and phone are on the same Wi-Fi.
- Laravel is served with `--host=0.0.0.0`.
- The mobile base URL uses the computer IP.
- The mobile base URL includes the port if Laravel uses a port.
- Firewall allows the port.

### Google Login Works But App Login Fails

Check:

- `POST /api/v1/auth/firebase` exists.
- `.env` has `FIREBASE_PROJECT_ID=nbts-d567e`.
- Laravel can reach Google's Firebase signing certificate URL.
- Flutter is sending `firebase_id_token`.

### Notifications Do Not Arrive

Check:

- Phone token is stored in `f_c_m_tokens`.
- User has `push_notifications_enabled=true`.
- `FIREBASE_NOTIFICATIONS_ENABLED=true`.
- Service account file exists at `storage/app/firebase/firebase-service-account.json`.
- Laravel queue/log has no Firebase send error.

