# NBTS API and Flutter contract

Last verified: 2026-08-11

## Contract ownership

Laravel owns the `/api/v1` contract. The canonical mobile client is the standalone repository at:

```text
/home/kevin/Desktop/MAIN /PROJECTS/NBTS/nbts-mobile
```

`docs/workflow.md` defines the business workflow and compatibility fields. This file is the operator/developer endpoint index. Any API change must update the relevant Form Request, Resource, Pest contract test, Flutter repository/model test, this file, `docs/workflow.md`, `docs/task.md`, and verified evidence in `docs/achievement.md` in the same change.

The Laravel repository is the source of truth for endpoint behavior. Flutter implementation is owned separately: the mobile developer should implement against this file and report any requested contract change back to Laravel instead of silently working around it in the client.

## Request conventions

- Base path: `/api/v1`.
- JSON clients send `Accept: application/json`.
- Language is selected with `X-Locale: en` or `X-Locale: sw`; stable state/error codes do not change with translation.
- Protected routes use `Authorization: Bearer <sanctum-token>`.
- Donor tokens are named per device, expire after the configured period (30 days by default), and carry `donor:read` and `donor:write` abilities.
- Authentication, ownership, active-account checks, and token abilities are enforced server-side. Firebase proves identity but does not replace Laravel authorization.
- List endpoints use bounded pagination. Resource lists use a `data` collection plus Laravel pagination links/meta; notification lists also return the authoritative unread count.
- Validation failures use HTTP 422. Missing or non-owned resources use 404 where revealing existence would cross an ownership boundary. Unauthenticated and unauthorized requests use 401/403 as appropriate.
- Authentication throttles are 5 requests/minute for password registration and login, and 10 requests/minute for Firebase login. A throttled client receives HTTP 429 and should honor `Retry-After` rather than retrying immediately.
- Dates use `YYYY-MM-DD`. Timestamps use ISO 8601, including an offset, and must be parsed as instants by the client.
- Boolean query values may be sent as `true`/`false`, `1`/`0`, or another Laravel-supported boolean representation.
- Compatibility aliases are intentionally duplicated in v1. New client code should prefer the canonical field while continuing to tolerate its documented alias.

Do not log or place access tokens, Firebase ID tokens, FCM registration tokens, service-account JSON, password material, passkey credentials, or signed donor-card payloads in documentation.

## Mobile developer quick start

1. Configure the environment-specific origin outside source code and append `/api/v1`. Production and staging must use HTTPS. For a local Android emulator, `10.0.2.2` points to the host only when Laravel is listening on an emulator-reachable interface.
2. Send `Accept: application/json` and `X-Locale: en|sw` on every request.
3. After password or Firebase authentication, store only the returned Sanctum token in platform-secure storage and send it as `Authorization: Bearer <token>`.
4. Treat HTTP 401 as an expired/revoked session, HTTP 403 as an authenticated ability/role denial, HTTP 404 as unavailable or non-owned data, HTTP 422 as field validation, and HTTP 429 as throttling.
5. Register the current FCM token after authentication and whenever Firebase rotates it. On logout, unregister the FCM token first, then revoke the Sanctum token.
6. Prefer canonical v1 keys such as `blood_center_id`, `starts_at`, `body`, `points`, and `tier`; accept the documented aliases while old clients remain supported.
7. Do not automatically retry appointment writes, profile updates, or notification mutations. Safe GET requests may use a short bounded retry for network failures only.

## Response envelopes

Authentication endpoints return token data and an unwrapped `user`:

```json
{
  "token_type": "Bearer",
  "token": "<sanctum-token>",
  "expires_at": "2026-09-10T14:30:00+03:00",
  "user": {
    "id": 42,
    "name": "Asha Donor",
    "roles": ["donor"]
  }
}
```

Single-resource endpoints return `{ "data": { ... } }`. A successful empty response, such as logout or device-token removal, returns HTTP 204 with no body. Paginated endpoints return Laravel's stable collection envelope:

