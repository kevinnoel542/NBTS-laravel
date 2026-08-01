# NBTS system workflow

Last updated: 2026-08-01

## Purpose

This document is the target operating model for NBTS-NEW. It merges the useful behavior from the previous NBTS workspace with the new Laravel 13, Fortify, Livewire 4, Flux UI, Tailwind CSS 4, Pest 5, Firebase, and Flutter foundation.

Legacy sources reconciled:

- `PROJECT_OVERVIEW.md`
- `MAIN_WORKFLOWS.md`
- `DATA_AND_DATABASE.md`
- `ADMIN_PANEL_PAGES.md`
- `PUBLIC_WEBSITE_PAGES.md`
- `MOBILE_APP_API.md`
- `MOBILE_BACKEND_SYNC_CHECKLIST.md`
- `KNOWN_IMPLEMENTATION_NOTES.md`
- `Blood Donation Platform — System Requirements.md`
- The previous public-site task tracker and Flutter achievement log.

Where a legacy document and the current direction differ, this document is the target. Existing API field names remain compatibility requirements until both Laravel and Flutter are migrated together.

## System boundaries

### Public website

The website provides trusted NBTS information, centers, campaigns, schedules, news, publications, impact data, customer service, and verified mobile-app links. Visitors do not need an account.

### Donor mobile application

Donors use Flutter to register, authenticate, manage their profile, hold a digital donor card, check eligibility, find centers and campaigns, book appointments, view donation history and loyalty, and receive notifications.

### Staff and administrator web account

Center staff, center managers, NBTS administrators, and super administrators use a Fortify-secured Livewire/Flux account. Navigation follows the operational workflow and hides unauthorized modules. Staff can switch between English and Kiswahili.

### Laravel backend

Laravel owns authentication, authorization, API contracts, business transactions, audit logging, queues, scheduling, reporting, document generation, notification orchestration, and data integrity.

## Roles and operating scope

| Role | Primary responsibility | Data scope |
| --- | --- | --- |
| Public visitor | Learn, locate services, read approved content, contact NBTS | Published public data |
| Donor | Manage personal donor journey through Flutter | Own records and public data |
| Center staff | Reception, screening, donation and blood operations | Assigned active centers |
| Center manager | Supervise center operations, stock, staff and center reports | Assigned active centers |
| NBTS admin | National operations, campaigns, content, analytics and governance | National operational data |
| Super admin | Security, configuration, recovery and full administration | Entire system |

Permissions, not role names, decide whether a page, action, metric, or export is available. Center assignments further restrict record scope.

## Language workflow

- Supported account languages are English (`en`) and Kiswahili (`sw`).
- A user selects a language from authentication/public navigation or account settings.
- The preference persists on the user record and session.
- Navigation, validation, actions, messages, reports, and PDF labels follow the selected language.
- Stored state codes remain stable English machine values; only display labels are translated.
- Managed public content can have English and Kiswahili variants with an explicit fallback when one translation is awaiting publication.
- Audit data records stable action codes so changing locale never changes evidence.

## Identity and access workflows

### Staff/admin password login

1. The user opens the staff sign-in page.
2. Fortify validates the normalized email and password under rate limiting.
3. The system rejects inactive users.
4. If two-factor authentication is enabled, Fortify presents the challenge.
5. The system creates a secure web session.
6. Authorization resolves roles, permissions, and active center assignments.
7. The user enters the first permitted workflow queue, normally Overview.
8. The audit trail records successful and failed security-relevant events without storing secrets.

### Passkey login

1. The browser requests passkey options for the configured relying party and origin.
2. The user completes the platform authenticator challenge.
3. Fortify validates the credential and counter.
4. The inactive-user and authorization checks still apply.
5. The credential's last-used time and security event are recorded.

### Mobile password login

1. The donor submits email or phone and password to `/api/v1/auth/login`.
2. Laravel validates credentials and active status.
3. Sanctum issues a named, revocable mobile token.
4. The API returns a stable `UserResource` representation.
5. Logout revokes the current token.

