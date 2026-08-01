# NBTS achievement log

Last updated: 2026-08-01

## Evidence rule

An achievement belongs here only when the feature exists in NBTS-NEW and has direct verification evidence. Planning, legacy implementation, copied code, or an unexecuted test does not count as completed work.

Each entry must record:

- Date.
- Feature or milestone.
- Web, mobile, backend, or operations scope.
- Main files or modules changed.
- Database/API impact.
- Automated tests and commands that passed.
- Browser/device verification when the feature has a user interface.
- Known limitations and the next dependent task.

## 2026-08-01 — Modernization control documents

Status: completed

Scope: project planning and architecture documentation.

Delivered:

- Created the living implementation checklist in `docs/task.md`.
- Created this evidence-based achievement log.
- Created `docs/workflow.md` by reconciling the previous NBTS project overview, database model, public website pages, admin pages, mobile/backend synchronization checklist, system requirements, and main workflows with the Laravel 13/Fortify/Livewire direction.
- Added explicit workstreams for bilingual accounts, audit trails, disaster recovery, PDF outputs, public content, mobile integration, and 1600×900 browser QA.

Verified evidence:

- The three documentation files exist in NBTS-NEW.
- The workflow includes public, donor-mobile, center staff, center manager, NBTS admin, and super-admin responsibilities.
- The task plan contains implementation and verification gates for web, mobile, backend, security, operations, and release.

Database/API impact: none.

Known limitations:

- No legacy feature is claimed as implemented in NBTS-NEW merely because it exists in the old workspace or shared database.
- Database backup/restore proof and migration reconciliation are the next foundation gate.

## 2026-08-01 — Database safety and reproducible foundation

Status: completed

Scope: backend and operations foundation.

Delivered:

- Imported all 23 legacy migrations using their deployed filenames and exact contents.
- Reconciled the three conflicting Laravel base migrations with the definitions that created the existing NBTS schema.
- Installed Laravel Sanctum 4.3.3, Spatie Laravel Permission 7.4.2, Spatie Laravel Backup 10.3.1, Laravel DOMPDF 3.1.2, and Endroid QR Code 6.1.3.
- Published permission, backup, and PDF configuration.
- Created the dedicated `nbts_new_test` MySQL test database.
- Created a healthy private local database-only backup archive of the shared `nbts` database.
- Restored the archive into an isolated temporary database and verified it before removing the temporary database and extracted SQL.
- Created the persistent `nbts_new_dev` clone and pointed NBTS-NEW's local environment to it.
- Applied the passkey and Fortify two-factor migrations only to `nbts_new_dev`.

Database/API impact:

- Shared `nbts`: no schema or record changes; it remains at 23 migrations.
- Development `nbts_new_dev`: existing records preserved, `passkeys` added, and three nullable two-factor columns added to `users`; it is at 25 migrations.
- Test `nbts_new_test`: full schema built from zero using all 25 migrations.
- API routes are not changed yet.

Automated verification:

- All 23 imported migration files match the legacy migration files byte for byte.
- `php -l` passed for all 25 migration files.
- Clean MySQL migration plus `php artisan test --compact`: 33 tests passed with 81 assertions.
- Development and clean-test schemas have zero differences across tables, columns, indexes, and foreign keys.
- Backup restore comparison: 36 tables in source and restore with no missing table names.
- Source/restore counts matched: 11 users, 7 donor profiles, 7 donations, 4 blood centers, 10 appointments, and 23 migration records.
- Source/development data counts remained equal after the additive security migrations.
- Composer reported no known security vulnerability advisories after installation.
- `git diff --check` passed.

Browser/device verification:

- Not applicable to this backend/operations milestone.

Known limitations:

- The backup currently proves private local development backup/restore; off-site encrypted production storage and retention remain pending.
- Sanctum and Spatie Permission are installed but are not yet connected to the reconciled `User` model.
- Public and account user interfaces have not yet been redesigned.

Next dependent task:

- Reconcile the Fortify `User` model with existing NBTS fields, roles, permissions, center assignments, and mobile token authentication.

## 2026-08-01 — Identity, access, and bilingual account foundation

Status: completed

Scope: backend, staff web authentication, localization, and test infrastructure.

Delivered:

- Reconciled the Laravel 13 `User` model with the deployed NBTS profile, security, role, Firebase, and mobile fields while preserving existing password hashes and remember tokens.
- Connected Sanctum API tokens, Spatie roles, Fortify two-factor authentication, passkeys, email verification, and locale preference to the same user model.
- Restricted Fortify web authentication and passkey login to active staff-role accounts; donor accounts remain outside staff/admin web access.
- Disabled public web registration and removed the obsolete registration prompt from the staff login page.
- Added active-account and staff-account middleware, including deactivation enforcement during an active session or two-factor challenge.
- Added English/Kiswahili locale middleware, a persistent locale endpoint, user locale storage, and Swahili authentication messages.
- Added realistic donor, staff, manager, NBTS admin, super-admin, inactive, unverified, and two-factor user factory states.
- Added a committed MySQL schema snapshot so clean test databases load the verified 36-table schema without replaying every historical migration.
- Added Pint exclusions that keep all 23 deployed legacy migrations byte-for-byte immutable.

Main implementation:

- `app/Models/User.php`
- `app/Providers/FortifyServiceProvider.php`
- `app/Http/Middleware/EnsureAccountIsActive.php`
- `app/Http/Middleware/EnsureStaffAccountAccess.php`
- `app/Http/Middleware/SetLocale.php`
- `app/Http/Controllers/LocaleController.php`
- `database/migrations/2026_08_01_092943_add_locale_to_users_table.php`
- `database/migrations/2026_08_01_092944_backfill_user_locales_from_donor_profiles.php`
- `database/schema/mysql-schema.sql`
- `pint.json`

