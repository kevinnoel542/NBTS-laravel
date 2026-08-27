# NBTS achievement log

Last updated: 2026-08-27

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

## 2026-08-27 — Flutter owner handoff clarified

Status: completed

Scope: mobile/API documentation.

Requirement IDs: MOB-COMPLETE, API-CONTRACT, MSG-OMNI.

Delivered:

- Added the current Flutter work package to `docs/technical/api.md`.
- Defined the mobile screens to build now: auth, donor home, donor card, eligibility, centers, slots, appointments, donations, loyalty, notifications, profile, preferences, public content, and error/offline states.
- Defined the Laravel endpoint contracts the Flutter owner should use.
- Defined restricted workflows that must not be built in Flutter yet, including staff dashboards, screening, collection authority, barcode/device authority, laboratory release, hospital, transfusion, recall, and CAPA.
- Defined the required Flutter evidence: formatting, analyzer, tests, Android journey, Firebase login, EN/SW behavior, appointment flow, donor-card expiry refresh, notification behavior, FCM registration/unregistration, offline handling, accessibility, and screenshots/recordings.

Database/API impact: none. This documents the existing Laravel `/api/v1` contract and current mobile implementation boundaries.

Automated verification:

- `git diff --check` must pass before handoff.

Browser/device verification: not performed in this documentation-only update. The Flutter owner must provide device evidence.

Known limitations:

- This does not implement Flutter code.
- iOS remains unsupported until the Flutter owner provides approved configuration and device evidence.
- Real push delivery remains unproven while Laravel is in construction-safe push mode or without approved FCM device testing.

Next dependent task:

- The Flutter owner should implement against `docs/technical/api.md` and return analyzer/test/device evidence before the mobile Phase 12 gate is marked complete.

## 2026-08-03 — National blood-management roadmap expansion

Status: completed

Scope: project planning, operating-model design, clinical-safety roadmap, center hierarchy, roles, workflows, and implementation sequencing.

Requirement IDs: DOC-ROADMAP-001, GOV-STRUCT-001, SAFE-CHAIN-001.

Delivered:

- Reclassified the current verified system as a donor-engagement and foundational blood-operations platform rather than a complete national blood-management system.
- Expanded `docs/planning/task.md` into the implementation roadmap for national, center, laboratory, component, logistics, hospital, transfusion, haemovigilance, quality, security, resilience, and rollout work.
- Expanded `docs/planning/workflow.md` into the target operating model from donor recruitment through recipient outcome, recall, and look-back.
- Defined national, blood-center, department, hospital, quality, audit, and technical operating roles while preserving permissions and center assignments as the real authorization boundary.
- Added explicit center hierarchy, candidate center types, departments, role assignments, data visibility, work queues, dashboards, approval levels, escalation paths, and separation-of-duties rules.
- Added the missing safety-critical capabilities: unique donation identification, bag–sample–component barcode linkage, laboratory orders/results/QC, hard quarantine, authorized release, component parent-child lineage, component-level FEFO inventory, cold chain, hospital requests, compatibility, dispatch/receipt, bedside verification, transfusion outcomes, adverse-event investigation, recall, look-back, deviations, CAPA, SOPs, competency, EQA, downtime, disaster recovery, interoperability, and formal change control.
- Replaced the future assumption that one donation permanently equals one usable blood product with a compatibility plan: one donation/collection identifier may produce zero or more traceable components.
- Added Must/Should/Could priority, safety classification, requirement IDs, dependencies, completion gates, approvals, and evidence expectations for future work.
- Added a controlled implementation sequence: discovery, core safety pilot, regional scale, national optimization, and formal acceptance.

Database/API impact: none. This milestone changes documentation and roadmap scope only.

Automated verification:

- Updated Markdown files exist and render as plain Markdown.
- Existing achievement entries remain preserved.
- New roadmap items are marked pending unless direct implementation evidence already exists.
- Current completed features remain separated from target national-service capabilities.

Browser/device verification: not applicable to this documentation milestone.

Clinical/operational approval:

- The roadmap requires NBTS clinical, laboratory, quality, operations, ICT, data-protection, hospital, and Ministry stakeholders to approve final rules before safety-critical implementation.
- Candidate center types, component catalog, test algorithms, shelf lives, release rules, retention periods, identifiers, labels, hospital data requirements, and service levels are not treated as approved policy merely because they appear in the roadmap.

Known limitations:

- No newly listed laboratory, component, hospital, cold-chain, haemovigilance, interoperability, or national-rollout feature is claimed as implemented.
- Publicly available operational evidence does not define every current NBTS center workflow, device, hospital interface, legal requirement, or staffing arrangement.
- The implementation sequence and duration ranges remain planning guidance, not contractual estimates.

Next dependent task:

- Conduct the approved foundation-and-discovery phase, produce the current-state workflow map and safety case, then approve the unique donation identifier, barcode standard, component model, laboratory rule set, quarantine/release authority, center taxonomy, hospital integration boundary, and pilot sites before schema implementation.

## 2026-08-01 — Modernization control documents

Status: completed

Scope: project planning and architecture documentation.

Delivered:

- Created the living implementation checklist in `docs/planning/task.md`.
- Created this evidence-based achievement log.
- Created `docs/planning/workflow.md` by reconciling the previous NBTS project overview, database model, public website pages, admin pages, mobile/backend synchronization checklist, system requirements, and main workflows with the Laravel 13/Fortify/Livewire direction.
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
- Connected Sanctum API tokens, Spatie roles, Fortify two-factor authentication, passkeys, email verification, and locale preference to the same user model. Staff email verification was later disabled by the 2026-08-11 construction decision recorded below.
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
- Added `docs/operations/runbook.md` with explicit environment boundaries, operator/approver roles, private backup handling, retention, isolated restore commands, verification evidence, deployment steps, and rollback controls without credentials.
- Added `docs/technical/api.md` as the current `/api/v1` and canonical Flutter endpoint/compatibility index, including the documentation/test update rule for every future contract change.
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

## 2026-08-01 — Phase 1 sensitive account-action throttling

Status: completed

Scope: web authentication and Livewire account security.

Delivered:

- Retained named Fortify limits for password login, two-factor challenge, and passkey authentication/registration.
- Added per-route, per-identity, and per-IP minute/hour limits for password reset requests, password resets, password confirmation, 2FA enable/confirm/disable/recovery regeneration, and passkey deletion.
- Added a shared Livewire action limiter for password changes, profile/email changes, verification resend, account deletion, passkey deletion, and 2FA enable/confirm/disable actions.
- Returned a validation error with the remaining retry interval when a Livewire action is blocked.