### Mobile Firebase login

1. Flutter authenticates with an enabled Firebase provider.
2. Flutter sends the Firebase ID token to `/api/v1/auth/firebase`.
3. Laravel validates signature, issuer, audience, subject, activation time, and expiry against Firebase project `nbts-d567e`.
4. Laravel links by Firebase UID or a safely verified email, preventing UID conflicts.
5. Laravel creates the donor profile only when needed.
6. Sanctum returns the NBTS API token; Firebase never replaces Laravel authorization.

## End-to-end donor journey

1. A donor downloads the verified Flutter application.
2. The donor registers with password credentials or authenticates through Firebase.
3. Laravel creates the user, donor role, donor profile, language preference, and unique donor ID.
4. The app displays an expiring digital donor-card QR payload.
5. The donor reviews eligibility guidance and current personal eligibility.
6. The donor finds an active blood center, campaign, or collection schedule.
7. The donor books an available appointment or later arrives as a walk-in.
8. The center receives the donor through QR, donor ID, phone, email, or name search.
9. Staff confirms identity and profile details.
10. Staff performs and records the health/eligibility screening.
11. If eligible, staff records the collection and later confirms the blood group and unit status.
12. The transaction updates the appointment, donation history, next eligibility, loyalty, blood unit, inventory, alerts, audit trail, and notifications.
13. The donor sees updated history and receives appropriate in-app/push/SMS/email communication based on consent and configuration.

## Reception and donor identification

1. Staff opens Donor reception.
2. Staff scans an expiring signed QR payload or searches by donor ID, phone, email, or name.
3. The system shows a concise donor summary: identity, photo, preferred center, blood-group confidence, eligibility state, deferrals, last donation, next eligible date, and today's appointment.
4. Staff confirms the correct donor before opening sensitive details.
5. New walk-in donors can be registered under permission and duplicate-detection controls.
6. Access and profile changes are audited.

## Appointment workflow

### Donor booking

1. The donor chooses an active center and available time.
2. The service checks capacity, conflicting active appointments, center status, lead time, and scheduling rules.
3. The appointment starts as `pending` unless center policy confirms automatically.
4. Laravel creates in-app notification history and dispatches configured channels.

### Staff processing

1. Staff views today's or pending queue for an assigned center.
2. Staff confirms, reschedules, cancels, or checks in the donor with a reason where required.
3. A checked-in appointment enters the eligibility queue.
4. Completion occurs through donation recording, not a disconnected status edit.

### Appointment states

`pending → confirmed → checked_in → completed`

Alternative terminal paths:

- `pending|confirmed → rescheduled`
- `pending|confirmed → cancelled`
- `pending|confirmed → no_show`

Legacy records without the expanded states remain readable until an additive migration and compatibility mapping are deployed.

## Eligibility and deferral workflow

1. Staff starts screening from the donor or checked-in appointment.
2. The system calculates age and displays recent donation/deferral history.
3. Staff records weight, hemoglobin where required, health answers, observations, and notes.
4. The eligibility service evaluates configurable rules:
   - Minimum and maximum age.
   - Minimum weight.
   - Required donation interval, including approved gender-specific intervals.
   - Active temporary or permanent deferrals.
   - Other approved clinical answers.
5. Staff makes the final safety decision.
6. Laravel stores an immutable screening record and updates the current donor eligibility summary.
7. A temporary deferral includes an end date when known; a permanent deferral has no automatic expiry.
8. Lifting a deferral requires authorization, reason, actor, and reevaluation.

Eligibility codes:

- `eligible`
- `not_yet_eligible`
- `temporarily_deferred`
- `permanently_deferred`

## Donation recording transaction