Database/API impact:

- Shared `nbts` remains unchanged at 23 migrations.
- Development `nbts_new_dev` is at 27 migrations; all existing record counts are preserved and `users.locale` was added with legacy donor language backfill.
- Verified development counts: 11 users, 7 donor profiles, 7 donations, and 10 appointments.
- Test setup contains schema and migration history only; it contains no donor or operational records.
- Added `POST /locale/{locale}` for supported `en` and `sw` locale preferences.

Automated verification:

- `vendor/bin/pint --dirty --format agent`: passed.
- Legacy migration comparison: all 23 imported files match the previous workspace byte for byte after formatting.
- Focused identity/access/localization suite: 39 tests, 38 passed before the obsolete registration-link defect was fixed; the only failure was the removed route referenced by the login view.
- Authentication rerun after the fix: 8 tests passed with 21 assertions.
- `php artisan test --compact`: 41 tests passed with 112 assertions.
- `npm run build`: production Vite build passed.
- `git diff --check`: passed.
- Clean test startup improved from about 298 seconds to about 73 seconds after adding the MySQL schema snapshot.

Browser/device verification:

- Pending the premium account-shell implementation and required 1600×900 browser QA phase.

Known limitations:

- Existing cloned staff credentials have not yet been exercised interactively because no plaintext credentials are stored in the repository.
- Record-level permissions, center scoping, complete English/Kiswahili interface copy, and mobile Firebase token exchange remain pending.

Next dependent task:

- Port the reusable domain models and relationships, define canonical permissions and center scope, and build the versioned mobile authentication contract.

## 2026-08-01 — Canonical roles, permissions, and center scope

Status: completed

Scope: backend authorization and operational tenancy foundation.

Delivered:

- Replaced policy authority based on the coarse legacy `users.role` value with canonical backed role and permission names.
- Added an idempotent role/permission seeder covering 28 operational, content, reporting, audit, backup, and settings permissions.
- Preserved the legacy role column for transition reads while assigning users without canonical roles to the safest matching role.
- Added `BloodCenter` and `CenterStaff` models, factories, typed relationships, active scopes, and reusable staff-visible center queries.
- Added active center-assignment checks for center managers and staff, with national scope for NBTS and super administrators.
- Added a blood-center policy that separates public active-center information from center-scoped operational access and national center management.
- Added a super-admin gate bypass while making inactive status a denial inside Spatie's permission resolution boundary.
- Removed the starter database seeder's hard-coded test account; the default seeder now applies only the safe role/permission matrix.

Database/API impact:

- `nbts_new_dev` now contains 28 permissions and the canonical role matrix.
- Existing data remains intact: 11 users, 12 user-role assignments, 7 donor profiles, and 2 active center-staff assignments.
- Role permissions: super admin 28, NBTS admin 24, center manager 15, center staff 7, donor 0.
- No schema changes and no API contract changes in this milestone.

Automated verification:

- Center authorization suite: 5 tests passed with 25 assertions.
- Full suite: 46 tests passed with 137 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed.

Browser/device verification:

- Not applicable; account navigation and operational screens are not yet implemented.

Known limitations:

- Center scope is enforced for blood-center operational access, but appointment, donor, donation, inventory, campaign, report, and content policies are still pending.
- The existing legacy role column remains present until all consumers are migrated and a later reviewed migration can remove it safely.

Next dependent task:

- Port the core transactional models and factories, then extend the same permission and center-scope rules across appointments, eligibility, donations, and inventory.

## 2026-08-01 — Core transactional model graph

Status: completed

Scope: backend domain foundation.

Delivered:

- Ported and modernized donor profile, appointment, eligibility record, deferral, donation, blood unit, blood inventory, inventory adjustment, and low-stock alert models.
- Added typed Eloquent relationships across users, donors, centers, staff handlers, appointments, donations, blood units, and inventory adjustments.
- Added realistic factories for the full donor-to-inventory record chain.
- Added backed enums matching every deployed database code for blood groups, blood-group verification, eligibility, appointment status, donation type/status, deferral type, blood-unit status, and low-stock alert status.
- Added explicit appointment and blood-unit transition rules while keeping translated labels outside domain values.
- Added reusable active-center scopes for appointments, donations, blood units, and inventory.
- Added effective-deferral, stock-gap, stock-status, alert-severity, and inventory-adjustment direction helpers using stable codes.

Database/API impact:

- No schema or data mutations were required.
- Existing `nbts_new_dev` records hydrate successfully through the new enum casts and relationships.
- Real clone compatibility proved for 10 appointments, 7 donations, 7 donor profiles, 4 eligibility records, 7 blood units, 32 inventory rows, and 1 low-stock alert.
- API routes remain pending; these backed enum values are the stable codes the future API will expose.

Automated verification:

- Core domain model suite: 4 tests passed with 23 assertions.
- Full suite: 50 tests passed with 160 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- Read-only application-context hydration against `nbts_new_dev`: passed for every existing enum value and tested relationship.
- `git diff --check`: passed.

Browser/device verification:

- Not applicable; operational actions and screens are the next layer.

Known limitations:

- Model transition helpers do not mutate records; transactional workflow actions, locking, audit events, and policies still need implementation.
- Campaign, content, loyalty, notification, Firebase-token, leaderboard, reward, and SMS log models remain to be ported as their workflows are implemented.

Next dependent task:

- Implement policy-protected appointment transitions and transactional donation completion with eligibility, blood-unit, inventory, and audit invariants.

## 2026-08-01 — Audited appointment transitions

Status: completed

Scope: backend workflow, authorization, and audit foundation.

Delivered:

- Added an appointment policy for donor ownership, staff permissions, and active center assignment.
- Added a transactional appointment transition action using row-level `FOR UPDATE` locking and deadlock retries.
- Enforced explicit pending → confirmed/cancelled and confirmed → completed/cancelled transitions.
- Added handler attribution, confirmation/cancellation timestamps, optional staff notes, and complete rollback on invalid or unauthorized transitions.
- Added native append-only audit records with actor, center, polymorphic subject, request context, metadata, timestamp, previous hash, and HMAC record hash.
- Added application-level update/delete prevention for audit models and chained hashes for tamper evidence.
- Added the audit relationship to users and blood centers.

Database/API impact:

- Added the `audit_logs` table to `nbts_new_dev`; the clone is now at 28 migrations.
- Shared `nbts` remains unchanged at 23 migrations.
- Audit table includes nullable actor/center foreign keys, subject/action/time indexes, and a unique record hash.
- Existing development records remain intact: 11 users and 10 appointments; no synthetic audit records were added to the clone.
- Refreshed the clean MySQL schema snapshot to include the audit structure.

Automated verification:

- Appointment workflow suite: 5 tests passed with 23 assertions.
- Full suite: 55 tests passed with 183 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed.

Browser/device verification:

- Pending implementation of the staff appointment workspace.

Known limitations:

- Appointment rescheduling, slot capacity, donor cancellation windows, and check-in are not yet implemented.
- Model events protect normal Eloquent writes; direct database administrators remain capable of altering rows, with hash-chain verification intended to reveal tampering.
- Audit verification/reporting UI and scheduled integrity checks remain pending.

Next dependent task:

- Implement transactional donation completion, official configurable eligibility intervals, blood-unit creation, and later laboratory release into inventory.

## 2026-08-01 — Transactional donation completion

Status: completed

Scope: backend donation, eligibility, blood unit, authorization, and audit workflow.

Delivered:

- Added a donation policy that combines canonical permissions with active center assignment and donor ownership.
- Added typed donation input data and a retryable transactional recording action for both confirmed appointments and walk-ins.
- Required an active donor account, completed donor profile, eligible profile state, elapsed donation interval, no active deferral, and an eligible same-day screening record.
- Required staff blood-group verification and recorded the verifier and verification time.
- Applied current NBTS Tanzania whole-blood intervals: three months for men and four months for women, with a conservative four-month fallback for other or missing gender values.
- Updated donor blood group, last donation date, profile eligibility state, next eligible date, and completed-donation count.
- Completed a matching confirmed appointment inside the same transaction.
- Created exactly one deterministic blood-unit number per donation with initial `collected` status and configurable shelf life.
- Kept available inventory unchanged until the future laboratory release transition marks a unit `available`.
- Added a chained `donations.completed` audit record containing only operational metadata.
- Added database uniqueness guarantees for one donation per non-null appointment and one blood unit per donation.

Database/API impact:

- `nbts_new_dev` is now at 29 migrations; shared `nbts` remains unchanged at 23.
- Added unique indexes on `donations.appointment_id` and `blood_units.donation_id` after confirming no deployed duplicates.
- Existing counts remain unchanged: 11 users, 7 donor profiles, 10 appointments, 7 donations, and 7 blood units.
- Added `config/nbts.php` for reviewed interval policy and configurable whole-blood shelf life.
- API endpoints and request idempotency keys remain pending.

Automated verification:

- Donation workflow suite: 5 tests passed with 25 assertions.
- Full suite: 60 tests passed with 208 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- Clean MySQL schema snapshot refreshed and both new unique indexes verified.
- `git diff --check`: passed.

Browser/device verification:

- Pending implementation of donor lookup, screening, and staff donation workspaces.

Known limitations:

- The same-day screening record is modeled and enforced, but the staff screening/deferral action and UI are not yet implemented.
- Whole-blood shelf life defaults to the legacy 35-day rule and remains operator-configurable pending final component/storage SOP review.
- Loyalty awards, notifications, API idempotency keys, and laboratory inventory-release transitions remain pending.

Next dependent task:

- Implement audited blood-unit transitions and exactly-once center/blood-group inventory adjustments, including low-stock alert resolution.

## 2026-08-01 — Audited blood-unit and inventory transitions

Status: completed

Scope: backend laboratory, inventory, low-stock, authorization, and audit workflow.

Delivered:

- Added a blood-unit policy requiring inventory permission plus active center assignment.
- Added a retryable transactional blood-unit transition action with row locks on both the unit and center/blood-group inventory row.
- Enforced the explicit blood-unit transition graph and rejected repeated or terminal-state reversals.
- Calculated available and reserved quantity deltas from old/new unit states instead of accepting caller-supplied inventory numbers.
- Prevented either available or reserved inventory from falling below zero.
- Added reserved quantity deltas to adjustment history so reservations, releases, and consumption reconcile independently.
- Opened or updated low-stock alerts below threshold and resolved them when available stock recovered.
- Added one chained audit record per successful unit transition with status, deltas, balances, and alert reference.
- Ensured failed authorization, invalid transitions, and inconsistent negative balances write no unit, inventory, adjustment, alert, or audit changes.

Database/API impact:

- Added `inventory_adjustments.reserved_quantity_delta` with a zero default; `nbts_new_dev` is now at 30 migrations.
- Existing development data is unchanged: 7 blood units, 32 inventory rows, 4 historical adjustments, and 1 low-stock alert.
- Historical adjustment records safely received a zero reserved delta.
- Shared `nbts` remains unchanged at 23 migrations.
- Refreshed the clean MySQL schema snapshot.

Automated verification:

- Inventory workflow suite: 4 tests passed with 23 assertions.
- Full suite: 64 tests passed with 231 assertions.
- Reconciliation assertions prove adjustment sums match final available and reserved balances.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed.

Browser/device verification:

- Pending implementation of laboratory queues, blood-unit detail, and inventory workspaces.

Known limitations:

- Manual adjustments, transfers between centers, expiry sweeps, disposal confirmation, and low-stock notifications remain pending.
- Parallel-process contention is protected by row locks and unique keys but still needs a dedicated concurrency test environment.
- Campaign generation from alerts remains pending.

Next dependent task:

- Build the versioned mobile API/authentication contract and the first staff Livewire workflow surfaces on top of the verified domain actions.

## 2026-08-01 — Secure Firebase mobile authentication foundation

Status: completed

Scope: backend and mobile API foundation.

Delivered:

- Established versioned `/api/v1` routing with localized API middleware and explicit Sanctum ability aliases.
- Installed the Laravel 13/PHP 8.4-compatible Laravel Firebase bridge 7.2.1 and Firebase PHP Admin SDK 8.3.0.
- Added a Firebase verifier boundary that asks the Admin SDK to verify ID-token revocation and maps only trusted token claims into an immutable identity object.
- Added Firebase donor onboarding and authentication with row locks, verified-email requirements for first linking, UID/email conflict protection, inactive-account denial, and staff-account auto-link denial.
- Created the canonical donor role/profile and stable donor ID only when absent, without overwriting existing donor names or profiles.
- Issued named 30-day Sanctum tokens with `donor:read` and `donor:write` abilities and replaced stale tokens for the same device name.
- Added protected current-user and bearer-token logout endpoints; logout revokes only the presented token.
- Added a stable API Resource with domain state codes plus English/Kiswahili authentication errors selected through `X-Locale` or `Accept-Language`.
- Added redacted audit events for Firebase account creation and authentication; Firebase tokens, UIDs, emails, and service credentials are not written to audit metadata.
- Identified the standalone `NBTS/nbts-mobile` Git repository as the canonical Flutter source and rejected the older divergent `NBTS/database/nbts-mobile` duplicate.
- Confirmed the existing Android identity uses Firebase project `nbts-d567e` and package `com.nbts.mobile` without copying the old private service-account key.
- Ignored backend service-account JSON locations and documented fresh credential generation as an external Firebase Console step.

Database/API impact:

- No schema or domain-data changes were required; the existing Firebase UID/provider and Sanctum token tables are reused.
- Added `POST /api/v1/auth/firebase`, `GET /api/v1/me`, and `POST /api/v1/logout`.
- Firebase login accepts `firebase_id_token` and `device_name`, returning `token_type`, `token`, `expires_at`, and `user`.
- The current-user endpoint requires the `donor:read` token ability; mobile tokens expire after 30 days by default.
- No Firebase service-account JSON or `.env` secret is tracked by Git.

Automated verification:

- Firebase authentication feature suite: 9 tests passed with 59 assertions.
- Kreait adapter unit suite: 2 tests passed with 9 assertions.
- Full suite: 75 tests passed with 299 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- `composer audit --no-interaction`: no security vulnerability advisories found.
- Secret-path tracking and ignore checks: passed.

Browser/device verification:

- Not yet applicable to this server-only milestone; Flutter migration and emulator/device testing remain pending.

Known limitations:

- A fresh Firebase backend credential must be generated/rotated by an authorized Firebase Console operator and supplied through `FIREBASE_CREDENTIALS` or Google Application Default Credentials.
- Password registration/login, profile mutation, donor card, appointments, content, notifications, and FCM token APIs remain pending.
- Android client configuration is identified but not yet migrated into NBTS-NEW; iOS Firebase configuration was not present and must not be claimed as supported.
- Live Firebase verification has not been exercised because no private credential is stored in this workspace; the adapter and HTTP behavior are tested with deterministic fakes/mocks.

Next dependent task:

- Complete the donor-facing API contract (password authentication, profile, centers, appointments, donor card, history, and notifications), then align the canonical Flutter repository against it.

## 2026-08-01 — Mobile password identity and profile contract

Status: completed

Scope: backend and donor mobile API.

Delivered:

- Added donor password registration and email-or-phone login under `/api/v1` without reopening staff web registration.
- Preserved nullable donor email while requiring a unique phone number and adding a database uniqueness guarantee against concurrent duplicate registration.
- Normalized legacy Flutter payloads, including omitted device names and login aliases, so the existing client contract can migrate incrementally.
- Reused one transactional token issuer for password and Firebase authentication: named 30-day Sanctum tokens, `donor:read`/`donor:write` abilities, and same-device replacement.
- Required active donor status for mobile password login and returned one generic localized failure for missing users, bad passwords, inactive donors, and staff accounts.
- Added current-user/profile compatibility routes, approved profile mutation, active preferred-center validation, unique phone validation, and English/Kiswahili preference persistence.
- Prevented donors from changing a blood group once NBTS staff has verified it.
- Added validated 5 MB/3000×3000 raster profile-photo upload with transactional model/audit updates, rollback cleanup, local-file replacement, and safe handling of remote Firebase photo URLs.
- Expanded the user API resource with the top-level compatibility fields consumed by the canonical Flutter model while retaining a structured `donor_profile` object.
- Added redacted audit events for password account creation, successful password authentication, profile changes, and photo changes.