Database/API impact:

- No schema or API contract changes; rate-limit counters use the configured Laravel cache.

Automated verification:

- Sensitive-action limiter suite: 4 tests passed with 53 assertions.
- The broader authentication/settings regression run passed 30 of 31 tests; its sole failure was an incorrect middleware-introspection assertion. After correcting the assertion to expand the web middleware group, the focused suite passed completely.
- Laravel Pint passed after formatting the implementation and tests.

Browser/device verification:

- No visual behavior changed; the visible browser remains open for the complete EN/SW Phase 1 smoke pass.

Known limitations:

- Rate limits are process/cache-environment dependent and must use a shared production cache when the application is horizontally scaled.

Next dependent task:

- Complete English/Kiswahili UI, validation, system, PDF, and operational-action translations and verify both locales.

## 2026-08-01 — Phase 1 localization and completion gate

Status: completed

Scope: staff web authentication, localization, mobile API contracts, authorization, and quality gates.

Delivered:

- Completed current English/Kiswahili coverage for navigation, authentication, settings, validation, empty states, system messages, PDF headings, accessibility labels, and operational labels.
- Added persistent language controls to guest and authenticated layouts and translated static Livewire page titles.
- Defined the managed-content language contract: English base fields, future polymorphic Kiswahili translations, explicit fallback behavior, recipient-locale notification snapshots, and no silent machine translation.
- Preserved machine-stable API status, type, and blood-group codes while adding separately localized display labels.
- Verified that cloned staff and admin users retain their existing password hashes and can authenticate without password resets; only the documented local demo staff password differs intentionally.
- Completed record-level policies for every current application model and enforced canonical Spatie roles with center-scoped operational access.

Main implementation:

- `lang/sw.json`, `lang/sw/*.php`, and the matching English operational/system/PDF catalogs.
- `config/content.php` and the managed-content contract in `docs/planning/workflow.md`.
- `app/Http/Resources/Api/V1/*Resource.php` stable-code and localized-label contracts.
- `app/Policies/*.php`, `app/Concerns/ThrottlesSensitiveActions.php`, and `app/Http/Middleware/ThrottleSensitiveAuthenticationActions.php`.

Database/API impact:

- No schema changes were required.
- Existing API code fields remain stable across locales; localized labels are additive fields.
- The source `nbts` database remained read-only, while authentication verification used the isolated `nbts_new_dev` clone.

Automated verification:

- `composer ci:check`: 145 tests passed with 1,579 assertions; Pint passed and Larastan reported zero errors.
- `npm run build`: the Tailwind/Vite production bundle completed successfully.
- `git diff --check`: passed.

Browser/device verification:

- Headed Chromium at 1600x900, signed in as the cloned center-staff demo account on `/settings/security`.
- Kiswahili and English titles, navigation, account menus, form labels, password visibility labels, 2FA action, and passkey action rendered correctly; the current page reported no browser errors.

Known limitations:

- Stored Boost logs contain older missing-Lucide warnings from unfinished public pages; these remain a Phase 4 public-website cleanup item.
- Managed-content translation persistence and editors are intentionally scheduled for Phase 4; Phase 1 defines and tests their contract.

Next dependent task:

- Complete the Phase 2 core-domain models, seeders, services, operations, PDFs, audits, and corresponding Pest coverage before starting Phase 3.

## 2026-08-01 — Phase 2 domain records and safe seed foundation

Status: completed

Scope: backend models, authorization, reference data, and local test/demo setup.

Delivered:

- Completed typed models, relationships, casts, scopes, factories, and record policies for every deployed NBTS domain table.
- Audited the legacy repositories and intentionally did not copy their thin, unbounded Eloquent wrappers; current actions, services, and model scopes are the tested query and mutation boundaries.
- Added deterministic seeders for canonical roles/permissions, four Tanzanian blood centers, loyalty badges/rewards, and public education content.
- Added environment-gated local/testing demo accounts and center assignments while preserving an existing account password on every rerun.
- Ensured reference and demo seeders are idempotent and do not delete existing center or content records.

Main implementation:

- `app/Models/*.php`, `database/factories/*.php`, and `app/Policies/*.php`.
- `database/seeders/RolePermissionSeeder.php`, `BloodCenterSeeder.php`, `LoyaltySeeder.php`, `ArticleSeeder.php`, `DemoDataSeeder.php`, and `DatabaseSeeder.php`.

Database/API impact:

- No schema or API contract changes.
- Seeders were verified only in the isolated test database; the cloned development and source databases were not seeded during this milestone.

Automated verification:

- Seeder suite: 2 tests passed with 16 assertions, including idempotency, production no-op behavior, canonical role assignment, and password preservation.
- Core model, loyalty, and record-policy regression group: 11 tests passed with 94 assertions.
- Laravel Pint passed, Larastan reported zero errors, and `git diff --check` passed.

Browser/device verification:

- Not applicable to this backend-only milestone; the headed 1600x900 Chromium session remains open on the authenticated account UI.

Known limitations:

- Phase 2 operational actions for donor reception, screening/deferrals, inventory maintenance, alerts, campaigns, loyalty, and notifications remain open.

Next dependent task:

- Complete donor and center operational services, including donor creation/search, preferred centers, and center-staff lifecycle, before moving to eligibility workflows.

## 2026-08-11 - Staff email verification disabled during construction

Status: completed

Scope: staff web authentication, profile settings, routes, and documentation.

Delivered:

- Disabled Fortify email verification and removed the `MustVerifyEmail` requirement from the staff user model.
- Removed the verification gate from dashboard, operational, appearance, and security routes.
- Removed the unverified-email warning and resend action from Profile settings.
- Preserved Firebase verified-email checks for donor mobile identity linking because they are a separate security boundary.
- Recorded re-enablement as a product/security production-readiness decision.

Main implementation:

- `config/fortify.php`
- `app/Models/User.php`
- `app/Livewire/Settings/Profile.php`
- `resources/views/livewire/settings/profile.blade.php`
- `routes/web.php`
- `routes/settings.php`

Database/API impact:

- No schema or API contract changes.
- Existing `email_verified_at` values are retained as historical/identity metadata but no longer gate staff web access.

Automated verification:

- Focused Fortify, Profile, sensitive-action, and dashboard suite: 15 tests passed with 77 assertions.
- Laravel Pint passed for all touched PHP files.
- `php artisan route:list --name=verification --except-vendor` confirmed that no verification or resend route is registered.

Browser/device verification:

- The visible headed Chromium session remains open at 1600x900 on the current NBTS login page.
- The Profile component regression test proves that the verification warning and resend text are not rendered for an unverified staff account; authenticated visual verification remains part of the active Phase 2 browser gate.