```json
{
  "data": [],
  "links": {
    "first": "...page=1",
    "last": "...page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": null,
    "last_page": 1,
    "per_page": 20,
    "to": null,
    "total": 0
  }
}
```

Validation errors use field-keyed arrays. The translated message can change with locale; client logic must use the HTTP status and field key, not compare message text:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "scheduled_at": ["The selected appointment slot is unavailable."]
  }
}
```

## Public discovery endpoints

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/blood-centers` | Active center directory with validated search/city/service filters. |
| `GET` | `/blood-centers/{center}` | Active center detail. |
| `GET` | `/blood-centers/{center}/available-slots` | Validated future appointment slots and capacity. |
| `GET` | `/campaigns` | Current/upcoming campaigns with validated filters and emergency-first ordering. |
| `GET` | `/campaigns/{campaign}` | Publicly visible campaign detail. |
| `GET` | `/articles` | Published article directory. |
| `GET` | `/articles/{article}` | Published article detail. |
| `GET` | `/publications` | Published articles with approved attachments, projected as publications. |
| `GET` | `/publications/{article}` | Published attachment/document detail. |
| `GET` | `/schedules` | Visible campaign/center schedule projection. |
| `GET` | `/schedules/{campaign}` | Visible schedule detail. |

Inactive centers, ended/cancelled campaigns, drafts, archived articles, future publications, and records tied to inactive centers are not public.

### Public list filters

| Endpoint | Query parameters |
| --- | --- |
| `/blood-centers` | `q`, exact `city`, exact service value in `service`, `per_page` (1–50; default 20). |
| `/blood-centers/{center}/available-slots` | Required `date=YYYY-MM-DD`, from today through the configured booking window (90 days by default). |
| `/campaigns` and `/schedules` | `q`, `status=upcoming|ongoing`, `type=standard|emergency`, `blood_group`, `center_id`, `per_page` (1–50; default 20). |
| `/articles` and `/publications` | `q`, exact `category`, `featured=true|false`, `per_page` (1–50; default 20). |

Blood-group codes are `A+`, `A-`, `B+`, `B-`, `AB+`, `AB-`, `O+`, and `O-`. Discovery detail endpoints return 404 when the record exists but is not currently public.

## Donor authentication and account

| Method | Path | Ability | Purpose |
| --- | --- | --- | --- |
| `POST` | `/auth/register` | Public, throttled | Create a donor password account and profile. |
| `POST` | `/auth/login` | Public, throttled | Donor email-or-phone/password login. |
| `POST` | `/auth/firebase` | Public, throttled | Verify a Firebase ID token and safely link/create a donor. |
| `POST` | `/auth/logout` and `/logout` | Authenticated | Revoke the presented token; `/logout` is a v1 compatibility alias. |
| `GET` | `/me` and `/user` | `donor:read` | Current donor resource; `/user` is a v1 compatibility alias. |
| `GET` | `/profile` | `donor:read` | Current donor profile. |
| `PUT` | `/profile` | `donor:write` | Update allowed profile/preferences fields. |
| `POST` | `/profile/photo` | `donor:write` | Validate and replace a raster profile photo. |

Staff/inactive accounts receive the same safe failure as invalid mobile credentials. A Firebase token may auto-link by email only when the trusted token claim confirms a verified email; staff accounts cannot be auto-linked through the donor app.

### Authentication request bodies

`POST /auth/register` returns HTTP 201:

| Field | Requirement |
| --- | --- |
| `name` | Required string, maximum 255 characters. |
| `email` | Optional valid email, unique when supplied; normalized to lowercase. |
| `phone` | Required unique string, maximum 30 characters. |
| `password` | Required and must satisfy the active Laravel password policy. |
| `password_confirmation` | Required to match `password`. |
| `blood_group` | Required stable blood-group code. This starts as donor-selected, not staff-verified. |
| `gender` | Required: `male`, `female`, or `other`. |
| `region` | Required string, maximum 255 characters. |
| `date_of_birth` | Required `YYYY-MM-DD`, not in the future. |
| `device_name` | Optional compatibility field; defaults to `NBTS Mobile`, maximum 100 characters. |