Database/API impact:

- Added `users_phone_unique` after proving the safe clone had no duplicate or empty non-null phone values.
- Applied the additive migration only to `nbts_new_dev`; it now has 31 migrations while user/profile counts remain 11 and 7.
- Refreshed the schema-only MySQL test snapshot; shared `nbts` remains unchanged at its original migration history.
- Added `/api/v1/auth/register`, `/api/v1/auth/login`, `/api/v1/user`, `/api/v1/profile`, `/api/v1/profile/photo`, and the legacy-compatible `/api/v1/auth/logout` alias.

Automated verification:

- Password registration/login suite: 5 tests passed.
- Mobile profile suite: 5 tests passed.
- Focused mobile password/profile suites: 10 tests passed with 117 assertions.
- Full suite: 85 tests passed with 416 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors before the focused/full feature runs.
- `vendor/bin/pint --dirty --format agent`: passed.
- `git diff --check`: passed before final documentation updates.

Browser/device verification:

- Server API milestone only. Flutter analyzer, emulator/device, and network-environment testing remain pending.

Known limitations:

- Mobile password reset/email verification flows are not yet exposed through the donor API.
- Phone values are trimmed and unique but not yet normalized to a single E.164 format; that requires an approved Tanzania/international number policy and migration plan for existing values.
- Public profile-photo URLs require the deployment's public storage link or object-storage URL to be configured.
- Donor card, discovery, appointments, history, loyalty, content, notifications, and FCM registration APIs remain pending.

Next dependent task:

- Implement read-only center/campaign/article discovery and donor appointment/card/history capabilities, then align the Flutter repositories and environment-specific base URL.

## 2026-08-01 — Donor center discovery and appointment workflow API

Status: completed

Scope: backend and donor mobile API.

Delivered:

- Added a public active blood-center directory/detail API with bounded pagination, text/city/service filtering, public storage URLs, and all aliases consumed by the existing Flutter center model.
- Added date-specific appointment-slot discovery using the established six daily times, stable availability aliases, localized status/reason copy, configurable per-slot capacity, and a configurable 90-day booking window.
- Added donor-owned appointment list, detail, nearest upcoming, booking, rescheduling, and cancellation endpoints.
- Rebuilt appointment mutations as retryable transactional actions instead of porting legacy controller writes.
- Serialized bookings by locking the center row before checking slot capacity and active donor appointments.
- Enforced one `pending`/`confirmed` appointment per donor, active-center requirements, exact configured slot times, future/window limits, per-slot capacity, donor ownership, active account status, and Sanctum read/write abilities.
- Rescheduling reruns availability rules, returns confirmed appointments to `pending`, clears prior confirmation/handler state, and preserves the prior schedule in redacted audit metadata.
- Cancellation is donor-owned and auditable while staff confirmation/completion continues through the separate center-authorized transition action.
- Added appointment and center API Resources matching the Flutter compatibility parser while retaining stable status/reason codes.

Database/API impact:

- No schema or existing-data changes were required.
- Added public center list/detail/slot routes and protected donor appointment list/upcoming/detail/create/update/cancel routes.
- Appointment slot capacity defaults to one and the booking window to 90 days through `config/nbts.php` and environment overrides.

Automated verification:

- Blood-center API suite: 4 tests.
- Donor appointment API suite: 6 tests.
- Focused center/appointment run: 10 tests passed after correcting the create response expectation to HTTP 201.
- Full suite: 95 tests passed with 485 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.

Browser/device verification:

- Server API milestone only; Flutter repository and emulator/device integration remain pending.

Known limitations:

- Slot times and capacity are configuration-based because the deployed schema has no per-center schedule/capacity tables; managed center schedules remain a later additive feature.
- Dedicated parallel-process contention testing is still pending even though center row locking serializes normal competing requests.
- Appointment reminders and creation/reschedule/cancel notifications await the after-commit notification/outbox layer.
- Expanded check-in, no-show, and rescheduled status codes require an additive compatibility migration before use.

Next dependent task:

- Implement donor card, eligibility summary, donation history/summary, then content/campaign discovery and notification/FCM registration.

## 2026-08-01 — Donor card, eligibility, and donation-history API

Status: completed

Scope: backend and donor mobile API.

Delivered:

- Added a Flutter-compatible digital donor card endpoint with a five-minute configurable signed QR payload, preferred center, blood-group confidence, eligibility state, loyalty fields, and completed-donation statistics.
- Added a reusable QR issuer/verifier using purpose/version markers, random nonces, HMAC-SHA256 signatures, strict URL-safe Base64 parsing, configurable signing-key separation, expiry checks, identity matching, and active donor-account enforcement.
- Kept legacy donors usable by safely creating a missing donor profile and stable donor ID during card retrieval while explicitly preserving HTTP 200 semantics for the GET response.
- Added a donor-facing eligibility endpoint that prioritizes effective deferrals, persisted staff decisions, and next-donation intervals without making or storing a new clinical decision.
- Added English and Kiswahili eligibility guidance and stable, untranslated eligibility codes. Every response states that center screening remains required.
- Added owner-scoped, bounded, paginated donation history with the aliases consumed by the canonical Flutter model.
- Added authoritative completed-donation summary totals, volume, last donation, and the legacy lives-touched estimate with an explicit estimate flag.
- Updated donation authorization so active donors can view their own history while staff center-scope rules remain intact.
- Corrected `Deferral` and `DonorProfile` model property contracts so static analysis reflects their enum/date casts.