Known limitations:

- Product and security owners must decide whether to re-enable staff email verification before pilot or production acceptance.

Next dependent task:

- Resume the locked Phase 2 audit and complete every remaining Phase 2 sub-item before starting Phase 3.

## 2026-08-11 — Phase 2 core operations completion gate

Status: completed

Scope: staff web operations, backend domain workflows, inventory integrity, donor engagement, notifications, scheduling, localization, and evidence.

Delivered:

- Completed center-aware donor reception with staff registration, stable donor IDs, QR/donor ID/phone/email/name search, preferred-center handling, center maintenance actions, and center-staff assignment lifecycle.
- Completed staff-led eligibility screening, temporary/permanent deferrals, safe deferral lifting, official donation-interval enforcement, and auditable staff authority.
- Completed appointment confirmation, staff rescheduling, check-in, cancellation, no-show, completion, capacity enforcement, and walk-in handling.
- Completed transactional donation recording with center/eligibility authorization, blood-group verification, donor/unit/inventory updates, and replay-safe idempotency keys.
- Completed blood-unit transitions, exactly-once inventory deltas, controlled manual corrections, negative-balance protection, expiry processing, final disposal, reconciliation UI/command, and real two-worker contention verification.
- Completed transactional low-stock response, linked emergency campaigns, consented and center/blood-group-aware donor targeting, deterministic recognition, rewards, tiers, and leaderboards.
- Completed in-app, push, SMS, and email notification orchestration with per-channel preferences, duplicate-safe appointment reminders, retry metadata, failure tracking, and a filterable Engagement delivery-status table.
- Completed the compact premium staff shell for all Phase 2 workspaces with page summaries, workflow tabs, search, filters, clear/reset controls, configurable columns, sorting, pagination, CSV export, Lucide icons, and content-sized table panels.

Main implementation:

- `app/Livewire/Operations/Workspace.php` and `resources/views/livewire/operations/workspace.blade.php`.
- `app/Actions/Donors`, `app/Actions/Centers`, `app/Actions/Eligibility`, `app/Actions/Appointments`, `app/Actions/Donations`, `app/Actions/Inventory`, `app/Actions/Response`, `app/Actions/Engagement`, and `app/Actions/Content`.
- `app/Services/DonorSearchService.php`, `app/Services/DonorRecognitionService.php`, `app/Services/AppointmentReminderService.php`, `app/Services/InventoryStockAlertService.php`, and `app/Services/Notifications`.
- `app/Notifications`, `app/Contracts/PushTransport.php`, `app/Contracts/SmsTransport.php`, and `app/Console/Commands/ReconcileInventoryCommand.php`.
- `resources/css/app.css`, `config/operations.php`, `lang/en`, and `lang/sw`.

Database/API impact:

- Added donation idempotency keys, notification delivery records, notification source keys, and donor email-notification preferences through additive migrations.
- Applied all Phase 2 migrations successfully to the existing `nbts_new_dev` clone while preserving its operational records.
- Proved a clean database path through the Feature and Unit suites, which rebuilt `nbts_new_test` from the schema/migrations.
- Kept SMS and push provider transports construction-safe and log-backed; Phase 3 supplies approved Firebase/provider credentials behind the tested transport contracts.

Automated verification:

- `php artisan test --compact tests/Feature`: 191 tests passed with 2,209 assertions.
- `php artisan test --compact tests/Unit`: 4 tests passed with 15 assertions, including two-process inventory contention.
- `vendor/bin/pint --dirty --format agent`: passed.
- `npm run build`: passed; Vite transformed 1,787 modules and emitted the production manifest.
- `php artisan view:cache`: passed.
- `php artisan schedule:list`: confirmed the hourly duplicate-safe appointment-reminder command.
- `git diff --check`: passed.

Browser/device verification:

- Headed Chromium remained visible at 1600x900 and authenticated as the local super-administrator account.
- Verified Overview, Donor reception, Appointments, Eligibility, Donations, Blood operations, Response, Engagement, Delivery status, and Content.
- Verified headings, summaries, workflow tabs, filter status URL state, clear/reset action, configurable columns, responsive table width, current QR/registration tab transitions, and absence of current browser errors.
- The audit found and removed a forced 390px table-panel height; one-row queues now end immediately after their content with no oversized empty card.

Known limitations:

- External SMS and push delivery currently use explicit construction log transports. Approved production Firebase/messaging provider configuration is Phase 3 work.
- Expanded national laboratory, component, hospital, cold-chain, and haemovigilance workflows remain in the later safety roadmap and are not claimed by this phase.

Next dependent task:

- Begin Phase 3 mobile API, Firebase, and Flutter work without reopening Phase 2 except for regressions required by Phase 3 integration.

## 2026-08-11 — Phase 3 local mobile/API and Firebase implementation gate

Status: local implementation and SDK verification complete; external device acceptance pending

Scope: mobile API, Firebase push transport, Flutter API contract, and documentation

Delivered:

- Completed the donor loyalty summary and privacy-safe anonymized leaderboard API with Sanctum abilities, donor ownership/privacy policy checks, validation, bounded pagination, stable aliases, and tests.
- Added the optional FCM HTTP v1 push transport behind the existing notification delivery contract, including recipient-scoped invalid-token retirement, provider identifiers, retry-compatible failures, and fingerprint-only audit metadata.
- Completed the Laravel-to-Flutter handoff in `docs/technical/api.md` with all 40 v1 routes, request bodies, filters, response envelopes, errors, field/alias contracts, Firebase backend configuration, and implementation guidance.
- Added authoritative completed-donation `total_volume_ml` plus readable `share_anonymized_data` to every mobile user response, keeping failed donation attempts out of the volume total and making privacy-consent state round-trip correctly.
- Confirmed Android package `com.nbts.mobile` belongs to Firebase project `nbts-d567e`; kept the backend service-account boundary outside Git.
- Extended the canonical `NBTS/nbts-mobile` client with loyalty, leaderboard, publication, and donation-schedule models/repositories, service registration, localized dashboard discovery, and repository/model contract tests.
- Explicitly marked iOS unsupported until an approved bundle ID and `GoogleService-Info.plist` are supplied.

Main implementation:

