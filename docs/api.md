# NBTS API and Flutter contract

Last verified: 2026-08-01

## Contract ownership

Laravel owns the `/api/v1` contract. The canonical mobile client is the standalone repository at:

```text
/home/kevin/Desktop/MAIN /PROJECTS/NBTS/nbts-mobile
```

`docs/workflow.md` defines the business workflow and compatibility fields. This file is the operator/developer endpoint index. Any API change must update the relevant Form Request, Resource, Pest contract test, Flutter repository/model test, this file, `docs/workflow.md`, `docs/task.md`, and verified evidence in `docs/achievement.md` in the same change.

## Request conventions

- Base path: `/api/v1`.
- JSON clients send `Accept: application/json`.
- Language is selected with `X-Locale: en` or `X-Locale: sw`; stable state/error codes do not change with translation.
- Protected routes use `Authorization: Bearer <sanctum-token>`.
- Donor tokens are named per device, expire after the configured period (30 days by default), and carry `donor:read` and `donor:write` abilities.
- Authentication, ownership, active-account checks, and token abilities are enforced server-side. Firebase proves identity but does not replace Laravel authorization.
- List endpoints use bounded pagination. Resource lists use a `data` collection plus Laravel pagination links/meta; notification lists also return the authoritative unread count.
- Validation failures use HTTP 422. Missing or non-owned resources use 404 where revealing existence would cross an ownership boundary. Unauthenticated and unauthorized requests use 401/403 as appropriate.

Do not log or place access tokens, Firebase ID tokens, FCM registration tokens, service-account JSON, password material, passkey credentials, or signed donor-card payloads in documentation.

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

## Donor authentication and account

| Method | Path | Ability | Purpose |
| --- | --- | --- | --- |
| `POST` | `/auth/register` | Public, throttled | Create a donor password account and profile. |
| `POST` | `/auth/login` | Public, throttled | Donor email-or-phone/password login. |
| `POST` | `/auth/firebase` | Public, throttled | Verify a Firebase ID token and safely link/create a donor. |
| `POST` | `/auth/logout` | Authenticated | Revoke the presented token. |
| `GET` | `/me` and `/user` | `donor:read` | Current donor resource; `/user` is a v1 compatibility alias. |
| `GET` | `/profile` | `donor:read` | Current donor profile. |
| `PUT` | `/profile` | `donor:write` | Update allowed profile/preferences fields. |
| `POST` | `/profile/photo` | `donor:write` | Validate and replace a raster profile photo. |

Staff/inactive accounts receive the same safe failure as invalid mobile credentials. A Firebase token may auto-link by email only when the trusted token claim confirms a verified email; staff accounts cannot be auto-linked through the donor app.

## Donor journey endpoints

| Method | Path | Ability | Purpose |
| --- | --- | --- | --- |
| `GET` | `/donor-card` | `donor:read` | Stable donor identity, aggregate stats, and short-lived signed QR payload. |
| `GET` | `/eligibility` | `donor:read` | Read-only eligibility guidance; never a clinical decision. |
| `GET` | `/donations` | `donor:read` | Owner-scoped donation history. |
| `GET` | `/donations/summary` | `donor:read` | Calculated completed-donation totals. |
| `GET` | `/appointments` | `donor:read` | Owner-scoped appointment history. |
| `GET` | `/appointments/upcoming` | `donor:read` | Next eligible appointment projection. |
| `GET` | `/appointments/{appointment}` | `donor:read` | Owner-scoped detail. |
| `POST` | `/appointments` | `donor:write` | Book against active center/slot capacity. |
| `PUT` | `/appointments/{appointment}` | `donor:write` | Reschedule an eligible appointment. |
| `POST` | `/appointments/{appointment}/cancel` | `donor:write` | Cancel an eligible appointment. |

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

## Compatibility and versioning

The exact v1 compatibility aliases required by the current Flutter models are listed in `docs/workflow.md`. They may be deprecated only through a coordinated, versioned migration with mobile parser tests. Fields must never disappear silently from v1.

Current intentional projections:

- Publications reuse approved article attachment fields until a dedicated editorial workflow justifies an additive table.
- Schedules reuse visible campaign dates and active center location fields until a dedicated scheduling workflow is approved.

## Verification commands

Laravel contract checks:

```bash
php artisan route:list --path=api/v1 --except-vendor -vv
php artisan test --compact tests/Feature/MobilePasswordAuthenticationTest.php
php artisan test --compact tests/Feature/FirebaseAuthenticationTest.php
php artisan test --compact tests/Feature/DonorAppointmentApiTest.php
php artisan test --compact tests/Feature/DonorJourneyApiTest.php
php artisan test --compact tests/Feature/PublicContentApiTest.php
php artisan test --compact tests/Feature/NotificationApiTest.php
```

Canonical Flutter checks, once a compatible SDK is installed:

```bash
cd '/home/kevin/Desktop/MAIN /PROJECTS/NBTS/nbts-mobile'
dart format --output=none --set-exit-if-changed .
flutter analyze
flutter test
```

Live Firebase verification requires a fresh service-account credential stored outside Git. Android uses Firebase project `nbts-d567e` and package `com.nbts.mobile`. iOS support is not claimed until a valid `GoogleService-Info.plist` and device verification exist.