Database/API impact:

- No schema or existing-data changes were required.
- Added protected `GET /api/v1/donor-card`, `GET /api/v1/eligibility`, `GET /api/v1/donations`, and `GET /api/v1/donations/summary` routes.
- Added `NBTS_DONOR_CARD_QR_TTL_SECONDS` and optional `NBTS_DONOR_CARD_QR_SIGNING_KEY` environment configuration. The application key remains the safe fallback when a dedicated key is not configured.

Automated verification:

- Donor journey API suite: 7 tests covering compatible card data, legacy profile creation, deferral priority, Kiswahili interval guidance, owner-scoped pagination, authoritative summaries, token ability, and donor-role boundaries.
- QR service suite: 3 tests covering valid signatures, payload tampering, expiration, and inactive donors.
- Focused donor journey: 10 tests; two edge-case failures were corrected and rerun before the complete suite.
- Full suite: 105 tests passed with 537 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.

Browser/device verification:

- Server API milestone only; Flutter repository parsing, analyzer, emulator/device networking, and rendered donor-card QR verification remain pending.

Known limitations:

- The endpoint exposes current recorded eligibility guidance, not a substitute for same-day staff screening.
- A dedicated QR signing key is optional until deployment secrets are provisioned; changing it invalidates outstanding five-minute cards.
- Staff QR scan/search and audited reception are a later staff workflow milestone.
- Loyalty and leaderboard APIs remain pending even though existing card fields are exposed.

Next dependent task:

- Implement public/mobile campaign, article, publication, and schedule discovery; then add notification/FCM device registration and align the canonical Flutter repositories.

## 2026-08-01 — Published campaign, article, publication, and schedule APIs

Status: completed

Scope: backend, public API, and donor mobile API.

Delivered:

- Ported the deployed campaign and article tables into typed Laravel models, factories, enum casts, relationships, and reusable public-visibility scopes.
- Added public campaign list/detail APIs with text, state, type, target-blood-group, and center filters; bounded pagination; emergency-first ordering; active-center enforcement; and Flutter compatibility aliases.
- Prevented stale campaign states from leaking by excluding records whose end time has passed even when legacy status still says `upcoming` or `ongoing`.
- Added public article list/detail APIs with search, category, featured-state filters, bounded pagination, public storage URLs, and every field consumed by the canonical Flutter dashboard/article sheet.
- Enforced a non-null publication timestamp at or before the current time; draft, archived, and future-scheduled articles remain private.
- Added publication list/detail APIs as a safe projection of published articles with approved attachments, avoiding a speculative new table while preserving document names, MIME types, source attribution, and download URLs.
- Added donation schedule list/detail APIs as a location/time projection of visible campaigns and active centers, avoiding duplicated schedule state until staff management requirements are finalized.
- Added deterministic article slug and reading-time maintenance for future content-management entry points.

Database/API impact:

- No schema or existing-data changes were required; the existing five campaigns and three articles remain intact.
- Added public list/detail routes for campaigns, articles, publications, and schedules under `/api/v1`.
- Publications currently use article attachment fields; schedules currently use campaign event dates and center location fields.

Automated verification:

- Public-content API suite: 7 tests passed with 51 assertions.
- Full suite: 112 tests passed with 588 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.

Browser/device verification:

- API-only milestone. Public page rendering and Flutter emulator/device integration remain pending.

Known limitations:

- The cloned campaigns are all past their end dates as of 2026-08-01, so the production-like clone correctly returns no current campaigns until authorized staff publish new dates.
- Publications and schedules are compatibility projections, not independent management domains. Dedicated additive tables should be introduced only when workflow fields, review states, ownership, and migration rules are approved.
- Content currently has one language per record; explicit bilingual pairing/fallback remains pending.

Next dependent task:

- Implement donor notification inbox/unread/read/delete APIs and secure FCM device-token registration, then align and test the canonical Flutter client.

## 2026-08-01 — Donor notification inbox and FCM device registration

Status: completed

Scope: backend and donor mobile API.

Delivered:

- Ported the deployed custom user-notification and FCM-token tables into typed Laravel models, factories, casts, and user relationships.
- Added owner-scoped notification list, unread-count, mark-one-read, mark-all-read, and delete endpoints matching the canonical Flutter repository.
- Added bounded pagination plus optional unread/type filters while retaining `body`/`message`, `read`/`read_at`, and timestamp compatibility fields.
- Added Android/iOS FCM token registration with global deduplication, token refresh/reassignment support, idempotent repeat registration, and an owner-scoped idempotent unregister endpoint.
- Added tamper-evident audit events for FCM registration and removal using only a SHA-256 fingerprint and platform; raw device tokens are excluded from audit evidence.
- Enforced active donor role, Sanctum `donor:read`/`donor:write` abilities, and record ownership across the capability group.
- Corrected Laravel's single-resource wrapping edge case caused by a notification's own `data` metadata field, keeping the outer `{ data: ... }` API contract stable.

Database/API impact:

- No schema or existing-data changes were required.
- Added seven protected notification/device routes under `/api/v1/notifications`.
- Existing unique `f_c_m_tokens.token` storage remains the global deduplication boundary.

Automated verification:

- Notification/FCM API suite: 8 tests covering inbox isolation, filters, unread totals, individual/all read operations, deletion ownership, registration reassignment/deduplication, audit redaction, unregister ownership/idempotency, abilities, and validation.
- The one single-resource wrapping failure was corrected and passed its targeted rerun.
- Full suite: 120 tests passed with 637 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.