- `app/Http/Controllers/Api/V1/LoyaltyController.php`, `app/Http/Controllers/Api/V1/LeaderboardController.php`, and their requests/resources.
- `app/Services/Notifications/FcmPushTransport.php`, `config/services.php`, and `app/Providers/AppServiceProvider.php`.
- `app/Actions/Profile/PrepareMobileUserResource.php`, `app/Http/Resources/Api/V1/UserResource.php`, and `docs/technical/api.md`.
- Canonical mobile `lib/core/data/models`, `lib/core/data/repositories`, `lib/core/api/service_locator.dart`, and `lib/features/dashboard/screens/dashboard_screen.dart`.

Database/API impact:

- Added `GET /api/v1/loyalty` and `GET /api/v1/leaderboard`; no new Phase 3 migration was required.
- Added `total_volume_ml` and `share_anonymized_data` to existing mobile user representations without removing or renaming any v1 field.
- Added `PUSH_TRANSPORT=log` as the safe default. `fcm` requires approved credentials outside source control.

Automated verification:

- Phase 3 Laravel API/FCM group: 64 tests passed with 490 assertions.
- Consolidated Laravel Phase 3 contract rerun after the user-response/documentation handoff: 60 tests passed with 475 assertions.
- Focused password/Firebase/profile response group: 20 tests passed with 193 assertions.
- Eligibility deferral regression: 5 tests passed with 22 assertions.
- `vendor/bin/phpstan analyse --no-progress`: passed with 0 errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan route:list --path=api/v1 --except-vendor`: confirmed 40 v1 routes.
- Flutter 3.44/Dart 3.12 `flutter analyze`: passed with no issues.
- Flutter `flutter test --reporter compact`: all 8 API/model/repository and welcome/registration tests passed.
- `git diff --check`: passed.

Browser/device verification:

- Laravel contract behavior is covered by the API tests. Flutter analysis/tests ran against a read-only repository copy in an isolated SDK container; `adb` still reports no attached device, so Android execution is pending.
- No iOS Firebase file is present and the placeholder bundle ID remains; iOS support is not claimed.

Known limitations:

- A freshly rotated Firebase service-account key, approved external login providers, Android device/emulator, and real push delivery proof are external acceptance inputs.
- Google authentication and real FCM delivery are not claimed until an approved Android test target and fresh backend credentials are available.
- Further Flutter implementation and device evidence are owned by the separate mobile developer; this Laravel work provides the tested API contract and does not claim those external acceptance gates.

Next dependent task:

- Supply an Android test target and fresh Firebase credentials, then run the remaining Phase 3 authentication/device/push gates before starting Phase 4 implementation.

## 2026-08-11 — Staff navigation refinement gate

Status: completed

Scope: Laravel staff command-center navigation before further phase implementation

Delivered:

- Reordered the permitted staff destinations into Overview, Donation workflow, Coordination, System control, and Account groups without changing permission boundaries.
- Added an animated desktop collapse control that reduces the 244-pixel navigation to a persistent 60-pixel icon rail while keeping all permitted destinations available.
- Added translated navigation labels, icon tooltips, a compact active state, restrained separators, reduced-motion handling, and a clean edge-to-edge relationship with the page content.
- Kept the existing mobile sidebar behavior and did not begin new Phase 4 implementation as part of this refinement.

Verification:

- `php artisan test --compact tests/Feature/OperationsAccountTest.php`: 5 tests passed with 21 assertions.
- `npm run build`: passed; the updated production stylesheet was loaded in the browser.
- Headed Chromium at 1600×900 verified the 244-pixel expanded state, 60-pixel collapsed state, 12 visible icon destinations, translated tooltip content, saved collapse state after reload, reliable re-expansion, and no current browser errors.

## 2026-08-12 — Phase 5 operating model, assignment authorization, and role-aware dashboards

Status: implementation foundation completed; external clinical and governance approvals pending

Scope: Laravel documentation, organization/assignment backend, authorization, seeding, role-aware dashboards, tests, and visible browser QA

Delivered:

- Created the controlled `docs/overview/system-overview.md`, `docs/operations/center-operating-model.md`, and `docs/security/roles-and-permissions.md` documents, including the 26-profile matrix, 13-dashboard mapping, multi-assignment policy, active-context rules, and separation-of-duty boundaries.
- Recorded product-owner implementation approval while keeping Ministry/NBTS operations, laboratory, quality, hospital, privacy, center-capability, competency, and later clinical authority decisions explicitly open.
- Added organization units, departments, work locations, staff assignments, staff competencies, and blood-center organization links through additive migrations and typed Eloquent models.
- Added transactional, auditable assignment creation and status changes with actor-scope validation, effective dates, duplicate protection, clinical self-assignment protection, and historical accountability.
- Added ownership-checked active assignment resolution. Assignment, account, organization, department, and location state now constrain scoped access; center visibility and operational models use the selected effective assignment before the compatibility fallback.
- Removed blanket super-administrator authorization bypass. Technical administration now uses explicit permissions and does not grant unregistered or later clinical authority.
- Expanded the role catalogue to 26 target profiles plus the two temporary compatibility-only codes and seeded explicit permission mappings for all 28 transition roles.
- Added a dry-run-capable, non-destructive, idempotent `nbts:backfill-staff-assignments` command. The development backfill created two compatibility assignments on its first run and zero on its second run.
- Seeded five local compatibility accounts: super administrator, NBTS administrator, center manager, center staff, and donor. Four staff accounts have scoped assignments; the center-staff account has two centers for context-switch isolation testing.
- Built one compact premium Livewire dashboard shell with 13 role-aware staff configurations, active responsibility context, concise page heading and summary, real scoped metrics, priority queues, permission-aware quick actions, compact connected metric strips, and content-sized panels. Later clinical surfaces remain absent or explicitly unavailable.
- Preserved the animated 60-pixel icon-only navigation rail, English/Kiswahili labels, dark/light themes, reduced motion, responsive layout, and donor staff-web boundary.

Database/API impact:

- Applied six additive Phase 5 migrations to the development database. The verified snapshot contains five organization units, thirty-eight departments, six staff assignments, and five compatibility demo accounts.
- Kept deployed `center_staff` records and fallback behavior intact; no prior assignment or operational record was removed.
- Added no donor API v1 route and changed no Flutter code. `docs/technical/api.md` now records the future staff-context contract direction without claiming an endpoint exists.

Automated verification:

- `vendor/bin/pint --dirty --format agent`: passed.
- `composer lint:check`: passed.
- `composer types:check`: PHPStan passed with 0 errors.
- `php artisan view:cache`: passed.
- `npm run build`: passed; Vite produced the production bundle.
- `php artisan test --compact`: 213 tests passed with 2,473 assertions in 626.916 seconds before the final browser-discovered inventory aggregation regression was added.
- `php artisan test --compact tests/Feature/RoleAwareDashboardTest.php`: 6 tests passed with 51 assertions after adding and fixing the national inventory aggregation regression.
- Assignment coverage includes multi-center permission isolation, foreign/expired/suspended assignment denial, center visibility, hospital scope, assignment creation/revocation separation, and backfill dry-run/idempotence.
- Dashboard coverage proves exactly 13 configurations, all 26 target profile mappings, the five compatibility account boundaries, hospital controlled-foundation behavior, and assignment-switch session behavior.

Browser/device verification:

- Headed Chromium remained visible at 1600×900 in session `nbts-live`.
- Verified super-administrator, NBTS administrator, center manager, center staff, center-staff assignment switching, and donor staff-web denial.
- Verified the 60-pixel collapsed navigation, dark and light themes, no horizontal overflow, and no current page or JavaScript errors.
- Found and resolved four issues: unsupported metric icons, clipped national assignment text, indistinguishable center-assignment labels, and duplicate national blood-group rows instead of aggregated stock. Evidence is stored in `docs/evidence/phase-5-dashboard-qa/`.

Known limitations:

- Final organization hierarchy, center types/capabilities, department owners/escalations, competency policy, clinical separation rules, and hospital/patient boundary require external approval.
- Laboratory release, component approval, compatibility, blood issue, transfusion, recall, and other later safety-critical actions are not activated by this foundation.
- Dashboard SLA/owner/escalation detail remains tied to later queue implementations; fake clinical statistics or placeholder actions were intentionally not added.

Next dependent task:

- Obtain and record the remaining operating/clinical approvals, then implement the next task-file phase without reopening this verified foundation except for regressions or approved refinements.

## 2026-08-13 — Phase 6 donor reception, screening, collection, identification, and offline control

Status: Laravel construction phase completed; external production acceptance inputs remain explicitly blocked

Scope: Laravel backend, Livewire staff web, database, authorization, audit, operations evidence, and API guidance; no Flutter code change

Delivered:

- Added scoped donor reception with consent and communication preferences, normalized duplicate detection, authorized duplicate review/merge, immutable alias provenance, and expiring positive identity confirmation.
- Added versioned eligibility protocols, rule/questionnaire snapshots, confidential self-exclusion, safe deferral handling, counselling, referral, re-entry evidence, controlled overrides, and generic private notifications that omit sensitive clinical detail.
- Added transactionally locked center/year collection identifiers with check characters, Code 128-B labels, print/scan/apply/replacement provenance, original quarantine containers, required specimens, handoff evidence, and mismatch/relabel safety blocks.
- Added collection preparation, start, completion, explicit failed/interrupted/volume outcomes, aftercare acknowledgement, donor-reaction treatment/referral/follow-up, and quarantine-only compatibility stock creation.
- Added assigned and revocable offline devices, one-time credentials, non-overlapping expiring identifier batches, encrypted and idempotent receipts, authoritative reconciliation, retained conflict/rejection evidence, and numbered no-store downtime forms.
- Added explicit Phase 6 permissions and policies without granting implicit clinical authority to the super-administrator role.
- Built compact premium donor-reception, eligibility, collection, history, and offline workspaces with concise headings and summaries, filters, clear controls, configurable page size, pagination, content-sized dialogs, traceability progress, responsive layouts, dark/light themes, and the animated icon-only navigation rail.
- Seeded the construction screening protocol, center identifier/capacity settings, one complete demo donor journey, and the five verified local compatibility accounts.
- Updated the workflow, task, API/Flutter guidance, demo credentials, documentation entry point, and visible browser evidence without modifying the Flutter repository.

Main implementation:

- `app/Livewire/Operations/DonorJourney.php`
- `resources/views/livewire/operations/donor-journey.blade.php`
- `app/Actions/Collections/`, `app/Actions/Donors/`, `app/Actions/Eligibility/`, and `app/Actions/Offline/`
- `app/Services/CollectionIdentifierService.php`, `app/Services/Code128Barcode.php`, and `app/Services/DonorDuplicateDetector.php`
- Phase 6 Eloquent models, policies, factories, enums, configuration, seeders, and sixteen additive migrations dated `2026_08_12_111025` through `2026_08_12_111040`
- `tests/Feature/PhaseSixDonorIdentityTest.php`, `PhaseSixScreeningTest.php`, `PhaseSixCollectionTraceabilityTest.php`, `PhaseSixOfflineReconciliationTest.php`, and `PhaseSixWorkspaceTest.php`

Database/API impact:

- Added duplicate-case, identity-check, protocol, identifier-sequence, collection, container, specimen, label, donor-reaction, offline-device, identifier-batch, and encrypted-submission records plus additive donor, eligibility, and center fields.
- Successful collection and offline reconciliation stop at quarantine; neither path can release components or increase available inventory.
- Added explicit staff web routes for `/operations/donor-reception`, `/operations/eligibility`, and `/operations/donations`, plus protected barcode and downtime-form routes.
- Added no donor `/api/v1` route, changed no existing donor response field, and changed no Flutter file. `docs/technical/api.md` records the future field-device contract and server-authority rules.

Automated verification:

- Phase 6 focused coverage contains 23 tests across identity, screening, collection traceability, offline reconciliation, and workspace behavior.
- The latest traceability/workspace rerun passed 12 tests with 60 assertions, including the handed-off specimen-count regression.
- `vendor/bin/pint --dirty --format agent`, `composer lint:check`, `composer types:check`, `php artisan view:cache`, `npm run build`, and `git diff --check` passed.

Browser verification:

- Headed Chromium remained visible in session `nbts-live` at 1600×900 using the center-manager account and Muhimbili scope.
- Completed donor reception → eligibility → collection preparation → label issue/application → collection start → specimen collection/handoff → completion → quarantine history, then verified offline device/batch and no-store downtime-form controls.
- Verified dark and light themes, 244-to-60-pixel animated navigation collapse, no horizontal overflow, and no current page or JavaScript errors.
- Resolved all three findings: unsupported icons, oversized short-action panels, and handed-off specimens omitted from the progress total.
- Thirteen screenshots and the complete results are recorded in [`docs/evidence/phase-6-donor-journey-qa/report.md`](evidence/phase-6-donor-journey-qa/report.md).

Known limitations:

- Production national-identifier integration requires an approved source, legal basis, access, matching, and retention policy.
- The construction screening protocol cannot be promoted until clinical/NBTS owners approve the questionnaire, thresholds, decision/referral rules, and effective date.
- ISBT 128 or the approved national equivalent, production label layouts, product codes, and scanner/printer validation require national approval.
- Field-client protected storage, barcode capture, device/key baseline, retention, and loss/wipe behavior remain separately owned and externally blocked. Laravel's contract and controls are complete.

Next dependent task:

- Record the required clinical, identifier, privacy, security, and operations approvals. Start Phase 7 only after its safety/data documents and approval gates are accepted; do not reopen Phase 6 except for a verified regression or approved change.

## 2026-08-13 — Phase 7 backend laboratory, quarantine, and release foundation

Status: backend construction scope accepted and verified; formal production laboratory/quality owner approval remains pending

Scope: Laravel backend laboratory catalog, specimen/testing, laboratory QC, hard quarantine, release authorization, release-blocking, and backend release-drill evidence; no new Phase 7 browser/UI routes were introduced

Verified backend state:

- LAB-CATALOG, LAB-SAMPLE, and LAB-QC backend implementation has been accepted and verified.
- LAB-CATALOG stores approved catalog records, method/category/specimen rules, algorithm versions, release-blocking interpretations, effective dates, and approval actor/time.
- LAB-CATALOG stores reagent lots with usable/quarantined/recalled/expired state, validation state, expiry, receipt/storage, and recall timestamp.
- LAB-CATALOG stores equipment/analyzer registry data with calibration, maintenance, downtime, and manual/analyzer interface state.
- LAB-SAMPLE receives specimens by barcode, rejects mismatches/duplicates, creates required test orders from approved rules, and records test run/result/QC context.
- LAB-QC records internal QC and quality events and blocks donation-result use when QC is failed/missing.
- REL-QUAR backend hard-quarantine implementation has been accepted and verified.
- REL-AUTH backend release-authorization implementation has been accepted and verified after adaptation to the main `laboratory_test_results` schema.
- Release authorization was integrated without adding a duplicate laboratory result table.
- Attempted release with missing, reactive, discrepant, invalid, repeated, failed-QC, expired, recalled, or cold-chain-excursion affected data is blocked.
- Emergency override records auditable exception evidence without converting unsafe or untested donations into routine released stock.
- Routine release records criteria version, evaluated tests, approver, electronic signature, audit logs, quarantine release criteria, available-stock movement, and inventory count.

Automated verification:

- `NBTS_REUSE_TEST_SCHEMA=1 php artisan test --compact tests/Feature/PhaseSevenLaboratoryFoundationTest.php tests/Feature/PhaseSevenQuarantineTest.php tests/Feature/PhaseSevenReleaseAuthorizationTest.php`
- Result in main: 15 tests passed with 81 assertions.

Evidence boundary:

- This entry records verified backend construction scope only. It does not claim formal production laboratory-owner approval, quality-owner approval, production release readiness, or production clinical authority.
- Phase 7 browser/UI QA is not applicable to this backend-only construction increment because no Phase 7 Livewire/browser workspace routes were introduced.
- Component-level release evidence remains Phase 8 because component lineage is not part of the Phase 7 backend scope.

Next dependent task:

- Record formal production laboratory and quality owner approvals before treating these construction algorithms as production clinical policy.

## 2026-08-13 — Phase 8 backend component, inventory, cold-chain, and logistics foundation

Status: backend construction scope accepted and verified; formal production owner approvals remain pending

Scope: Laravel backend component catalog, component lineage, component-level inventory, FEFO reservation, expiry, return/disposal, cold-chain telemetry/excursion hold, center transfer, and hospital dispatch proof; no new Phase 8 browser/UI routes were introduced

Verified backend state:

- CMP-MODEL stores component/product catalog records with codes, methods, additive solutions, volume, storage range, shelf life, quality criteria, and approval metadata.
- Legacy `blood_units` compatibility records remain intact while `blood_components` provides controlled derived component lineage.
- One donation can produce zero or more uniquely identified components with parent-child lineage.
- Processing events record method/device, operator, timing, yields, modifications, QC samples, deviations, and final label verification.
- Orphan and cross-donation component lineage is blocked.
- INV-COMP tracks component type, ABO/Rh, attributes, release state, center, location/device, expiry, reservation, dispatch, receipt, return, disposal, recall, and investigation hold state.
- FEFO reservation chooses the earliest compatible available component and stale reservations are released.
- Component reconciliation compares authoritative component states against physical counts.
- INV-EXP expires eligible components without deleting traceability, assesses returns before restocking, and records disposal reason/method/witness/approval/evidence.
- CC-EQUIP records cold-chain devices and temperature readings, opens alarms/excursions, and automatically holds affected stock.
- LOG-TRANSFER moves stock only through transfer state transitions with temperature evidence and acceptance/hold result.
- LOG-DISPATCH packs only issued/authorized components and records proof-of-delivery reconciliation.

Automated verification:

- `NBTS_REUSE_TEST_SCHEMA=1 php artisan test --compact tests/Feature/PhaseEightComponentLineageTest.php tests/Feature/PhaseEightComponentInventoryTest.php tests/Feature/PhaseEightColdChainLogisticsTest.php`
- Result in main: 7 tests passed with 45 assertions.

Evidence boundary:

- This entry records verified backend construction scope only. It does not claim formal production component-owner, inventory-owner, logistics-owner, cold-chain-owner, or quality-owner approval.
- Phase 8 browser/UI QA is not applicable to this backend-only construction increment because no Phase 8 Livewire/browser workspace routes were introduced.
- Hospital clinical compatibility and transfusion workflows remain Phase 9.

Next dependent task:

- Record formal production owner approvals before treating these component, inventory, cold-chain, and logistics workflows as production operational policy.

## 2026-08-13 — Phase 9 backend hospital request, compatibility, issue, and transfusion foundation

Status: backend construction scope accepted and verified; formal hospital clinical and blood-bank owner approvals remain pending

Scope: Laravel backend hospital registry, hospital service registry, hospital-scoped request handling, patient specimen linkage, compatibility/crossmatch evidence, emergency release authorization, FEFO component allocation, issue check, receipt, bedside verification, transfusion outcome, and overdue outcome queue; no new Phase 9 browser/UI routes were introduced.

Verified backend state:

- HSP-REG stores approved hospitals, hospital blood-bank/service capability metadata, operating hours, request routes, integration identifiers, patient-identity minimums, privacy policy version, and service status.
- Hospital users are constrained by active hospital organization assignments before request, compatibility, issue, receipt, or transfusion actions are accepted.
- HSP-REQ records minimum patient reference, hospital/ward/service, clinician requester, diagnosis/indication, haemoglobin, observations, active bleeding state, urgency, requested component, quantity, required time, attachments, notes, guidance snapshot, override reason, source mode, and timestamps.
- Requests outside the construction patient-blood-management threshold require an override reason without replacing clinical judgment.
- Downtime/paper requests are captured as controlled records instead of disappearing from the workflow.
- XMT-COMPAT links patient specimens to the same patient request, records ABO/Rh confirmation, antibody screen, compatibility result, method, instrument/reagent/control context, operator, reviewer, validity window, and exceptions.
- Wrong-patient, wrong-component, incompatible, expired, unavailable, and unapproved component paths are blocked before routine allocation.
- Emergency release requires emergency urgency, named clinical authorization, reason, risk acknowledgement, selected component, acknowledgement, and retrospective completion due date.
- HSP-ALLOC allocates compatible components by FEFO and changes component state to prevent double allocation.
- Final issue check requires request, patient, component, release, compatibility/emergency authorization, expiry, label, package, and staff confirmation.
- Hospital receipt records receiving officer, time, condition, temperature evidence, discrepancy/hold state, and chain of custody.
- TRF-BEDSIDE records bedside verification, start/completion, observations, volume, outcome, unused disposition, final component state, and donor-to-recipient traceability.
- Missing outcomes appear in an overdue reconciliation queue.

Automated verification:

- `NBTS_REUSE_TEST_SCHEMA=1 php artisan test --compact tests/Feature/PhaseNineHospitalRequestTest.php tests/Feature/PhaseNineCompatibilityIssueTest.php tests/Feature/PhaseNineTransfusionTraceabilityTest.php`
- Result in main: 6 tests passed with 32 assertions.
- Phase 8+9 regression: `NBTS_REUSE_TEST_SCHEMA=1 php artisan test --compact tests/Feature/PhaseEightComponentLineageTest.php tests/Feature/PhaseEightComponentInventoryTest.php tests/Feature/PhaseEightColdChainLogisticsTest.php tests/Feature/PhaseNineHospitalRequestTest.php tests/Feature/PhaseNineCompatibilityIssueTest.php tests/Feature/PhaseNineTransfusionTraceabilityTest.php`
- Result in main: 13 tests passed with 77 assertions.

Evidence boundary:

- This entry records verified backend construction scope only. It does not claim formal hospital clinical-owner approval, hospital blood-bank-owner approval, compatibility rule approval, transfusion policy approval, quality-owner approval, production release readiness, or production clinical authority.
- Phase 9 browser/UI QA is not applicable to this backend-only construction increment because no Phase 9 Livewire/browser workspace routes were introduced.
- Haemovigilance adverse-event investigation, recall, look-back, CAPA, SOP, competency, and audit workflows remain Phase 10.

Next dependent task:

- Record formal hospital clinical, blood-bank, compatibility, transfusion, and quality owner approvals before treating these workflows as production clinical policy.

## 2026-08-26 — Phase 10 backend haemovigilance, recall, QMS, and clinical governance foundation

Status: backend construction scope accepted and verified; formal quality/haemovigilance owner approval remains pending

Scope: Laravel backend haemovigilance event records, recall/look-back trace records, quality deviations/CAPA, SOP document control, competency training, audit/EQA records, quality trend snapshot, and hospital transfusion committee review; no new Phase 10 browser/UI routes were introduced.

Verified backend state:

- HV-DONOR records donor reactions with severity, symptoms, treatment, referral, follow-up due date, future-eligibility implication context, center, collection episode, equipment, bag/supply context, escalation, and notification targets.
- HV-RECIP records recipient transfusion reactions against hospital, request, component, transfusion record, immediate action, outcome, samples/tests/staff investigation context, classification, imputability, reporting state, escalation, and notification targets.
- HV-RECALL opens controlled recall/look-back cases from approved triggers, holds affected components, traces backward to donation/unit and forward to component issue/hospital/recipient, records deadlines, decision authority, closure approval, and unresolved exceptions.
- QMS-CAPA records deviations/nonconformities with affected records, owner, due date, RCA, corrective/preventive action, effectiveness check, closure evidence, and quality approval.
- Critical CAPA closure is blocked until RCA, corrective action, preventive action, effectiveness check, and evidence are present.
- QMS trend analysis identifies repeated deviation types, open critical deviations, overdue open deviations, and audit-linked deviation IDs.
- QMS-SOP records effective SOP/document versions and links workflow codes to the active document version.
- Competency records track staff training, verifier, evidence, reassessment, validity, and retraining state.
- Audit and EQA records capture findings/results and nonconforming EQA outcomes.
- Hospital transfusion committee review records utilization, emergency release, reaction, wastage, and education actions.

Automated verification:

- `NBTS_REUSE_TEST_SCHEMA=true DB_DATABASE=nbts_new_test php artisan test --compact tests/Feature/PhaseTenHaemovigilanceTest.php tests/Feature/PhaseTenRecallLookbackTest.php tests/Feature/PhaseTenQualityGovernanceTest.php`
- Result in main: 8 tests passed with 69 assertions.
- `vendor/bin/pint --dirty --format agent`
- Result in main: passed.

Evidence boundary:

- This entry records verified backend construction scope only. It does not claim formal production quality-owner approval, haemovigilance-owner approval, regulator approval, production release readiness, or production clinical authority.
- Phase 10 browser/UI QA is not applicable to this backend-only construction increment because no Phase 10 Livewire/browser workspace routes were introduced.
- Notification targets are recorded as structured escalation metadata; external delivery channels remain part of later notification/integration work.

Next dependent task:

- Record formal production quality and haemovigilance owner approvals before treating investigation, recall closure, CAPA closure, and committee review workflows as production clinical governance policy.

## 2026-08-26 — Phase 11 backend data governance, interoperability, security, resilience, and change-control foundation

Status: backend construction scope accepted and verified; formal external architecture and production owner approvals remain pending

Scope: Laravel backend privacy inventory, privacy notice/version control, retention policy/deletion guard, protected export approval, integration endpoint/message gateway records, high-risk MFA enforcement service, access review conflict reporting, backup/restore evidence, incident/SLA records, and change-control approval rules; no Flutter code was changed.

Verified backend state:

- SEC-PRIV records processing inventory, controller/processor responsibilities, lawful basis, data minimization, vendor controls, DPIA evidence, breach response, rights handling, privacy notice versions, communication preferences, retention policy, deletion restriction, and encrypted protected-export manifest.
- INT-STD records approved integration endpoint profiles and encrypted endpoint config, enforces idempotency and sequence checks, stores acknowledgements, and dead-letters failed/late messages instead of losing them silently.
- SEC-ACCESS enforces MFA for high-risk roles and creates access review records with high-risk roles and separation-conflict evidence.
- DR-BCP records encrypted/off-site backup evidence with size/checksum/verification and recovery exercises with recovery point/time, required identification/quarantine/release/traceability validation checks, exceptions, reopening approval, and CAPA reference.
- OPS-SLA records severity, owner, impact, workaround, communication, escalation targets, acknowledgement, restoration/resolution state, and recurrence link.
- GOV-CHANGE records clinical/safety, privacy/data, operational, and infrastructure changes with required approvals, regression evidence, migration/rollback plan, release notes, training impact, effective date, emergency flag, and retrospective review due date.

Automated verification:

- `NBTS_REUSE_TEST_SCHEMA=true DB_DATABASE=nbts_new_test php artisan test --compact tests/Feature/PhaseElevenGovernanceTest.php`
- Result in main: 4 tests passed with 43 assertions.
- `vendor/bin/pint --dirty --format agent`
- Result in main: passed after import-order fix.

Evidence boundary:

- This entry records verified backend construction scope only. It does not claim Ministry interoperability approval, ISBT/national coding decision approval, production HMIS/LIS/DHIS2 connector readiness, infrastructure TLS/certificate/key-management deployment, physical network segmentation, formal clinical safety officer appointment, or production readiness approval.
- Integration endpoint records avoid direct database access by contract, but real external connector implementation remains later integration work.
- Phase 11 browser/UI QA is not applicable to this backend-only construction increment because no Phase 11 Livewire/browser workspace routes were introduced.

Next dependent task:

- Obtain formal DPO, ICT/security, quality, clinical, operations, Ministry/interoperability, and governance owner approvals before treating these controls as production readiness evidence.

## 2026-08-27 — Phase 12 Laravel staff UX, communication, reporting, document snapshot, and public legal-content foundation

Status: Laravel/backend/web construction scope implemented and verified; Flutter/device-lab and visible browser/device QA remain pending

Scope: Laravel staff operations shell, public legal pages, notification outbox controls, donor communication segmentation, KPI dictionary, report snapshots, PDF/export document snapshots, localization, routing, and focused automated verification; no Flutter code was changed.

Delivered:

- Added direct role-aware staff workspaces for Laboratory, Components, Inventory, Logistics, Hospital, and Quality inside the existing Operations shell so the Phase 7–10 records are reachable through a consistent premium table interface with search, filters, clear controls, sorting, pagination, visible-column controls, empty states, and scope-aware navigation.
- Added hospital and quality record queries and row summaries for hospital requests, compatibility tests, issue/allocation controls, transfusion records, haemovigilance events, recalls, CAPA/deviations, and audits.
- Added approved public legal pages for privacy policy, data-protection notice, terms of use, cookie statement, and complaints/rights handling, with English and Kiswahili translations and footer links.
- Added Phase 12 notification outbox governance covering after-commit intent, idempotency, provider status fields, attempts/retries, failure visibility, preferences, consent, quiet hours, safe template summaries, operational alert categories, SMS/USSD/assisted channel support, and non-coercive recognition blocking.
- Added donor communication segmentation by approved non-sensitive criteria: location, blood group, eligibility date, language, channel, previous response, and consent. Sensitive health/patient criteria are rejected.
- Added KPI definition approval and report snapshot controls with numerator, denominator, exclusions, source models, owner, frequency, target, data-quality checks, anti-gaming controls, de-identification, national-dashboard readiness, and authoritative reconciliation.
- Added privacy-safe document/export snapshots with locale labels, stable identifiers, source periods, document reference, issue time, verification context, access scope, authorization/audit flags, encrypted payloads, checksum, queue flag, queue name, and safe expiry.

Main implementation:

- `app/Livewire/Operations/Workspace.php`
- `app/Services/PhaseTwelveExperienceService.php`
- `app/Http/Controllers/Web/PublicPageController.php`
- `config/operations.php`
- `routes/web.php`
- `resources/views/web/legal.blade.php`
- `resources/views/layouts/public.blade.php`
- `lang/en/console.php`
- `lang/sw/console.php`
- `lang/en/public.php`
- `lang/sw/public.php`
- `tests/Feature/PhaseTwelveExperienceTest.php`
- `tests/Feature/OperationsAccountTest.php`
- `tests/Feature/PhaseSixWorkspaceTest.php`

Database/API impact:

- Added Phase 12 data tables: `notification_outboxes`, `kpi_definitions`, `report_snapshots`, and `document_snapshots`.
- Added public web routes: `/privacy`, `/data-protection`, `/terms`, `/cookies`, and `/complaints-rights`.
- No mobile/Flutter source files were changed.

Automated verification:

- `DB_DATABASE=nbts_new_test php artisan migrate --no-interaction`
- Result: Phase 12 migrations applied to the test database.
- `DB_DATABASE=nbts_new_dev php artisan migrate --no-interaction`
- Result: Phase 12 migrations applied to the development database.
- `NBTS_REUSE_TEST_SCHEMA=true DB_DATABASE=nbts_new_test php artisan test --compact tests/Feature/PhaseTwelveExperienceTest.php`
- Result: 4 tests passed with 64 assertions.
- `NBTS_REUSE_TEST_SCHEMA=true DB_DATABASE=nbts_new_test php artisan test --compact tests/Feature/OperationsAccountTest.php tests/Feature/PublicWebsiteTest.php tests/Feature/PhaseSixWorkspaceTest.php`
- Result: 20 tests passed with 127 assertions.
- `NBTS_REUSE_TEST_SCHEMA=true DB_DATABASE=nbts_new_test php artisan test --compact tests/Feature/PhaseTwelveExperienceTest.php tests/Feature/OperationsAccountTest.php tests/Feature/PublicWebsiteTest.php tests/Feature/PhaseSixWorkspaceTest.php`
- Result: 24 tests passed with 191 assertions.
- `vendor/bin/pint --dirty --format agent`
- Result: passed after formatting/import-order fixes.

Browser/device verification:

- Not completed in this increment. Visible 1600×900, tablet, mobile, barcode/printer, Flutter, push-device, and external device-lab evidence remain open in `docs/planning/task.md`.

Known limitations:

- Rendered PDF templates for every listed operational document remain partial; this increment implements reproducible, localized, authorized, encrypted document/export snapshot foundations.
- Demand forecasting and campaign-trigger automation remain pending until data quality and component-level demand are proven.
- Controlled public content workflow is partial for leadership/governance, regional contacts, and final mobile-store links.
- Formal production owner approvals and real external provider integrations are not claimed.

Next dependent task:

- Run visible browser/device QA and complete the remaining Flutter/device/printer/barcode evidence gates before marking Phase 12 fully production-ready.

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