1. Staff opens an eligible donor from the workflow queue.
2. Laravel authorizes the actor and center.
3. Staff chooses appointment or walk-in and records collection details.
4. Laravel rechecks eligibility at the moment of completion.
5. One database transaction:
   - Creates the donation.
   - Links and completes the appointment when applicable.
   - Updates donor and donor-profile history.
   - Calculates the next eligible date.
   - Creates one blood unit with a unique unit number and expiry date.
   - Applies the initial inventory effect appropriate to the initial unit status.
   - Awards deterministic loyalty milestones.
   - Writes audit events/outbox records.
6. Notifications and other remote effects run after commit through queued work.
7. Repeating the same request cannot create a second donation or blood unit.

## Blood-group verification

Blood-group confidence moves through:

`unknown → user_selected → staff_verified`

1. Donor-provided blood group is informational.
2. Authorized staff records laboratory-confirmed ABO/Rh result.
3. Laravel stores verifier and verification time.
4. Donation, donor, and donor-profile fields remain consistent.
5. Changing an already verified blood group is high risk: it requires reason, elevated permission, confirmation, and audit before/after values.

## Blood-unit lifecycle and inventory

### Unit lifecycle

`collected → testing → available → reserved → used`

Additional controlled outcomes:

- `testing → rejected → discarded`
- `available|reserved → transferred`
- `collected|testing|available|reserved → expired → discarded`

### Inventory effects

- Inventory is grouped by blood center and blood group.
- Only statuses defined as available stock contribute to `available_units`.
- A status transition applies its inventory delta exactly once.
- Transfers create traceable source and destination effects.
- Manual adjustments cannot make inventory negative and require a reason.
- Every automatic or manual delta creates an inventory-adjustment record and audit event.
- Reconciliation reports compare blood-unit states with aggregate inventory.

### Expiry and disposal

1. The scheduler identifies due units in eligible nonterminal states.
2. A controlled service marks them expired and removes any counted availability.
3. Staff sees an expiry/disposal queue.
4. Authorized staff confirms disposal with method, time, notes, and actor.
5. Alerts are reevaluated after each stock effect.

## Low-stock and emergency response

1. Every inventory effect compares available units with the center/blood-group threshold.
2. A shortage opens or updates one active low-stock alert.
3. Center managers and permitted national staff are notified.
4. Staff may create one linked emergency campaign; duplicate requests return the existing campaign.
5. Donor targeting selects only active, eligible, consented donors using approved geographic rules.
6. Push, SMS, and email jobs are queued, deduplicated, retried, and logged.
7. When stock recovers to the configured threshold, the alert resolves while retaining history.

Alert states:

`open → notified → campaign_created → resolved`

## Loyalty workflow

1. Only a successfully completed donation changes loyalty totals.
2. The service recalculates total donations, volume, points, and tier.
3. Active badge and reward thresholds are evaluated idempotently.
4. Newly earned items are recorded once and trigger donor notification.
5. Reward redemption requires explicit state, actor, time, and audit.
6. Leaderboards use an approved period and privacy display rules.

## Notification workflow

1. Domain events request a notification after the originating transaction commits.
2. Laravel stores the in-app notification record first.
3. Channel selection respects donor preferences, consent, verified contact details, urgency, and system configuration.
4. FCM uses a service account and HTTP v1; invalid tokens are retired safely.
5. SMS reminders use configured providers and idempotency keys.
6. Email uses queued Laravel notifications/mail.
7. Every attempt records channel, status, provider identifier, retry information, and safe error details.

Primary triggers include appointment creation/confirmation/reminders/cancellation, eligibility changes, donation completion, badge/reward awards, low stock, emergency campaigns, and campaigns near eligible donors.

## Public content workflow

1. Authorized content staff create English and/or Kiswahili drafts.
2. Required fields, media, source attribution, and document uploads are validated.
3. A reviewer approves and publishes or schedules the item.
4. Only published, currently visible records appear publicly or in the donor API.
5. Archive/unpublish actions preserve history and are audited.