Browser/device verification:

- Server API milestone only. Live FCM delivery requires the fresh external service-account key and a test device; Flutter integration remains pending.

Known limitations:

- FCM HTTP v1 send jobs, provider-error handling, invalid-token retirement, delivery attempt records, and after-commit notification triggers remain pending.
- The deployed token column is limited to 255 characters; current Android/iOS registration tokens fit the existing contract, but schema expansion must precede support for a provider that emits longer values.
- Notification delete is a hard delete in the deployed schema; retention/archive policy should be decided before staff broadcast management is added.

Next dependent task:

- Align the canonical Flutter API environment and repositories with the now-tested v1 contract, run Flutter analysis/tests, then implement loyalty and queued FCM delivery.

## 2026-08-01 — Canonical Flutter API and security alignment

Status: implementation complete; SDK verification blocked

Scope: canonical Flutter repository at `/home/kevin/Desktop/MAIN /PROJECTS/NBTS/nbts-mobile`.

Delivered:

- Removed the developer-specific `192.168.*` API default and added `API_BASE_URL` build-time configuration.
- Added Android-emulator and loopback debug defaults, normalized path/query composition, absolute HTTP(S) validation, a required release value, and mandatory HTTPS for release builds.
- Added `X-Locale` to every API request from the current English/Kiswahili language controller.
- Extended DELETE requests with JSON bodies and connected FCM unregister-on-logout before the Sanctum token is revoked.
- Removed global Android and iOS cleartext allowances. Android local HTTP remains allowed only in debug/profile manifest overlays.
- Added Flutter contract tests for URL handling, authorization/locale headers, token unregister payloads, and Laravel response parsing across donor card, eligibility, campaign, article, donation, and notification models.
- Updated the canonical mobile README and mobile achievement log with current environment commands and platform support boundaries.

Verification:

- Flutter repository was clean before this scoped work and now contains only the described uncommitted changes.
- Android manifests and iOS plist pass `xmllint --noout`.
- `git diff --check` passes in both Laravel and Flutter repositories.
- `flutter analyze`, `flutter test`, and Dart formatting could not run because `flutter`, `dart`, `fvm`, and `melos` are not installed on the workstation.

Known limitations:

- iOS has no `GoogleService-Info.plist`; Firebase/Apple login and push notification support are not claimed.
- Android emulator/device end-to-end testing remains pending until Flutter tooling and a reachable API environment are available.
- No Flutter dependency versions were changed and no generated platform files were regenerated without the SDK.

Next dependent task:

- Install or provide a Flutter 3.41+/Dart 3.11-compatible SDK, run format/analyze/tests, then perform the Android donor-journey device pass against a configured `API_BASE_URL`.

## 2026-08-01 — Public NBTS website foundation

Status: completed

Scope: public web, backend read models, localization, and frontend QA.

Delivered:

- Rebuilt the public website on Laravel 13 with a dedicated responsive layout, an NBTS editorial visual system, Lucide icons, restrained motion, accessible landmarks/focus states, and English/Kiswahili navigation and static guidance.
- Added the home, about, donate, services, eligibility, center directory/detail, campaign directory/detail, news directory/detail, publications, FAQ, contact, download-app, and aggregate impact experiences.
- Connected public directories to the cloned database with validated searches, bounded pagination, active-center enforcement, campaign lifecycle visibility, published-article visibility, draft/archive protection, and emergency-first campaign ordering.
- Kept public impact data aggregate-only and withheld unverified app-store/QR destinations rather than inventing links or claims.
- Reused selected approved visual assets from the previous workspace without copying environment files, credentials, generated dependencies, or private Firebase material.
- Replaced the personal donor name in the reused mobile preview with the generic label `NBTS Donor`; the public project contains only the anonymized image.

Main implementation:

- `routes/web.php`
- `app/Http/Controllers/Web/`
- `app/Http/Requests/Web/`
- `resources/views/layouts/public.blade.php`
- `resources/views/web/`
- `resources/views/components/public/`
- `lang/en/public.php`
- `lang/sw/public.php`
- `resources/css/app.css`
- `resources/js/app.js`
- `tests/Feature/PublicWebsiteTest.php`

Database/API impact:

- No schema or existing-data changes were required.
- Public web queries reuse the tested center, campaign, article, publication, donation, and unit/inventory records through read-only controllers and existing model visibility scopes.
- The production-like clone correctly has no visible campaigns because all five legacy campaign dates have passed as of 2026-08-01.

Automated verification:

- Public website feature suite: 6 tests passed with 46 assertions.
- Complete Laravel suite: 126 tests passed with 683 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: passed with zero errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- `npm run build`: passed with 1,787 transformed modules.
- Composer and npm security audits: no known vulnerabilities.

Browser/device verification:

- Agent-browser desktop pass at 1600×900 covered every implemented public route, including a live center detail and published news detail; all pages had the expected title/heading and no horizontal overflow.
- English/Kiswahili switching, center filtering, FAQ expansion, desktop navigation, and the mobile navigation open/close state were exercised successfully.
- Mobile overflow checks at 375/390-pixel widths passed for the home, centers, FAQ, download-app, and news pages.
- Browser console and Laravel browser logs contained no application or JavaScript errors; only normal Vite development connection messages were present.

Known limitations:

- Managed records currently have one stored language; bilingual content fields, editorial fallback state, and staff publishing workflows remain pending.
- Legal/privacy pages, complete social/canonical metadata, custom error pages, a dedicated publication detail route, and verified app-store/QR destinations remain pending.
- The retained legacy official-news image is suitable only for small placements and is not used as a large hero because its source resolution is 357×210.