`POST /auth/login` accepts `identifier` plus `password` and optional `device_name`. `identifier` may be the donor email or phone. Legacy clients may send `email` or `phone` instead of `identifier`; Laravel normalizes those aliases.

`POST /auth/firebase` accepts `firebase_id_token` and optional `device_name`. The ID token is verified server-side, including revocation. Never send a Firebase access token, authorization code, provider password, or raw provider credential in this field.

### User/profile response contract

The `user` object returned by authentication and the `data` object returned by `/me`, `/user`, and `/profile` expose:

- Identity/profile: `id`, `name`, nullable `email`, nullable `phone`, `blood_group`, `gender`, `date_of_birth`, `region`, `address`, `profile_photo_url`, `photo_url`, `locale`, `language`, and `profile_complete`.
- Donor compatibility: `donor_id`, `preferred_center_id`, `preferred_center`, emergency-contact fields, notification preferences, `loyalty_tier`, `loyalty_points`, `total_donations`, authoritative completed-donation `total_volume_ml`, `next_eligible_date`, and `share_anonymized_data`.
- Authorization/detail: `roles` when loaded and a nested `donor_profile` containing blood-group confidence, eligibility, loyalty, contact, consent, language, and preferred-center data.

`total_volume_ml` sums only completed donation records; failed attempts are excluded. `share_anonymized_data` controls leaderboard inclusion and must never be inferred from push or email consent.

### Profile update request

`PUT /profile` is a partial update. Send only changed fields:

| Fields | Rules |
| --- | --- |
| `name`, `phone`, `blood_group`, `gender`, `date_of_birth`, `region` | Required when present; use the same formats as registration. `phone` remains unique. |
| `address` | Nullable string, maximum 1000 characters. |
| `preferred_center_id` | Nullable ID of an active blood center. |
| `emergency_contact_name`, `emergency_contact_phone` | Nullable strings, maximum 255 and 30 characters. |
| `push_notifications_enabled`, `email_notifications_enabled`, `sms_reminders_enabled` | Boolean channel consent. |
| `share_anonymized_data` | Boolean, independent opt-in for privacy-safe leaderboard visibility. |
| `language` | `en` or `sw`; legacy `English` and `Swahili` values are normalized. |

A donor cannot replace a staff-verified blood group. `POST /profile/photo` uses `multipart/form-data` with field `photo`; it accepts a raster image up to 5 MB and no larger than 3000×3000 pixels.

## Donor journey endpoints

| Method | Path | Ability | Purpose |
| --- | --- | --- | --- |
| `GET` | `/donor-card` | `donor:read` | Stable donor identity, aggregate stats, and short-lived signed QR payload. |
| `GET` | `/eligibility` | `donor:read` | Read-only eligibility guidance; never a clinical decision. |
| `GET` | `/donations` | `donor:read` | Owner-scoped donation history. |
| `GET` | `/donations/summary` | `donor:read` | Calculated completed-donation totals. |
| `GET` | `/loyalty` | `donor:read` | Current donor points, tier, rank, badges, and rewards. |
| `GET` | `/leaderboard` | `donor:read` | Privacy-safe opted-in leaderboard. |
| `GET` | `/appointments` | `donor:read` | Owner-scoped appointment history. |
| `GET` | `/appointments/upcoming` | `donor:read` | Next eligible appointment projection. |
| `GET` | `/appointments/{appointment}` | `donor:read` | Owner-scoped detail. |
| `POST` | `/appointments` | `donor:write` | Book against active center/slot capacity. |
| `PUT` | `/appointments/{appointment}` | `donor:write` | Reschedule an eligible appointment. |
| `POST` | `/appointments/{appointment}/cancel` | `donor:write` | Cancel an eligible appointment. |

`/appointments` and `/donations` accept `per_page` from 1–50 and default to 20. `/leaderboard` accepts `period=all_time` and `per_page` from 1–50, defaulting to 20.