Managed content includes articles, publications, campaigns, centers, schedules, regional contacts, FAQs, leadership/governance information, public metrics, feedback categories, privacy, and terms.

## Public-page information architecture

- Home: mission, urgent actions, current campaigns, centers, trusted impact, and donor journey.
- About: mandate, mission, vision, governance, leadership, and national network.
- Donate: reasons, process, preparation, aftercare, whole blood, and apheresis.
- Services: recruitment/collection, laboratory testing, components, storage/supply, clinical guidance, and quality management.
- Eligibility: public guidance, deferrals, intervals, and staff-decision disclaimer.
- Centers: backend-driven search, filters, schedules, contacts, maps, and details.
- Campaigns: current/upcoming/emergency filters and details.
- News and publications: searchable approved records and accessible downloads.
- Impact: approved dated statistics and operational analytics safe for public release.
- Customer service: contact, feedback, complaints, charter, and response expectations.
- Download app: verified stores, package identity, QR link, supported platforms, and help.
- Legal: privacy, terms, cookies where applicable, and data-protection information.

Public facts and contact details must come from approved backend records or a verified NBTS source. Old or unclear statistics must show their period and cannot be presented as current.

## Staff account navigation flow

Navigation follows the work rather than database table names:

1. **Overview** — urgent queues, today's activity, center context, and shortcuts.
2. **Donor reception** — scan/search, register, profile, donor card.
3. **Appointments** — pending, today, upcoming, check-in, exceptions.
4. **Eligibility** — screening queue, history, deferrals.
5. **Donations** — record, verify blood group, history.
6. **Blood operations** — testing queue, units, inventory, transfers, expiry, disposal.
7. **Response** — low-stock alerts, campaigns, targeted communication.
8. **Engagement** — notifications, loyalty, rewards, leaderboard.
9. **Content** — articles, publications, FAQs, schedules, regional contacts, public metrics.
10. **Intelligence** — operational reports, analytics, PDFs, exports.
11. **Administration** — users, roles, permissions, centers, audit, backup/recovery, settings.
12. **Account** — profile, language, appearance, password, 2FA, passkeys, sessions.

Dashboard numbers always link to the queue that explains or resolves them.

## Audit workflow

Sensitive actions record:

- Stable action code.
- Actor and impersonator when applicable.
- Subject type and identifier.
- Blood-center context.
- Request/correlation identifier.
- Safe before and after values.
- Reason for high-risk changes.
- IP address and user agent.
- Timestamp.

Ordinary application roles cannot update or delete audit records. Secret values, password material, passkey credentials, access tokens, Firebase tokens, and service-account data are redacted.

## Disaster-recovery workflow

1. Scheduled jobs create encrypted database and required media/document backups.
2. Backups are copied to an approved off-site store under retention policy.
3. Verification jobs confirm existence, size, age, checksum/readability, and alert on failure.
4. A restore never targets the live database during testing.
5. Authorized operators restore into an isolated environment, run migrations/checks, compare critical counts, and execute smoke tests.
6. The result, operator, timing, recovery point, and exceptions are recorded.
7. Production recovery follows an approved runbook with communication, write freeze, restore, verification, controlled reopening, and post-incident review.

## PDF and export workflow

1. The user requests a document from an authorized record or report.
2. Laravel authorizes record and center scope.
3. The service builds a versioned data snapshot so the document is reproducible.
4. The selected locale controls labels while identifiers and codes remain stable.
5. Laravel renders a branded accessible PDF with issue time, source period, document identifier, and verification context where appropriate.
6. Generation/download is audited for sensitive documents.
7. Large reports run through queues and notify the requester when ready.

## Mobile API compatibility contract

The target API prefix is `/api/v1`. Required capability groups:

- Authentication: register, login, Firebase login, logout.
- User/profile: current user, profile, profile update, profile photo.
- Discovery: campaigns, articles, publications, centers, available slots.
- Donor: donor card, eligibility, loyalty, leaderboard, donation history and summary.
- Appointments: list, upcoming, create, reschedule/update, cancel.
- Notifications: list, unread count, register token, mark all read, read one, delete one.
- Staff: donor search, screening, deferrals, appointment operations, donation recording, blood-group verification, unit/inventory operations, alerts, campaigns, and reports.

Compatibility fields currently required by Flutter include:

- User: `id`, `name`, `email`, `phone`, `blood_group`, `gender`, `region`, `date_of_birth`, `donor_id`, `preferred_center`, `loyalty_tier`, `loyalty_points`, `total_donations`, `total_volume_ml`, `next_eligible_date`.
- Campaign: `id`, `title`, `summary`, `description`, `category`, `type`, `blood_group`, `blood_type`, `starts_at`, `start_date`, `ends_at`, `end_date`, `urgent`.
- Center: `id`, `name`, `address`, `phone`, `phone_number`, `opening_hours`, `hours`, `wait_time`, `capacity_label`, `services`, `is_open`.
- Appointment: `id`, `scheduled_at`, `blood_center_id`, `center_id`, `center_name`, `status`, `notes`.
- Donation: `id`, `donation_date`, `donated_at`, `blood_group`, `blood_type`, `volume_ml`, `status`, `donation_type`.
- Donor card: `donor_id`, `qr_payload`, `qr_expires_at`, `donor`, `stats`.
- Notification: `id`, `title`, `body`, `message`, `type`, `read`, `read_at`, `sent_at`, `created_at`.

Aliases can remain during transition but should be normalized in a future versioned API, never silently removed from v1.

## Reports and operational intelligence

Reports must be filterable by authorized center, period, blood group, campaign, status, and other relevant dimensions. Required outputs include:

- Donor registrations, active/eligible donors, return rate, and deferrals.
- Appointments, attendance, cancellation, no-show, and conversion to donation.
- Donations by period, center, blood group, type, and status.
- Blood units by lifecycle state, expiry risk, transfer, rejection, and disposal.
- Inventory by center/blood group and reconciliation exceptions.
- Low-stock duration, emergency response, and campaign outcomes.
- Notification delivery and engagement.
- Loyalty milestones and reward redemption.
- Content publishing and customer-service performance.
- Audit/security and backup/recovery status.

Every displayed metric must link to its definition and source period. Exported totals must reconcile with the filtered source records.

## Core invariants

- A donor has at most one donor profile and one stable donor ID.
- A completed donation creates at most one blood unit.
- The same appointment cannot produce multiple completed donations.
- A unit transition affects inventory at most once.
- Inventory cannot be negative.
- Center-scoped users cannot read or mutate records outside assigned centers.
- Donor-provided blood group is never presented as staff verified.
- Inactive users cannot authenticate or continue privileged work.
- Remote notifications never run before the originating transaction commits.
- Audit evidence never stores authentication secrets or full private tokens.
- Public content is not visible before publication.
- API v1 fields are not removed without a coordinated versioned migration.

## Known legacy corrections carried forward

- Use `blood_centers.phone`; `phone_number` is a temporary API compatibility alias.
- Campaign states remain `upcoming`, `ongoing`, `completed`, and `cancelled`; `is_active` is derived rather than a separate state.
- Firebase token verification requires the configured project ID.
- FCM HTTP v1 uses a service-account credential; the legacy server key is not required.
- The old test suite did not prove the core business rules; all migrated behavior requires new Pest coverage.
- The old donor interval used a single 90-day rule. The target uses approved configurable policy, including gender-specific intervals when confirmed.
- Legacy documentation sometimes says Filament is the account boundary. The target staff interface is Livewire/Flux unless a later explicit decision retains Filament for a bounded administrative module.

## Completion evidence

Implementation status is tracked in `docs/task.md`. Proven completed work is recorded in `docs/achievement.md`. A workflow is complete only when its domain tests, authorization tests, UI/API tests, operational evidence, and relevant browser/device checks pass.