Next dependent task:

- Build the permission-aware Livewire/Flux staff shell and overview queues, then implement the workflow modules in operational order.

## 2026-08-01 — Phase 0 operational and CI closure

Status: completed

Scope: repository automation, backup safety, operations documentation, and development access QA.

Delivered:

- Replaced the baseline CI setup with a MySQL 8.4 service that matches PHPUnit's `nbts_new_test` contract, locked PHP/npm installation, a clean production frontend build, and the repository's Pint/Larastan/Pest gate.
- Added `docs/operations.md` with explicit environment boundaries, operator/approver roles, private backup handling, retention, isolated restore commands, verification evidence, deployment steps, and rollback controls without credentials.
- Added `docs/api.md` as the current `/api/v1` and canonical Flutter endpoint/compatibility index, including the documentation/test update rule for every future contract change.
- Moved the verified source archive from the stale package destination `storage/app/private/Laravel/` to the monitored `storage/app/private/NBTS/` destination after the application name correction and restricted it to mode `0600`.
- Kept the agent-browser Chromium window visible at 1600×900 and verified reusable development access for the super-admin, center-manager, and center-staff web accounts plus the donor mobile API account.

Database/API impact:

- No schema or domain-record changes.
- The `staff@nbts.test` development-demo password had drifted from the documented demo credential and was restored only in `nbts_new_dev`; no cloned personal-user password was changed.
- The one live donor API token created for credential verification was immediately revoked after the request.

Automated verification:

- `composer ci:check`: passed Pint, Larastan level 7 with zero errors, and all 126 Pest tests with 683 assertions.
- `npm ci`: 70 packages installed from the lockfile and zero vulnerabilities reported.
- `npm run build`: passed with 1,787 transformed modules.
- GitHub Actions workflow YAML parsing and `git diff --check`: passed.
- `php artisan backup:list`: the NBTS destination is reachable and healthy with one monitored archive.
- `unzip -t storage/app/private/NBTS/2026-08-01-09-19-58.zip`: no compressed-data errors; the expected `db-dumps/mysql-nbts.sql` member is present.

Browser/device verification:

- Visible 1600×900 browser login succeeded for `admin@nbts.test`, `manager@nbts.test`, and `staff@nbts.test` and reached `/dashboard` under each staff role.
- Live password login for `donor@nbts.test` returned a valid bearer-token response through `/api/v1/auth/login`; the test token was then removed.
- Browser QA exposed public Lucide initialization warnings that were not visible in the earlier error-only check; they remain an explicit Phase 4 fix and prevent the public phase from being marked complete.

Known limitations:

- The verified archive is a database-only, unencrypted, private local-development artifact. Media coverage, archive encryption, off-site storage, scheduled verification, production recovery objectives, and a production restore drill remain Phase 6 requirements.
- CI syntax and the exact commands are locally verified; the first remote GitHub Actions run still requires a push or pull request.
- Flutter checks remain unavailable until a compatible Flutter/Dart SDK is installed.

Next dependent task:

- Complete every open Phase 1 identity, rate-limiting, permission, localization, and cloned-account verification item before advancing to Phase 2.

## 2026-08-01 — Phase 1 canonical roles and record authorization

Status: completed

Scope: backend identity, authorization, center isolation, and loyalty relationships.

Delivered:

- Completed the `User` relationship graph for badges, donor badges, rewards, donor rewards, and leaderboard entries with typed loyalty models, casts, scopes, enums, and factories.
- Removed the legacy `users.role` column from runtime mobile authentication and public donor counts; Spatie roles and permissions are now canonical, while the column remains readable only for migration/backfill compatibility.
- Added automatically discovered policies for all 22 application record models and completed the supported actions on the four existing policies.
- Enforced donor ownership for personal notification, device-token, eligibility, deferral, and loyalty records.
- Enforced assigned-center boundaries for donor visibility, center staffing, eligibility, deferrals, appointments, donations, blood units, inventory, adjustments, alerts, and non-public campaign records.
- Preserved the national operations boundary: NBTS administrators can operate nationally without user/role, backup, or settings authority; active super administrators retain the explicit whole-system override.

Database/API impact:

- No schema or existing-data changes.
- Mobile password and Firebase authentication now reject accounts without the canonical donor role even when the legacy role column says `donor`.

Automated verification:

- Canonical-role mobile/Firebase/public regression group: 21 tests passed with 169 assertions.
- Record-policy, center authorization, appointment, donation, and inventory workflow group: 24 tests passed with 157 assertions.
- Laravel Pint formatted all touched PHP files before both test groups.

Browser/device verification:

- No UI was added for this backend authorization milestone; the existing visible 1600×900 authenticated staff browser remains open for the later Phase 1 locale smoke pass.

Known limitations:

- Phase 1 remains open for sensitive-action throttling, complete English/Kiswahili coverage, bilingual managed-content rules, stable API-code verification, and cloned-account authentication evidence.

Next dependent task:

- Complete and test sensitive Fortify and Livewire action throttling before starting the remaining Phase 1 localization work.

## Achievement template

Copy this section for future verified work.

### YYYY-MM-DD — Feature name

Status: completed

Scope: web | mobile | backend | operations

Delivered:

- Feature behavior.

Main implementation:

- `path/to/file`

Database/API impact:

- None, or exact migrations/routes/contracts.

Automated verification:

- `command`
- Result and relevant test count.

Browser/device verification:

- Viewport/device, route, role, language, and result.

Known limitations:

- Explicit limitation, or `None`.

Next dependent task:

- Next task.