### Appointment write bodies

Create an appointment with `POST /appointments`:

```json
{
  "blood_center_id": 3,
  "scheduled_at": "2026-08-20T09:30:00+03:00",
  "notes": "Optional donor note"
}
```

`center_id` is accepted as a legacy alias for `blood_center_id`. The time must be in the future, inside the configured booking window, and exactly match a returned slot. A donor may hold only one active `pending` or `confirmed` appointment. Rescheduling with `PUT /appointments/{appointment}` requires a new `scheduled_at`, optionally a new center, and returns the appointment to `pending`. Cancellation has no request body.

### Donor summary response fields

- Donor card: `donor_id`, short-lived `qr_payload`, `qr_expires_at`, top-level identity aliases, nested `donor`, and `stats`. The QR is opaque client data and must never be decoded as an authorization decision.
- Eligibility: stable `status`, translated `status_label`, `eligible`, `message`, `reasons`, `next_eligible_donation_date`, alias `next_eligible_date`, `last_eligibility_checked_at`, and `clinical_screening_required`.
- Donation: canonical IDs, center, appointment, donation type, blood group, verification flag, volume, date aliases, and stable status.
- Donation summary: completed `total_donations`, `total_volume_ml`, `total_volume_liters`, `last_donation`, and explicitly estimated `lives_touched` fields.
- Loyalty: `points`/`loyalty_points`, `tier`/`loyalty_tier`, `total_donations`, nullable `rank`, and ordered `badges` and `rewards` arrays.
- Leaderboard entry: `id`, `period`, `rank`, anonymous `display_name`, `donation_count`, `loyalty_tier`, and `is_current_user`. Pagination metadata additionally contains `current_user_rank` and `period`.

Leaderboard responses never contain donor names, emails, phone numbers, donor IDs, Firebase IDs, or FCM tokens. Only profiles with `share_anonymized_data=true` appear in the collection; the authenticated donor may still receive their own rank in metadata.

## Notifications and devices

| Method | Path | Ability | Purpose |
| --- | --- | --- | --- |
| `GET` | `/notifications` | `donor:read` | Owner-scoped inbox with unread/type filters. |
| `GET` | `/notifications/unread-count` | `donor:read` | Dashboard badge count. |
| `POST` | `/notifications/{notification}/read` | `donor:write` | Mark one owned notification read. |
| `POST` | `/notifications/mark-all-read` | `donor:write` | Mark the current donor's inbox read. |
| `DELETE` | `/notifications/{notification}` | `donor:write` | Delete one owned notification. |
| `POST` | `/notifications/register-token` | `donor:write` | Register/reassign a validated Android/iOS FCM token. |
| `DELETE` | `/notifications/device-token` | `donor:write` | Idempotently unregister an owned device token. |

Raw FCM tokens are never written to audit metadata; audit evidence stores only platform and a SHA-256 fingerprint.

### Notification filters and mutations

`GET /notifications` accepts `unread=true|false`, a notification `type` up to 50 characters, and `per_page` from 1–50. Its pagination metadata includes authoritative `unread_count`. Notification objects expose `id`, `title`, `body`, compatibility alias `message`, `type`, nullable `action_url`, structured `data`, `read`, `read_at`, `sent_at`, and `created_at`.

Register a device token with:

```json
{
  "token": "<fcm-registration-token>",
  "device_type": "android"
}
```

`device_type` is `android` or `ios` and defaults to `android`; token length is 20–255 characters. Registration returns only the database ID, platform, and `registered=true`, never the raw token. `DELETE /notifications/device-token` accepts `{ "token": "..." }` and is idempotent for missing or foreign tokens.

## Firebase backend configuration

