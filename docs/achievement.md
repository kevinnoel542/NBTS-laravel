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