- Keep the Firebase project identity `nbts-d567e`. In Laravel, `FIREBASE_PROJECT=app` selects the configured project alias; the external service-account JSON referenced by `FIREBASE_CREDENTIALS` or `GOOGLE_APPLICATION_CREDENTIALS` must belong to `nbts-d567e`.
- Keep the service-account JSON outside Git and outside the web root. Never paste it into `.env`, documentation, tests, issue trackers, or mobile assets; configure only its absolute server path.
- `PUSH_TRANSPORT=log` is the construction-safe default. Use `PUSH_TRANSPORT=fcm` only after approved credentials and a real test device are available.
- Android is registered as package `com.nbts.mobile`. iOS remains unsupported until an approved bundle ID and `GoogleService-Info.plist` exist.
- Laravel verifies Firebase ID tokens for authentication and uses FCM HTTP v1 for push. Mobile Firebase configuration identifies the app, but it never grants Laravel permissions by itself.

## Compatibility and versioning

The exact v1 compatibility aliases required by the current Flutter models are listed in `docs/workflow.md`. They may be deprecated only through a coordinated, versioned migration with mobile parser tests. Fields must never disappear silently from v1.

Current intentional projections:

- Publications reuse approved article attachment fields until a dedicated editorial workflow justifies an additive table.
- Schedules reuse visible campaign dates and active center location fields until a dedicated scheduling workflow is approved.

## Mobile implementation rules and API suggestions

- Keep one API client responsible for base URL, JSON headers, locale, bearer token, timeout, and normalized error parsing.
- Keep repositories grouped by server capability: authentication/profile, discovery, appointments, donor history/card/eligibility, loyalty/leaderboard, and notifications/devices.
- Decode canonical fields first and aliases second. For example, prefer `blood_center_id` over `center_id`, `body` over `message`, `points` over `loyalty_points`, and `tier` over `loyalty_tier` when both are present.
- Preserve unknown JSON fields and enum codes safely. Display labels may be translated, but app decisions must use stable codes.
- Refresh the user resource after changing profile consent so leaderboard and notification UI reflects the server value.
- Request available slots before booking or rescheduling and send the exact returned `scheduled_at`; do not create times locally.
- Render `data: null` from `/appointments/upcoming` as “no upcoming appointment,” not as an error.
- Use `meta.unread_count` or `/notifications/unread-count` as authoritative. Do not infer unread totals from the currently loaded page.
- Treat `action_url` and remote media URLs as untrusted input: allow only approved schemes/hosts and require user intent before opening an external destination.
- Never cache donor-card QR payloads beyond `qr_expires_at`. Refresh the card when expired and never use the QR as a bearer credential.
- Do not claim successful push delivery merely because FCM token registration succeeded; device receipt remains a separate acceptance test.

## Verification commands

Laravel contract checks:

```bash
php artisan route:list --path=api/v1 --except-vendor -vv
php artisan test --compact tests/Feature/MobilePasswordAuthenticationTest.php
php artisan test --compact tests/Feature/FirebaseAuthenticationTest.php
php artisan test --compact tests/Feature/MobileProfileTest.php
php artisan test --compact tests/Feature/DonorAppointmentApiTest.php
php artisan test --compact tests/Feature/DonorJourneyApiTest.php
php artisan test --compact tests/Feature/MobileLoyaltyApiTest.php
php artisan test --compact tests/Feature/PublicContentApiTest.php
php artisan test --compact tests/Feature/NotificationApiTest.php
php artisan test --compact tests/Feature/FcmPushTransportTest.php
vendor/bin/phpstan analyse --no-progress
vendor/bin/pint --dirty --format agent
```

Canonical Flutter checks, once a compatible SDK is installed:

```bash
cd '/home/kevin/Desktop/MAIN /PROJECTS/NBTS/nbts-mobile'
dart format --output=none --set-exit-if-changed .
flutter analyze
flutter test
```

Live Firebase verification requires a fresh service-account credential stored outside Git. Android uses Firebase project `nbts-d567e` and package `com.nbts.mobile`. iOS support is not claimed until a valid `GoogleService-Info.plist` and device verification exist. The Flutter owner should attach analyzer/test/device evidence to the handoff; Laravel Phase 3 must remain open until the external device and push-delivery gates pass.
